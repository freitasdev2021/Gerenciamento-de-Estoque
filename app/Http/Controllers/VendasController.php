<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Filial;
use App\Models\Pagamento;
use App\Models\Produto;
use App\Models\Venda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendasController extends Controller
{
    // ======================
    // MÉTODOS RESOURCE (CRUD)
    // ======================

    /**
     * Display a listing of sales.
     */
    public function index()
    {
        $filialId = $_SESSION['login']['filial'] ?? null;

        $vendas = self::getListaVendas($filialId);
        $filiais = Filial::orderBy('NMFilial')->get();

        return view('vendas.index', compact('vendas', 'filiais', 'filialId'));
    }

    /**
     * Show the form for making a sale.
     */
    public function create(Request $request)
    {
        $filialId = $_SESSION['login']['filial'] ?? 1;
        $produtoId = $request->input('produto');

        $produto = null;
        if ($produtoId) {
            $produto = Produto::with('fornecedor')->find($produtoId);
        }

        $clientes = Cliente::whereNull('STDelete')
            ->where('IDFilial', $filialId)
            ->orderBy('NMCliente')
            ->get();

        $pagamentos = Pagamento::whereNull('STDelete')
            ->where('IDFilial', $filialId)
            ->orderBy('NMPagamento')
            ->get();

        return view('vendas.create', compact('produto', 'clientes', 'pagamentos', 'filialId'));
    }

    /**
     * Process a sale.
     */
    public function store(Request $request)
    {
        $request->validate([
            'IDProduto'         => 'required|integer|exists:produtos,IDProduto',
            'IDCliente'         => 'required|integer|exists:clientes,IDCliente',
            'IDPagamento'       => 'required|integer|exists:pagamentos,IDPagamento',
            'NUUnidadesVendidas' => 'required|integer|min:1',
        ]);

        $filialId = $_SESSION['login']['filial'] ?? null;
        $produto = Produto::with('fornecedor')->findOrFail($request->IDProduto);

        // Verifica estoque
        if ($produto->NUEstoqueProduto < $request->NUUnidadesVendidas) {
            return redirect()->back()->with('error', 'Estoque insuficiente! Disponível: ' . $produto->NUEstoqueProduto)->withInput();
        }

        // Calcula o valor com promoção
        $valorComPromocao = PromocoesController::confProdutoPromocional(
            $produto->IDProduto,
            $produto->NUValorProduto,
            $filialId
        );

        $valorVenda = $valorComPromocao * $request->NUUnidadesVendidas;

        // Aplica desconto do pagamento
        $valorComDesconto = self::getDescontoPagamento($valorVenda, $request->IDPagamento);

        // Obtém o ID da promoção ativa (se houver)
        $idPromocao = $this->getPromocaoAtiva($produto->IDProduto, $filialId);
        $idFornecedor = $produto->fornecedor->IDFornecedor ?? 0;

        try {
            self::setVenda([
                'IDProduto'          => $produto->IDProduto,
                'IDFornecedor'       => $idFornecedor,
                'IDPromocao'         => $idPromocao,
                'IDCliente'          => $request->IDCliente,
                'IDColaborador'      => ContratosController::getColaboradorByUser($_SESSION['login']['dados']['id'] ?? 0),
                'NUUnidadesVendidas' => $request->NUUnidadesVendidas,
                'IDCaixa'            => 0,
                'IDFilial'           => $filialId,
                'IDPagamento'        => $request->IDPagamento,
                'VLVenda'            => $valorComDesconto,
                'CDVenda'            => '',
                'IDOrdem'            => '',
            ]);

            return redirect()->route('vendas.index')->with('success', 'Venda realizada com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao processar a venda: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Cancel a sale.
     */
    public function destroy($id)
    {
        Venda::where('IDVenda', $id)->update(['STVenda' => 0]);

        return redirect()->route('vendas.index')->with('success', 'Venda cancelada com sucesso!');
    }

    // ============================
    // MÉTODOS AUXILIARES DO RESOURCE
    // ============================

    /**
     * Retorna o ID da promoção ativa para um produto.
     */
    private function getPromocaoAtiva($IDProduto, $IDFilial)
    {
        $result = DB::select(
            "SELECT promocoes.IDPromocao
             FROM promocoes
             INNER JOIN promocionais USING(IDPromocao)
             WHERE NOW() >= promocoes.DTInicioPromo
               AND NOW() <= promocoes.DTTerminoPromo
               AND promocoes.IDFilial = ?
               AND promocionais.IDProduto = ?
             LIMIT 1",
            [$IDFilial, $IDProduto]
        );

        return !empty($result) ? $result[0]->IDPromocao : 0;
    }

    // ================================
    // MÉTODOS ESTÁTICOS (COMPATIBILIDADE)
    // ================================

    /**
     * Cancela uma venda (total ou parcial), com opção de repor estoque.
     *
     * @param  array  $dados  (IDVenda, QTDevolucao, Vendas, Acao)
     * @return bool
     */
    public static function cancelaVenda($dados)
    {
        $IDVenda      = $dados['IDVenda'];
        $QTDevolucao  = $dados['QTDevolucao'];
        $Vendas       = $dados['Vendas'];
        $Acao         = $dados['Acao'];

        $venda = DB::select(
            "SELECT NUCustoProduto, NUEstoqueProduto, IDProduto, VLVenda 
             FROM produtos 
             INNER JOIN vendas USING(IDProduto) 
             WHERE IDVenda = ?",
            [$IDVenda]
        );

        if (empty($venda)) {
            return false;
        }

        $produto = $venda[0];
        $vlvenda = $produto->VLVenda / $Vendas;

        if ($QTDevolucao == $Vendas) {
            Venda::where('IDVenda', $IDVenda)
                ->update(['STVenda' => 0]);

            if ($Acao == "Repor") {
                Produto::where('IDProduto', $produto->IDProduto)
                    ->increment('NUEstoqueProduto', $QTDevolucao);
            }
        } else {
            Venda::where('IDVenda', $IDVenda)->update([
                'NUUnidadesVendidas' => DB::raw("NUUnidadesVendidas - $QTDevolucao"),
                'VLVenda'            => DB::raw("VLVenda - $vlvenda"),
            ]);

            if ($Acao == "Repor") {
                Produto::where('IDProduto', $produto->IDProduto)
                    ->increment('NUEstoqueProduto', $QTDevolucao);
            }
        }

        return true;
    }

    /**
     * Retorna lista de vendas (produtos, não insumos) de uma filial.
     *
     * @param  int   $IDFilial
     * @return array
     */
    public static function getListaVendas($IDFilial)
    {
        return DB::select(
            "SELECT
                NMProduto,
                VLVenda,
                NMPagamento,
                NMPromo,
                NMCliente,
                NMPdv,
                DSGarantiaProduto,
                DTVenda,
                IDVenda,
                NUUnidadesVendidas,
                NMColaborador,
                NUCustoProduto,
                QTDesconto,
                pagamentos.TPDesconto
            FROM vendas
            INNER JOIN produtos ON(produtos.IDProduto = vendas.IDProduto)
            INNER JOIN pagamentos ON(pagamentos.IDPagamento = vendas.IDPagamento)
            LEFT JOIN promocoes ON(promocoes.IDPromocao = vendas.IDPromocao)
            LEFT JOIN clientes ON(clientes.IDCliente = vendas.IDCliente)
            LEFT JOIN caixa ON(caixa.IDCaixa = vendas.IDCaixa)
            LEFT JOIN colaboradores ON(colaboradores.IDColaborador = vendas.IDColaborador)
            LEFT JOIN fornecedores ON(fornecedores.IDFornecedor = produtos.IDFornecedor)
            LEFT JOIN filiais ON(filiais.IDFilial = fornecedores.IDFilial)
            WHERE STInsumo = 0 AND filiais.IDFilial = ? AND STVenda = 1
            ORDER BY DTVenda DESC",
            [$IDFilial]
        );
    }

    /**
     * Retorna lista de vendas de insumos de uma filial.
     *
     * @param  int   $IDFilial
     * @return array
     */
    public static function getListaVendasInsumos($IDFilial)
    {
        return DB::select(
            "SELECT
                NMProduto,
                VLVenda,
                NMPagamento,
                NMPromo,
                NMCliente,
                NMPdv,
                IDVenda,
                NUUnidadesVendidas,
                NMColaborador,
                DSTipoServico,
                NUCustoProduto,
                QTDesconto,
                pagamentos.TPDesconto,
                DTVenda
            FROM vendas
            INNER JOIN produtos ON(produtos.IDProduto = vendas.IDProduto)
            INNER JOIN pagamentos ON(pagamentos.IDPagamento = vendas.IDPagamento)
            LEFT JOIN promocoes ON(promocoes.IDPromocao = vendas.IDPromocao)
            LEFT JOIN clientes ON(clientes.IDCliente = vendas.IDCliente)
            LEFT JOIN caixa ON(caixa.IDCaixa = vendas.IDCaixa)
            LEFT JOIN colaboradores ON(colaboradores.IDColaborador = vendas.IDColaborador)
            LEFT JOIN fornecedores ON(fornecedores.IDFornecedor = produtos.IDFornecedor)
            LEFT JOIN filiais ON(filiais.IDFilial = fornecedores.IDFilial)
            INNER JOIN servicos ON(servicos.IDFilial = filiais.IDFilial)
            INNER JOIN ordemservico ON(ordemservico.IDServico = servicos.IDServico)
            LEFT JOIN custosordem ON(ordemservico.IDOrdem = custosordem.IDOrdem)
            WHERE STInsumo = 1 AND filiais.IDFilial = ?
            ORDER BY DTVenda DESC",
            [$IDFilial]
        );
    }

    /**
     * Verifica se existe alguma venda para um produto.
     *
     * @param  int  $IDProduto
     * @return \App\Models\Venda|null
     */
    public static function confereVenda($IDProduto)
    {
        return Venda::where('IDProduto', $IDProduto)->first();
    }

    /**
     * Sincroniza/Insere uma venda (usado por sistemas externos).
     *
     * @param  array  $dados  Dados da venda
     * @return \App\Models\Venda
     */
    public static function sincronizaVendas($dados)
    {
        return Venda::create([
            'IDProduto'          => $dados['IDProduto'],
            'IDFornecedor'       => $dados['IDFornecedor'],
            'IDPromocao'         => $dados['IDPromocao'],
            'IDCliente'          => $dados['IDCliente'],
            'IDColaborador'      => $dados['IDColaborador'],
            'NUUnidadesVendidas' => $dados['NUUnidadesVendidas'],
            'IDCaixa'            => $dados['IDCaixa'],
            'IDFilial'           => $dados['IDFilial'],
            'IDPagamento'        => $dados['IDPagamento'],
            'VLVenda'            => $dados['VLVenda'],
            'CDVenda'            => $dados['CDVenda'],
        ]);
    }

    /**
     * Registra uma venda e decrementa o estoque do produto.
     *
     * @param  array  $dados  Dados da venda
     * @return \App\Models\Venda
     */
    public static function setVenda($dados)
    {
        DB::beginTransaction();
        try {
            $ordi = $dados['IDOrdem'] ?? '';

            $venda = Venda::create([
                'IDProduto'          => $dados['IDProduto'],
                'IDFornecedor'       => $dados['IDFornecedor'],
                'IDPromocao'         => $dados['IDPromocao'],
                'IDCliente'          => $dados['IDCliente'],
                'IDColaborador'      => $dados['IDColaborador'],
                'NUUnidadesVendidas' => $dados['NUUnidadesVendidas'],
                'IDCaixa'            => $dados['IDCaixa'],
                'IDFilial'           => $dados['IDFilial'],
                'IDPagamento'        => $dados['IDPagamento'],
                'VLVenda'            => $dados['VLVenda'],
                'CDVenda'            => $dados['CDVenda'],
                'IDOrdem'            => $ordi,
            ]);

            Produto::where('IDProduto', $dados['IDProduto'])
                ->decrement('NUEstoqueProduto', $dados['NUUnidadesVendidas']);

            DB::commit();
            return $venda;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Retorna dados agregados de vendas de um produto.
     *
     * @param  int   $IDProduto
     * @return array
     */
    public static function getQuantidadeVendas($IDProduto)
    {
        $result = DB::select(
            "SELECT 
                SUM(NUUnidadesVendidas) as quantidadeVendas,
                SUM(NUUnidadesVendidas) * NUValorProduto as valorVendas,
                SUM(NUCustoProduto) as custoProd,
                produtos.IDProduto as produto,
                CASE WHEN vendas.IDPagamento IS NOT NULL THEN vendas.IDPagamento ELSE 0 END as pagamento
            FROM produtos 
            LEFT JOIN vendas USING(IDProduto) 
            WHERE IDProduto = ? AND STVenda = 1",
            [$IDProduto]
        );

        if (empty($result) || !$result[0]->quantidadeVendas) {
            return [
                "quantidade"       => 0,
                "valorVendas"      => 0,
                "valorVendasBruto" => 0,
            ];
        }

        $retorno = $result[0];
        $descontoPagamento = self::getDescontoPagamento($retorno->valorVendas, $retorno->pagamento);
        $valorDescontoPromo = PromocoesController::confProdutoPromocional(
            $retorno->produto,
            $descontoPagamento,
            $_SESSION['login']['filial']
        );

        return [
            "quantidade"       => $retorno->quantidadeVendas ?? 0,
            "valorVendas"      => $valorDescontoPromo - $retorno->custoProd,
            "valorVendasBruto" => $valorDescontoPromo,
        ];
    }

    /**
     * Retorna dados do produto (ID, fornecedor, valor, promoção).
     *
     * @param  int        $IDProduto
     * @return object|null
     */
    public static function getDadosProduto($IDProduto)
    {
        $result = DB::select(
            "SELECT 
                P.IDProduto,
                f.IDFornecedor,
                P.NUValorProduto,
                CASE WHEN promo.IDPromocao IS NULL THEN 0 ELSE promo.IDPromocao END as IDPromocao
            FROM produtos P
            INNER JOIN fornecedores f USING(IDFornecedor)
            LEFT JOIN promocionais promo USING(IDProduto)
            WHERE IDProduto = ?",
            [$IDProduto]
        );

        return !empty($result) ? $result[0] : null;
    }

    /**
     * Retorna a situação financeira de um cliente.
     *
     * @param  int   $IDCliente
     * @return array
     */
    public static function getSituacaoCliente($IDCliente)
    {
        return DB::select(
            "SELECT 
                CASE WHEN d.VLDivida IS NOT NULL THEN d.VLDivida ELSE 0.00 END as divida,
                CASE WHEN c.NUCredito IS NOT NULL THEN c.NUCredito ELSE 0.00 END as credito
            FROM clientes cli
            LEFT JOIN devedores d USING(IDCliente) 
            LEFT JOIN crediarios c USING(IDCliente)
            WHERE cli.IDCliente = ?",
            [$IDCliente]
        );
    }

    /**
     * Calcula o valor com desconto baseado no método de pagamento.
     *
     * @param  float  $valor
     * @param  int    $pagamento  ID do pagamento (0 = sem pagamento)
     * @return float
     */
    public static function getDescontoPagamento($valor, $pagamento)
    {
        if (!$pagamento) {
            return $valor;
        }

        $pag = Pagamento::find($pagamento);

        if (!$pag) {
            return $valor;
        }

        if ($pag->TPDesconto == 1) {
            $desconto = $valor - ($pag->QTDesconto * $valor) / 100;
        } elseif ($pag->TPDesconto == 2) {
            $desconto = $valor - $pag->QTDesconto;
        } else {
            $desconto = $valor;
        }

        return number_format((float)$desconto, 2, '.', '');
    }
}