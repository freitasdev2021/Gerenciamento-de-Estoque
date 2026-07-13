<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\CustosOrdem;
use App\Models\OrdemServico;
use App\Models\Produto;
use App\Models\Servico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServicosController extends Controller
{
    /**
     * Cancela uma ordem de serviço: devolve produto ao estoque, remove vendas e marca STServico = 0.
     *
     * @param  array  $dados  (ID)
     * @return int
     */
    public static function cancelaOrdem($dados)
    {
        $ID = $dados['ID'];

        // Busca o ID do produto vinculado às vendas da ordem
        $venda = DB::select("SELECT IDProduto FROM vendas WHERE IDOrdem = ?", [$ID]);

        if (!empty($venda)) {
            $idProduto = $venda[0]->IDProduto;

            // Devolve 1 unidade ao estoque
            Produto::where('IDProduto', $idProduto)->increment('NUEstoqueProduto', 1);
        }

        // Remove as vendas vinculadas
        DB::delete("DELETE FROM vendas WHERE IDOrdem = ?", [$ID]);

        // Marca a ordem como cancelada
        return OrdemServico::where('IDOrdem', $ID)
            ->update(['STServico' => 0]);
    }

    /**
     * Retorna a lista de serviços com detalhes de ordens, clientes, pagamentos e insumos.
     * Query extremamente complexa com múltiplos JOINs, subqueries e CASE WHEN.
     *
     * @param  int   $IDFilial
     * @return array
     */
    public static function getListaServicos($IDFilial)
    {
        return DB::select(
            "SELECT
                ordemservico.IDColaborador as idcol,
                ordemservico.IDOrdem as idord,
                NMCliente,
                NMPagamento,
                QTDesconto,
                TPDesconto,
                NUCustoProduto,
                NUUnidadesVendidas,
                VLVenda,
                VLBase,
                DTServico,
                NMColaborador,
                CASE WHEN pagamentos.QTDesconto > 0 THEN
                    CASE WHEN pagamentos.TPDesconto = '%' THEN
                            VLBase - (pagamentos.QTDesconto*VLBase)/100
                    ELSE
                            VLBase - pagamentos.QTDesconto
                        END
                    ELSE
                        VLBase
                END as mobra,
                NMFilial,
                DSTipoServico,
                (SELECT
                    SUM(VLVenda)
                    FROM vendas
                    LEFT JOIN produtos ON(produtos.IDProduto = vendas.IDProduto)
                    WHERE IDColaborador IN(idcol) AND STInsumo = 1) as insumos
            FROM ordemservico
            LEFT JOIN custosordem cu ON(cu.IDOrdem = ordemservico.IDOrdem)
            LEFT JOIN colaboradores ON(colaboradores.IDColaborador = ordemservico.IDColaborador)
            LEFT JOIN pagamentos USING(IDPagamento)
            LEFT JOIN vendas vn ON(vn.IDProduto = cu.IDProduto)
            LEFT JOIN produtos pr ON(pr.IDProduto = vn.IDProduto)
            LEFT JOIN servicos USING(IDServico)
            LEFT JOIN clientes ON(ordemservico.IDCliente = clientes.IDCliente)
            LEFT JOIN filiais ON(servicos.IDFilial = filiais.IDFilial)
            WHERE servicos.IDFilial = ? AND STServico = 1 
            GROUP BY idord",
            [$IDFilial]
        );
    }

    /**
     * Verifica se existem ordens de serviço vinculadas a um serviço.
     *
     * @param  int  $IDServico
     * @return \App\Models\OrdemServico|null
     */
    public static function confereOrdem($IDServico)
    {
        return OrdemServico::where('IDServico', $IDServico)->first();
    }

    /**
     * Retorna lista de insumos (produtos STInsumo = 1) de uma filial com dados do fornecedor.
     *
     * @param  int   $IDFilial
     * @return array
     */
    public static function getInsumos($IDFilial)
    {
        return DB::select(
            "SELECT * FROM produtos p 
             INNER JOIN fornecedores f USING(IDFornecedor) 
             INNER JOIN categorias c USING(IDCategoria) 
             LEFT JOIN compras USING(IDProduto) 
             WHERE f.IDFilial = ? AND p.STInsumo = 1 AND p.STDelete IS NULL 
             GROUP BY p.IDProduto",
            [$IDFilial]
        );
    }

    /**
     * Retorna lista de ordens de serviço de uma filial com dados do cliente e colaborador.
     *
     * @param  int   $IDFilial
     * @return array
     */
    public static function getOrdens($IDFilial)
    {
        return DB::select(
            "SELECT
                s.DSTipoServico,
                cli.NUTelefoneCliente,
                cli.NMEmailCliente,
                cli.NMCliente,
                cli.IDCliente,
                o.IDColaborador,
                s.DSGarantiaServico,
                CASE WHEN o.IDColaborador = 0 THEN 'Você' ELSE c.NMColaborador END as NMColaborador,
                o.IDOrdem,
                o.DTSaida,
                o.DTServico,
                o.STServico
             FROM ordemservico o 
             INNER JOIN servicos s USING(IDServico) 
             LEFT JOIN colaboradores c USING(IDColaborador) 
             INNER JOIN clientes cli USING(IDCliente) 
             WHERE s.IDFilial = ?",
            [$IDFilial]
        );
    }

    /**
     * Retorna lista de serviços ativos de uma filial.
     *
     * @param  int  $IDFilial
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getServicos($IDFilial)
    {
        return Servico::where('IDFilial', $IDFilial)
            ->whereNull('STDelete')
            ->get();
    }

    /**
     * Registra uma compra de insumo e atualiza o custo total do produto.
     *
     * @param  array  $dados  (Insumo, Quantidade, Custo)
     * @return \App\Models\Compra
     */
    public static function setCompra($dados)
    {
        DB::beginTransaction();
        try {
            // Cria o registro de compra
            $compra = Compra::create([
                'IDProduto' => $dados['Insumo'],
                'QTCompra'  => $dados['Quantidade'],
            ]);

            // Atualiza o custo total do produto
            Produto::where('IDProduto', $dados['Insumo'])
                ->increment('NUCustoTotal', $dados['Custo']);

            DB::commit();
            return $compra;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Salva ou atualiza um serviço.
     *
     * @param  array  $dados  Dados do serviço
     * @return \App\Models\Servico
     */
    public static function setServico($dados)
    {
        $IDFilial = $_SESSION['login']['filial'];

        $garantiaJson = json_encode([
            "Tipo"  => $dados['tipoGarantia'],
            "Tempo" => $dados['tempoGarantia'],
        ]);

        if (!empty($dados['IDServico'])) {
            // Atualização
            $servico = Servico::find($dados['IDServico']);
            if ($servico) {
                $servico->update([
                    'DSGarantiaServico' => $garantiaJson,
                    'VLBase'            => $dados['valorBase'],
                ]);
            }
        } else {
            // Criação
            $servico = Servico::create([
                'VLBase'            => $dados['valorBase'],
                'DSTipoServico'     => $dados['tipoServico'],
                'IDFilial'          => $IDFilial,
                'DSGarantiaServico' => $garantiaJson,
            ]);
        }

        return $servico;
    }

    /**
     * Retorna um serviço específico pelo ID.
     *
     * @param  int  $IDServico
     * @return \App\Models\Servico|null
     */
    public static function getServico($IDServico)
    {
        return Servico::find($IDServico);
    }

    /**
     * Cria uma nova ordem de serviço.
     *
     * @param  array  $dados  Dados da ordem (tipoOrdemServico, nomeClienteServico, previaServico, descricaoServico)
     * @return \App\Models\OrdemServico
     */
    public static function setOrdemServico($dados)
    {
        $colaborador = ContratosController::getColaboradorByUser($_SESSION['login']['dados']['id']);

        return OrdemServico::create([
            'IDServico'      => $dados['tipoOrdemServico'],
            'IDCliente'      => $dados['nomeClienteServico'],
            'IDColaborador'  => $colaborador,
            'DSOrdemServico' => $dados['previaServico'],
            'DSServico'      => $dados['descricaoServico'],
        ]);
    }

    /**
     * Retorna lista de custos (insumos) com indicador de vínculo a uma ordem.
     * Query complexa com subquery CASE WHEN.
     *
     * @param  int   $IDOrdem
     * @return array
     */
    public static function getCustos($IDOrdem)
    {
        $filialId = $_SESSION['login']['filial'];

        return DB::select(
            "SELECT pd.NMProduto, pd.IDProduto, co.NUQuantidade, 
                    CASE WHEN (SELECT COUNT(IDProduto) FROM custosordem 
                              WHERE IDOrdem = ? AND IDProduto = pd.IDProduto) > 0 
                    THEN 1 ELSE 0 END as vinculo 
             FROM produtos pd 
             LEFT JOIN custosordem co USING(IDProduto) 
             LEFT JOIN fornecedores ON(pd.IDFornecedor = fornecedores.IDFornecedor) 
             WHERE STInsumo = 1 AND NUEstoqueProduto > 0 AND IDFilial = ? 
             GROUP BY pd.IDProduto",
            [$IDOrdem, $filialId]
        );
    }

    /**
     * Realiza a baixa de um serviço (finaliza a ordem).
     * Atualiza nota, data de saída, status e cria registros de venda para cada insumo.
     * Método complexo que mantém compatibilidade com a lógica de negócio original.
     *
     * @param  array  $dados  (nota, pagamento, ordem, cliente, colaborador)
     * @return int
     */
    public static function baixaServico($dados)
    {
        $ordem     = $dados['ordem'];
        $nota      = $dados['nota'];
        $pagamento = $dados['pagamento'];
        $cliente    = $dados['cliente'];
        $colaborador = $dados['colaborador'];
        $filialId   = $_SESSION['login']['filial'];

        // Busca os dados da ordem (tipo "saida") para obter mão de obra
        $getOrdem = self::getOrdem([
            "Tipo"    => "saida",
            "IDFilial" => $filialId,
            "IDOrdem"  => $ordem,
        ]);

        // Se houver mão de obra vinculada, cria registros de venda
        if (!empty($getOrdem) && isset($getOrdem->maodeobra)) {
            $mobra = json_decode($getOrdem->maodeobra, true);

            if (is_array($mobra)) {
                foreach ($mobra as $m) {
                    // Busca dados do produto via query (sem model dedicado para esse método legado)
                    $dadosProduto = DB::select(
                        "SELECT * FROM produtos WHERE IDProduto = ?",
                        [$m['id']]
                    );

                    if (!empty($dadosProduto[0])) {
                        $prod = $dadosProduto[0];

                        // Calcula valor com promoção
                        $valorComDesconto = PromocoesController::confProdutoPromocional(
                            $m['id'],
                            $m['valor'],
                            $filialId
                        );

                        // Registra a venda
                        DB::insert(
                            "INSERT INTO vendas 
                             (IDProduto, IDFornecedor, IDPromocao, IDCliente, IDColaborador, 
                              IDCaixa, NUUnidadesVendidas, IDFilial, IDPagamento, VLVenda, CDVenda, IDOrdem) 
                             VALUES (?, ?, ?, ?, ?, 0, ?, ?, ?, ?, '', ?)",
                            [
                                $m['id'],
                                $prod->IDFornecedor ?? 0,
                                $prod->IDPromocao ?? 0,
                                $cliente,
                                $colaborador,
                                $m['quantidade'],
                                $filialId,
                                $pagamento,
                                $m['quantidade'] * $valorComDesconto,
                                $ordem,
                            ]
                        );
                    }
                }
            }
        }

        // Atualiza a ordem com os dados de baixa
        return DB::update(
            "UPDATE ordemservico SET DSNota = ?, DTSaida = NOW(), STServico = 1, IDPagamento = ? WHERE IDOrdem = ?",
            [$nota, $pagamento, $ordem]
        );
    }

    /**
     * Vincula um insumo (mão de obra) a uma ordem de serviço.
     *
     * @param  array  $dados  (IDOrdem, IDProduto, NUQuantidade)
     * @return \App\Models\CustosOrdem
     */
    public static function setCusto($dados)
    {
        return CustosOrdem::create([
            'IDOrdem'      => $dados['IDOrdem'],
            'IDProduto'    => $dados['IDProduto'],
            'NUQuantidade' => $dados['NUQuantidade'],
        ]);
    }

    /**
     * Remove todos os custos (insumos) vinculados a uma ordem.
     *
     * @param  int  $ID  ID da ordem
     * @return int  Número de registros deletados
     */
    public static function delCusto($ID)
    {
        return CustosOrdem::where('IDOrdem', $ID)->delete();
    }

    /**
     * Exclui um serviço.
     * Se houver ordens vinculadas, faz soft delete (STDelete = 1).
     * Caso contrário, faz hard delete.
     *
     * @param  int  $ID
     * @return bool|int
     */
    public static function delServico($ID)
    {
        if (self::confereOrdem($ID)) {
            // Soft delete
            return Servico::where('IDServico', $ID)
                ->update(['STDelete' => 1]);
        }

        // Hard delete
        return Servico::destroy($ID);
    }

    /**
     * Exclui uma ordem de serviço.
     *
     * @param  int  $ID
     * @return bool|null
     */
    public static function delOrdemServico($ID)
    {
        return OrdemServico::destroy($ID);
    }

    /**
     * Retorna os dados de uma ordem de serviço (entrada ou saída).
     * Query complexa que varia conforme o tipo.
     *
     * @param  array       $dados  (Tipo, IDFilial, IDOrdem)
     * @return object|null
     */
    public static function getOrdem($dados)
    {
        $IDFilial = $dados['IDFilial'];
        $IDOrdem  = $dados['IDOrdem'];

        if ($dados['Tipo'] == "entrada") {
            $SQL = "
                SELECT 
                    e.NMRazaoEmpresa as empresa,
                    f.DSEnderecoJSON as endereco,
                    c.NMCliente as cliente,
                    f.NMFilial as filial,
                    s.DSTipoServico as servico,
                    os.DSOrdemServico as previa,
                    os.DSServico as descricao,
                    CASE WHEN os.IDColaborador = 0 THEN e.NMRazaoEmpresa ELSE col.NMColaborador END as atendente,
                    os.IDOrdem as codigo,
                    e.NUCnpjEmpresa as cnpj,
                    os.DTServico as dataHora 
                FROM empresas e
                INNER JOIN filiais f USING(IDEmpresa)
                INNER JOIN clientes c USING(IDFilial)
                INNER JOIN ordemservico os USING(IDCliente)
                LEFT JOIN colaboradores as col USING(IDColaborador)
                INNER JOIN servicos s USING(IDServico)
                WHERE f.IDFilial = ? AND os.IDOrdem = ?
            ";
        } else {
            $SQL = "
                SELECT 
                    e.NMRazaoEmpresa as empresa,
                    f.DSEnderecoJSON as endereco,
                    c.NMCliente as cliente,
                    f.NMFilial as filial,
                    s.DSTipoServico as servico,
                    os.DSOrdemServico as previa,
                    os.DSServico as descricao,
                    col.NMColaborador as atendente,
                    os.IDOrdem as codigo,
                    os.DTSaida saida,
                    os.DSNota mensagem,
                    os.DTServico as dataHora,
                    pag.QTParcelas as parcelas,
                    pag.NUJuros as juros,
                    pag.DSMetodo as metodo,
                    (SELECT
                        CONCAT('[',
                            GROUP_CONCAT(
                            '{'
                            ,'\"produto\":\"',prod.NMProduto,'\"'
                            ,',\"valor\":\"',prod.NUValorProduto,'\"'
                            ,',\"quantidade\":\"',custos.NUQuantidade,'\"'
                            ,',\"id\":\"',prod.IDProduto,'\"'
                            ,'}' 
                        SEPARATOR ','),
                    ']')
                    FROM custosordem custos 
                    INNER JOIN produtos prod USING(IDProduto) 
                    LEFT JOIN promocionais k USING(IDProduto) 
                    LEFT JOIN promocoes y USING(IDPromocao) 
                    WHERE custos.IDOrdem = ? ) as maodeobra,
                    e.NUCnpjEmpresa as cnpj,
                    s.VLBase as mobra,
                    pag.IDPagamento as id_pagamento
                FROM empresas e
                LEFT JOIN filiais f USING(IDEmpresa)
                LEFT JOIN clientes c USING(IDFilial)
                LEFT JOIN ordemservico os USING(IDCliente)
                LEFT JOIN colaboradores as col USING(IDColaborador)
                LEFT JOIN servicos s USING(IDServico)
                LEFT JOIN custosordem cst USING(IDOrdem)
                LEFT JOIN produtos prv USING(IDProduto)
                LEFT JOIN pagamentos pag USING(IDPagamento)
                WHERE f.IDFilial = ? AND os.IDOrdem = ?
            ";
        }

        $params = ($dados['Tipo'] == "entrada")
            ? [$IDFilial, $IDOrdem]
            : [$IDOrdem, $IDFilial, $IDOrdem];

        $result = DB::select($SQL, $params);

        return !empty($result) ? $result[0] : null;
    }
}