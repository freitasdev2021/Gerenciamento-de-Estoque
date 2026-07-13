<?php

namespace App\Http\Controllers;

use App\Models\Pagamento;
use App\Models\Produto;
use App\Models\Venda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendasController extends Controller
{
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

        // Busca dados do produto vinculado à venda
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
            // Devolução total: cancela a venda
            Venda::where('IDVenda', $IDVenda)
                ->update(['STVenda' => 0]);

            if ($Acao == "Repor") {
                Produto::where('IDProduto', $produto->IDProduto)
                    ->increment('NUEstoqueProduto', $QTDevolucao);
            }
        } else {
            // Devolução parcial: reduz unidades e valor
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
     * Query complexa com múltiplos JOINs.
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
            WHERE STInsumo = 0 AND filiais.IDFilial = ? AND STVenda = 1",
            [$IDFilial]
        );
    }

    /**
     * Retorna lista de vendas de insumos de uma filial.
     * Query complexa com múltiplos JOINs.
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
            WHERE STInsumo = 1 AND filiais.IDFilial = ?",
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

            // Decrementa o estoque do produto
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
     * Retorna dados agregados de vendas de um produto (quantidade, valor líquido e bruto).
     * Query complexa com SUM, CASE WHEN e cálculo de descontos.
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
                "quantidade"      => 0,
                "valorVendas"     => 0,
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
     * Retorna a situação financeira de um cliente (dívida e crédito).
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
     * TPDesconto: 1 = percentual (%), 2 = valor fixo (R$)
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
            // Percentual
            $desconto = $valor - ($pag->QTDesconto * $valor) / 100;
        } elseif ($pag->TPDesconto == 2) {
            // Valor fixo
            $desconto = $valor - $pag->QTDesconto;
        } else {
            $desconto = $valor;
        }

        return number_format((float)$desconto, 2, '.', '');
    }
}