<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RelatoriosController extends Controller
{
    /**
     * Relatório de freguesia de serviços (clientes x ordens de serviço).
     */
    public static function getFreguesiaServicos($IDFilial)
    {
        return DB::select(
            "SELECT
                ordemservico.IDColaborador as idcol,
                IDOrdem as idord,
                ordemservico.IDCliente as idcli,
                COUNT(IDOrdem) as Quantidade,
                NMCliente as nome,
                CASE WHEN pagamentos.QTDesconto > 0 THEN
                    CASE WHEN pagamentos.TPDesconto = '%' THEN
                        SUM(VLBase) - (pagamentos.QTDesconto*SUM(VLBase))/100
                    ELSE
                        SUM(VLBase) - pagamentos.QTDesconto
                    END
                ELSE
                    SUM(VLBase)
                END as mobra,
                NMFilial,
                (SELECT SUM(VLVenda - NUCustoProduto) FROM vendas
                 LEFT JOIN produtos ON(produtos.IDProduto = vendas.IDProduto)
                 WHERE IDColaborador IN(idcol) AND IDCliente IN(idcli) AND STInsumo = 1) as vendas,
                (SELECT SUM(VLVenda) FROM vendas
                 LEFT JOIN produtos ON(produtos.IDProduto = vendas.IDProduto)
                 WHERE IDColaborador IN(idcol) AND IDCliente IN(idcli) AND STInsumo = 1) as vendasBrutas
            FROM ordemservico
            LEFT JOIN custosordem USING(IDOrdem)
            LEFT JOIN clientes ON(clientes.IDCliente = ordemservico.IDCliente)
            LEFT JOIN pagamentos USING(IDPagamento)
            LEFT JOIN produtos USING(IDProduto) 
            LEFT JOIN servicos USING(IDServico)
            LEFT JOIN filiais ON(servicos.IDFilial = filiais.IDFilial)
            WHERE filiais.IDFilial = ?
            GROUP BY NMCliente",
            [$IDFilial]
        );
    }

    /**
     * Relatório de freguesia de produtos (clientes x vendas de produtos).
     */
    public static function getFrueguesiaProdutos($IDFilial)
    {
        return DB::select(
            "SELECT
                NMCliente as nome,
                COUNT(IDVenda) as Quantidade,
                SUM(VLVenda - NUCustoProduto) as compras,
                SUM(VLVenda) as comprasBrutas
            FROM clientes
            LEFT JOIN vendas USING(IDCliente)
            LEFT JOIN produtos ON(produtos.IDProduto = vendas.IDProduto)
            LEFT JOIN filiais ON(vendas.IDFilial = filiais.IDFilial)
            WHERE STInsumo = 0 AND filiais.IDFilial = ? AND STVenda = 1
            GROUP BY NMCliente",
            [$IDFilial]
        );
    }

    /**
     * Painel administrativo: resumo de contratos, MRR, ARR e planos.
     */
    public static function getAdminPanel()
    {
        $result = DB::select(
            "SELECT 
                COUNT(IDContrato) as qtContratos,
                CASE WHEN TMPlano = 30 THEN SUM(VLPlano) END as mrr,
                CASE WHEN TMPlano = 30 THEN SUM(VLPlano)*12 END as arr,
                (SELECT COUNT(IDContrato) FROM contratos WHERE STContrato = 0) as qtInativos,
                (SELECT COUNT(IDContrato) FROM contratos WHERE IDPlano = 1) as starter,
                (SELECT COUNT(IDContrato) FROM contratos WHERE IDPlano = 2) as ecommerce,
                (SELECT COUNT(IDContrato) FROM contratos WHERE IDPlano = 3) as fiscal,
                (SELECT COUNT(IDContrato) FROM contratos WHERE IDPlano = 4) as ecommercefiscal
            FROM contratos INNER JOIN planos USING(IDPlano)"
        );

        return !empty($result) ? $result[0] : null;
    }

    /**
     * Relatório de categorias e serviços vendidos (filial ou empresa).
     */
    public static function getCatAndServ($dado)
    {
        $empresaId = $_SESSION['login']['empresa'];

        if ($dado == "filial") {
            $empresaParam  = $empresaId;
            $filialParam   = $_SESSION['login']['filial'];
            $whereCat      = "IDEmpresa = ? AND filiais.IDFilial = ?";
            $whereServ     = "IDEmpresa = ? AND filiais.IDFilial = ?";
            $paramsCat     = [$empresaParam, $filialParam];
            $paramsServ    = [$empresaParam, $filialParam];
        } elseif ($dado == "empresa") {
            $whereCat  = "IDEmpresa = ?";
            $whereServ = "IDEmpresa = ?";
            $paramsCat  = [$empresaId];
            $paramsServ = [$empresaId];
        } else {
            $whereCat  = "IDEmpresa = ? AND filiais.IDFilial = ?";
            $whereServ = "IDEmpresa = ? AND filiais.IDFilial = ?";
            $paramsCat  = [$empresaId, $dado];
            $paramsServ = [$empresaId, $dado];
        }

        $sqlCat = DB::select(
            "SELECT 
                SUM(NUUnidadesVendidas) as vendas,
                DSCategoria as categoria
            FROM vendas
            INNER JOIN produtos USING(IDProduto)
            INNER JOIN categorias USING(IDCategoria)
            INNER JOIN filiais ON(filiais.IDFilial = categorias.IDFilial)
            INNER JOIN empresas USING(IDEmpresa)
            WHERE $whereCat
            GROUP BY DSCategoria",
            $paramsCat
        );

        $sqlServ = DB::select(
            "SELECT 
                DSTipoServico as nome,
                COUNT(IDServico) as quantidade
            FROM servicos
            INNER JOIN ordemservico USING(IDServico)
            INNER JOIN filiais ON(filiais.IDFilial = servicos.IDFilial)
            INNER JOIN empresas USING(IDEmpresa)
            WHERE $whereServ
            GROUP BY DSTipoServico",
            $paramsServ
        );

        return [
            "CAT"  => $sqlCat,
            "SERV" => $sqlServ,
        ];
    }

    /**
     * Relatório de vendas agrupadas por método de pagamento.
     */
    public static function getPags($IDFilial)
    {
        return DB::select(
            "SELECT 
                SUM(NUUnidadesVendidas) as vendas,
                NMPagamento as pagamento
            FROM vendas
            INNER JOIN produtos USING(IDProduto)
            INNER JOIN pagamentos ON(vendas.IDPagamento = pagamentos.IDPagamento)
            INNER JOIN filiais ON(filiais.IDFilial = pagamentos.IDFilial)
            WHERE filiais.IDFilial = ?
            GROUP BY pagamentos.IDPagamento",
            [$IDFilial]
        );
    }

    /**
     * Relatório de vendas agrupadas por promoção ativa.
     */
    public static function getPromos($IDFilial)
    {
        return DB::select(
            "SELECT 
                SUM(NUUnidadesVendidas) as vendas,
                NMPromo as promo
            FROM vendas
            INNER JOIN produtos USING(IDProduto)
            INNER JOIN promocoes ON(vendas.IDPromocao = promocoes.IDPromocao)
            INNER JOIN filiais ON(filiais.IDFilial = promocoes.IDFilial)
            WHERE filiais.IDFilial = ? 
              AND NOW() >= promocoes.DTInicioPromo 
              AND NOW() <= promocoes.DTTerminoPromo
            GROUP BY promocoes.NMPromo",
            [$IDFilial]
        );
    }

    /**
     * Helper: constrói as cláusulas WHERE para filtros de filial/empresa/data.
     */
    private static function buildWhereClauses($dado, $data, $tipo, $campoData)
    {
        $empresaId = $_SESSION['login']['empresa'] ?? 0;

        $main       = '';
        $subF       = '';
        $subV       = '';
        $mainParams = [];
        $subFParams = [];
        $subVParams = [];

        if ($dado == "filial") {
            $filial = $_SESSION['login']['filial'];
            $subF   = "ef.IDEmpresa = ? AND ff.IDFilial = ?";
            $subV   = "ev.IDEmpresa = ? AND lv.IDFilial = ?";
            $subFParams = [$empresaId, $filial];
            $subVParams = [$empresaId, $filial];

            if (!empty($tipo)) {
                $main       = "IDEmpresa = ? AND filiais.IDFilial = ? AND $campoData LIKE ?";
                $mainParams = [$empresaId, $filial, "%$data%"];
            } else {
                $main       = "IDEmpresa = ? AND filiais.IDFilial = ?";
                $mainParams = [$empresaId, $filial];
            }
        } elseif ($dado == "empresa") {
            $subF   = "ef.IDEmpresa = ? AND ff.IDFilial IN (SELECT IDFilial FROM filiais WHERE IDEmpresa = ?)";
            $subV   = "ev.IDEmpresa = ? AND lv.IDFilial IN (SELECT IDFilial FROM filiais WHERE IDEmpresa = ?)";
            $subFParams = [$empresaId, $empresaId];
            $subVParams = [$empresaId, $empresaId];

            if (!empty($tipo)) {
                $main       = "IDEmpresa = ? AND $campoData LIKE ?";
                $mainParams = [$empresaId, "%$data%"];
            } else {
                $main       = "IDEmpresa = ?";
                $mainParams = [$empresaId];
            }
        } else {
            $subF   = "ef.IDEmpresa = ? AND ff.IDFilial = ?";
            $subV   = "ev.IDEmpresa = ? AND lv.IDFilial = ?";
            $subFParams = [$empresaId, $dado];
            $subVParams = [$empresaId, $dado];

            if (!empty($tipo)) {
                $main       = "IDEmpresa = ? AND filiais.IDFilial = ? AND $campoData LIKE ?";
                $mainParams = [$empresaId, $dado, "%$data%"];
            } else {
                $main       = "IDEmpresa = ? AND filiais.IDFilial = ?";
                $mainParams = [$empresaId, $dado];
            }
        }

        return compact('main', 'subF', 'subV', 'mainParams', 'subFParams', 'subVParams');
    }

    /**
     * Relatório de vendas (produtos): faturamento e lucro total/hoje.
     */
    public static function getVendas($dado, $data, $tipo)
    {
        $w = self::buildWhereClauses($dado, $data, $tipo, 'DTVenda');

        $result = DB::select(
            "SELECT 
                SUM(p.NUValorProduto * v.NUUnidadesVendidas) as faturamentovendastotal,
                SUM(v.VLVenda - p.NUCustoProduto * v.NUUnidadesVendidas) as lucrovendastotal,
                (SELECT SUM(NUValorProduto * NUUnidadesVendidas)
                 FROM produtos 
                 INNER JOIN vendas USING(IDProduto) 
                 INNER JOIN filiais ff USING(IDFilial) 
                 INNER JOIN empresas ef USING(IDEmpresa) 
                 WHERE {$w['subF']} 
                   AND DATE_FORMAT(DTVenda,'%Y-%m-%d') = DATE_FORMAT(NOW(),'%Y-%m-%d') 
                   AND IDProduto NOT IN(SELECT IDProduto FROM custosordem)
                ) as faturamentovendashoje,
                (SELECT SUM(VLVenda - NUCustoProduto * NUUnidadesVendidas)
                 FROM produtos 
                 INNER JOIN vendas USING(IDProduto) 
                 INNER JOIN filiais lv USING(IDFilial) 
                 INNER JOIN empresas ev USING(IDEmpresa) 
                 WHERE {$w['subV']} 
                   AND DATE_FORMAT(DTVenda,'%Y-%m-%d') = DATE_FORMAT(NOW(),'%Y-%m-%d') 
                   AND IDProduto NOT IN(SELECT IDProduto FROM custosordem)
                ) as lucrovendashoje
            FROM produtos p 
            INNER JOIN vendas v USING(IDProduto) 
            INNER JOIN filiais USING(IDFilial) 
            INNER JOIN empresas USING(IDEmpresa) 
            WHERE {$w['main']} 
              AND IDProduto NOT IN(SELECT IDProduto FROM custosordem)",
            array_merge($w['mainParams'], $w['subFParams'], $w['subVParams'])
        );

        return !empty($result) ? $result[0] : null;
    }

    /**
     * Relatório de serviços realizados hoje ou no total.
     */
    public static function getServicosHoje($IDFilial, $hoje)
    {
        $where = $hoje ? "AND DATE_FORMAT(DTServico,'%Y-%m-%d') = DATE_FORMAT(NOW(),'%Y-%m-%d')" : "";

        return DB::select(
            "SELECT
                ordemservico.IDColaborador as idcol,
                IDOrdem as idord,
                DSTipoServico,
                CASE WHEN pagamentos.QTDesconto > 0 THEN
                    CASE WHEN pagamentos.TPDesconto = '%' THEN
                        SUM(VLBase) - (pagamentos.QTDesconto*SUM(VLBase))/100
                    ELSE
                        SUM(VLBase) - pagamentos.QTDesconto
                    END
                ELSE
                    SUM(VLBase)
                END as mobra,
                COUNT(IDServico) as Quantidade,
                (SELECT SUM(VLVenda - produtos.NUCustoProduto) FROM vendas
                 LEFT JOIN produtos ON(produtos.IDProduto = vendas.IDProduto)
                 WHERE IDColaborador IN(idcol) AND STInsumo = 1) as insumos,
                (SELECT SUM(VLVenda) FROM vendas
                 LEFT JOIN produtos ON(produtos.IDProduto = vendas.IDProduto)
                 WHERE IDColaborador IN(idcol) AND STInsumo = 1) as insumosBruto
            FROM ordemservico
            LEFT JOIN custosordem USING(IDOrdem)
            LEFT JOIN colaboradores ON(colaboradores.IDColaborador = ordemservico.IDColaborador)
            LEFT JOIN pagamentos USING(IDPagamento)
            LEFT JOIN produtos USING(IDProduto) 
            LEFT JOIN servicos USING(IDServico)
            LEFT JOIN clientes USING(IDCliente)
            LEFT JOIN filiais ON(servicos.IDFilial = filiais.IDFilial)
            WHERE servicos.IDFilial = ? $where
            GROUP BY DSTipoServico",
            [$IDFilial]
        );
    }

    /**
     * Relatório de vendas de produtos hoje ou no total.
     */
    public static function getVendasHoje($IDFilial, $hoje)
    {
        $where = $hoje ? "AND DATE_FORMAT(DTVenda,'%Y-%m-%d') = DATE_FORMAT(NOW(),'%Y-%m-%d')" : "";

        return DB::select(
            "SELECT
                COUNT(produtos.IDProduto) as Quantidade,
                SUM(VLVenda - NUCustoProduto) as produtos,
                SUM(VLVenda) as produtosBruto,
                NMProduto
            FROM vendas
            LEFT JOIN produtos ON(produtos.IDProduto = vendas.IDProduto)
            LEFT JOIN filiais ON(vendas.IDFilial = filiais.IDFilial)
            WHERE STInsumo = 0 AND filiais.IDFilial = ? AND STVenda = 1 $where
            GROUP BY NMProduto",
            [$IDFilial]
        );
    }

    /**
     * Relatório de serviços: faturamento e lucro total/hoje.
     * Query extremamente complexa com CASE WHEN aninhados e subqueries.
     */
    public static function getServicos($dado, $data, $tipo)
    {
        $empresaId = $_SESSION['login']['empresa'] ?? 0;

        if ($dado == "filial") {
            $filial      = $_SESSION['login']['filial'];
            $whereF      = "emp.IDEmpresa = ? AND fil.IDFilial = ?";
            $whereI      = "ei.IDEmpresa = ? AND fi.IDFilial = ?";
            $whereFParams = [$empresaId, $filial];
            $whereIParams = [$empresaId, $filial];

            if (!empty($tipo)) {
                $main       = "IDEmpresa = ? AND filiais.IDFilial = ? AND DTSaida LIKE ?";
                $mainParams = [$empresaId, $filial, "%$data%"];
            } else {
                $main       = "IDEmpresa = ? AND filiais.IDFilial = ?";
                $mainParams = [$empresaId, $filial];
            }
        } elseif ($dado == "empresa") {
            $whereF      = "emp.IDEmpresa = ?";
            $whereI      = "ei.IDEmpresa = ?";
            $whereFParams = [$empresaId];
            $whereIParams = [$empresaId];

            if (!empty($tipo)) {
                $main       = "IDEmpresa = ? AND DTSaida LIKE ?";
                $mainParams = [$empresaId, "%$data%"];
            } else {
                $main       = "IDEmpresa = ?";
                $mainParams = [$empresaId];
            }
        } else {
            $whereF      = "emp.IDEmpresa = ? AND fil.IDFilial = ?";
            $whereI      = "ei.IDEmpresa = ? AND fi.IDFilial = ?";
            $whereFParams = [$empresaId, $dado];
            $whereIParams = [$empresaId, $dado];

            if (!empty($tipo)) {
                $main       = "IDEmpresa = ? AND filiais.IDFilial = ? AND DTSaida LIKE ?";
                $mainParams = [$empresaId, $dado, "%$data%"];
            } else {
                $main       = "IDEmpresa = ? AND filiais.IDFilial = ?";
                $mainParams = [$empresaId, $dado];
            }
        }

        $result = DB::select(
            "SELECT 
                CASE WHEN NUValorProduto IS NOT NULL THEN SUM(NUValorProduto) + SUM(VLBase) ELSE SUM(VLBase) END as faturamentoservicos_total,
                CASE WHEN NUValorProduto IS NOT NULL THEN
                    CASE WHEN pagamentos.QTDesconto > 0 THEN
                        CASE WHEN pagamentos.TPDesconto = '%' THEN
                            SUM(VLBase) + SUM(VLvenda) - (pagamentos.QTDesconto*SUM(VLBase) + SUM(VLVenda))/100
                        ELSE SUM(VLBase) + SUM(VLVenda) - pagamentos.QTDesconto END
                    ELSE SUM(VLVenda) + SUM(VLBase) END
                ELSE
                    CASE WHEN pagamentos.QTDesconto > 0 THEN
                        CASE WHEN pagamentos.TPDesconto = '%' THEN
                            SUM(VLBase) - (pagamentos.QTDesconto*SUM(VLBase) + SUM(VLVenda))/100
                        ELSE SUM(VLBase) - pagamentos.QTDesconto END
                    ELSE SUM(VLBase) END
                END as lucroservicos_total,
                (SELECT 
                    CASE WHEN NUValorProduto IS NOT NULL THEN
                        CASE WHEN pagamentos.QTDesconto > 0 THEN
                            CASE WHEN pagamentos.TPDesconto = '%' THEN
                                SUM(VLBase) + SUM(VLvenda) - (pagamentos.QTDesconto*SUM(VLBase) + SUM(VLVenda))/100
                            ELSE SUM(VLBase) + SUM(VLVenda) - pagamentos.QTDesconto END
                        ELSE SUM(VLVenda) + SUM(VLBase) END
                    ELSE
                        CASE WHEN pagamentos.QTDesconto > 0 THEN
                            CASE WHEN pagamentos.TPDesconto = '%' THEN
                                SUM(VLBase) - (pagamentos.QTDesconto*SUM(VLBase) + SUM(VLVenda))/100
                            ELSE SUM(VLBase) - pagamentos.QTDesconto END
                        ELSE SUM(VLBase) END
                    END
                 FROM ordemservico 
                 LEFT JOIN custosordem USING(IDOrdem) 
                 LEFT JOIN pagamentos USING(IDPagamento)
                 LEFT JOIN produtos USING(IDProduto) 
                 LEFT JOIN servicos USING(IDServico) 
                 LEFT JOIN vendas USING(IDProduto)
                 LEFT JOIN promocoes USING(IDPromocao)
                 LEFT JOIN promocionais USING(IDProduto)
                 LEFT JOIN filiais fil ON(servicos.IDFilial = fil.IDFilial) 
                 LEFT JOIN empresas emp USING(IDEmpresa) 
                 WHERE $whereF AND STServico = 1 
                   AND DATE_FORMAT(DTSaida,'%Y-%m-%d') = DATE_FORMAT(NOW(),'%Y-%m-%d')
                ) as lucroservicos_hoje,
                (SELECT
                    CASE WHEN NUValorProduto IS NOT NULL THEN SUM(NUValorProduto) + SUM(VLBase) ELSE SUM(VLBase) END 
                 FROM ordemservico 
                 LEFT JOIN custosordem USING(IDOrdem) 
                 LEFT JOIN produtos USING(IDProduto) 
                 LEFT JOIN servicos USING(IDServico) 
                 LEFT JOIN filiais as fi ON(servicos.IDFilial = fi.IDFilial) 
                 LEFT JOIN empresas as ei USING(IDEmpresa) 
                 WHERE $whereI AND STServico = 1 
                   AND DATE_FORMAT(DTSaida,'%Y-%m-%d') = DATE_FORMAT(NOW(),'%Y-%m-%d')
                ) as faturamentoservico_hoje
            FROM ordemservico 
            LEFT JOIN custosordem USING(IDOrdem) 
            LEFT JOIN pagamentos USING(IDPagamento)
            LEFT JOIN produtos USING(IDProduto) 
            LEFT JOIN servicos USING(IDServico) 
            LEFT JOIN vendas USING(IDProduto)
            LEFT JOIN promocoes USING(IDPromocao)
            LEFT JOIN promocionais USING(IDProduto)
            LEFT JOIN filiais ON(servicos.IDFilial = filiais.IDFilial) 
            LEFT JOIN empresas USING(IDEmpresa) 
            WHERE $main AND STServico = 1",
            array_merge($mainParams, $whereFParams, $whereIParams)
        );

        return !empty($result) ? $result[0] : null;
    }

    /**
     * Relatório de folha salarial (soma de salários e quantidade de colaboradores).
     */
    public static function getColaboradores($dado)
    {
        $empresaId = $_SESSION['login']['empresa'] ?? 0;

        if ($dado == "filial") {
            $where  = "IDEmpresa = ? AND filiais.IDFilial = ?";
            $params = [$empresaId, $_SESSION['login']['filial']];
        } elseif ($dado == "empresa") {
            $where  = "IDEmpresa = ?";
            $params = [$empresaId];
        } else {
            $where  = "IDEmpresa = ? AND filiais.IDFilial = ?";
            $params = [$empresaId, $dado];
        }

        $result = DB::select(
            "SELECT SUM(VLSalario) as salarios, COUNT(IDColaborador) as qtColaboradores 
             FROM colaboradores 
             INNER JOIN filiais USING(IDFilial) 
             INNER JOIN empresas USING(IDEmpresa) 
             WHERE $where",
            $params
        );

        return !empty($result) ? $result[0] : null;
    }

    /**
     * Relatório de vendas de produtos por colaborador (para comissão).
     */
    public static function getVendasProdutos($IDEmpresa)
    {
        return DB::select(
            "SELECT
                NMColaborador,
                SUM(VLVenda) as produtos,
                NUPorcentagem,
                NMFilial
            FROM colaboradores
            LEFT JOIN vendas USING(IDColaborador)
            LEFT JOIN comissionados USING(IDColaborador)
            LEFT JOIN comissoes ON(comissoes.IDComissao = comissionados.IDComissao)
            LEFT JOIN produtos ON(produtos.IDProduto = vendas.IDProduto)
            LEFT JOIN filiais ON(vendas.IDFilial = filiais.IDFilial)
            LEFT JOIN empresas ON(filiais.IDEmpresa = empresas.IDEmpresa)
            WHERE STInsumo = 0 AND empresas.IDEmpresa = ? AND STVenda = 1
            GROUP BY NMColaborador",
            [$IDEmpresa]
        );
    }

    /**
     * Relatório de vendas de serviços por colaborador (para comissão).
     */
    public static function getVendasServicos($IDEmpresa)
    {
        return DB::select(
            "SELECT
                ordemservico.IDColaborador as idcol,
                IDOrdem as idord,
                NMColaborador as nome,
                CASE WHEN pagamentos.QTDesconto > 0 THEN
                    CASE WHEN pagamentos.TPDesconto = '%' THEN
                        SUM(VLBase) - (pagamentos.QTDesconto*SUM(VLBase))/100
                    ELSE SUM(VLBase) - pagamentos.QTDesconto END
                ELSE SUM(VLBase) END as vendas,
                NMFilial,
                CASE WHEN NUPorcentagem IS NULL THEN 0 ELSE NUPorcentagem END as porcentagem,
                (SELECT SUM(VLVenda) FROM vendas
                 LEFT JOIN produtos ON(produtos.IDProduto = vendas.IDProduto)
                 WHERE IDColaborador IN(idcol) AND STInsumo = 1) as produtos
            FROM ordemservico
            LEFT JOIN custosordem USING(IDOrdem)
            LEFT JOIN colaboradores ON(colaboradores.IDColaborador = ordemservico.IDColaborador)
            LEFT JOIN pagamentos USING(IDPagamento)
            LEFT JOIN produtos USING(IDProduto) 
            LEFT JOIN servicos USING(IDServico)
            LEFT JOIN filiais ON(servicos.IDFilial = filiais.IDFilial)
            LEFT JOIN empresas USING(IDEmpresa) 
            LEFT JOIN comissionados ON(ordemservico.IDColaborador = comissionados.IDColaborador)
            LEFT JOIN comissoes ON(comissionados.IDComissao = comissoes.IDComissao)
            WHERE IDEmpresa = ?
            GROUP BY NMColaborador",
            [$IDEmpresa]
        );
    }

    /**
     * Relatório de despesas (contas a pagar).
     */
    public static function getDespesas($dado)
    {
        $empresaId = $_SESSION['login']['empresa'] ?? 0;
        $mesHoje   = date('Y-m');

        if ($dado == "filial") {
            $filial        = $_SESSION['login']['filial'];
            $where         = "IDEmpresa = ? AND filiais.IDFilial = ?";
            $whereF        = "ef.IDEmpresa = ? AND ff.IDFilial = ?";
            $params        = [$empresaId, $filial];
            $paramsMes     = [$empresaId, $filial, "%$mesHoje%"];
            $paramsVencer  = [$empresaId, $filial];
        } elseif ($dado == "empresa") {
            $where         = "IDEmpresa = ?";
            $whereF        = "ef.IDEmpresa = ?";
            $params        = [$empresaId];
            $paramsMes     = [$empresaId, "%$mesHoje%"];
            $paramsVencer  = [$empresaId];
        } else {
            $where         = "IDEmpresa = ? AND filiais.IDFilial = ?";
            $whereF        = "ef.IDEmpresa = ? AND ff.IDFilial = ?";
            $params        = [$empresaId, $dado];
            $paramsMes     = [$empresaId, $dado, "%$mesHoje%"];
            $paramsVencer  = [$empresaId, $dado];
        }

        $result = DB::select(
            "SELECT
                COUNT(IDConta) as QTContas,
                SUM(VLConta) as VLDespesas,
                (SELECT SUM(VLConta) FROM contapagar 
                 INNER JOIN filiais USING(IDFilial) 
                 INNER JOIN empresas USING(IDEmpresa) 
                 WHERE $where AND DTExpedicaoConta LIKE ?) as VLDespesasMes,
                (SELECT COUNT(IDConta) FROM contapagar 
                 INNER JOIN filiais ff USING(IDFilial) 
                 INNER JOIN empresas ef USING(IDEmpresa) 
                 WHERE $whereF AND DTVencimentoConta <= DATE_ADD(NOW(), INTERVAL 3 DAY)) as contasvencer
            FROM contapagar 
            INNER JOIN filiais USING(IDFilial) 
            INNER JOIN empresas USING(IDEmpresa) 
            WHERE $where",
            array_merge($params, [$paramsMes[count($paramsMes)-1]], $paramsVencer)
        );

        return !empty($result) ? $result[0] : null;
    }

    /**
     * Relatório de reposições/compras (custo com insumos e mercadorias).
     */
    public static function getReposicoes($dado, $data, $tipo)
    {
        $empresaId = $_SESSION['login']['empresa'] ?? 0;
        $mesHoje   = date('Y-m');

        if ($dado == "filial") {
            $filial = $_SESSION['login']['filial'];
            $where  = "IDEmpresa = ? AND filiais.IDFilial = ?";
            $mainParams = [$empresaId, $filial];

            if (!empty($tipo)) {
                $whereF       = "ef.IDEmpresa = ? AND ff.IDFilial = ? AND DTReposicao LIKE ?";
                $whereV       = "ev.IDEmpresa = ? AND lv.IDFilial = ? AND DTEntradaProduto LIKE ?";
                $subFParams   = [$empresaId, $filial, "%$data%"];
                $subVParams   = [$empresaId, $filial, "%$data%"];
            } else {
                $whereF       = "ef.IDEmpresa = ? AND ff.IDFilial = ?";
                $whereV       = "ev.IDEmpresa = ? AND lv.IDFilial = ?";
                $subFParams   = [$empresaId, $filial];
                $subVParams   = [$empresaId, $filial];
            }
        } elseif ($dado == "empresa") {
            $where  = "IDEmpresa = ?";
            $mainParams = [$empresaId];

            if (!empty($tipo)) {
                $whereF       = "IDEmpresa = ? AND DTReposicao LIKE ?";
                $whereV       = "IDEmpresa = ? AND DTEntradaProduto LIKE ?";
                $subFParams   = [$empresaId, "%$data%"];
                $subVParams   = [$empresaId, "%$data%"];
            } else {
                $whereF       = "IDEmpresa = ?";
                $whereV       = "IDEmpresa = ?";
                $subFParams   = [$empresaId];
                $subVParams   = [$empresaId];
            }
        } else {
            $where  = "IDEmpresa = ? AND filiais.IDFilial = ?";
            $mainParams = [$empresaId, $dado];

            if (!empty($tipo)) {
                $whereF       = "ef.IDEmpresa = ? AND ff.IDFilial = ? AND DTReposicao LIKE ?";
                $whereV       = "ev.IDEmpresa = ? AND lv.IDFilial = ? AND DTEntradaProduto LIKE ?";
                $subFParams   = [$empresaId, $dado, "%$data%"];
                $subVParams   = [$empresaId, $dado, "%$data%"];
            } else {
                $whereF       = "ef.IDEmpresa = ? AND ff.IDFilial = ?";
                $whereV       = "ev.IDEmpresa = ? AND lv.IDFilial = ?";
                $subFParams   = [$empresaId, $dado];
                $subVParams   = [$empresaId, $dado];
            }
        }

        $result = DB::select(
            "SELECT 
                SUM(NUCustoProduto)* QTCompra as comprasTotal,
                (SELECT SUM(NUCustoProduto * QTCompra) FROM compras 
                 LEFT JOIN produtos USING(IDProduto)
                 INNER JOIN fornecedores USING(IDFornecedor)
                 INNER JOIN filiais as ff USING(IDFilial)
                 INNER JOIN empresas as ef USING(IDEmpresa)
                 WHERE $whereF) as comprasHoje,
                (SELECT SUM(NUCustoProduto * QTCompra) FROM compras 
                 LEFT JOIN produtos USING(IDProduto)
                 INNER JOIN fornecedores USING(IDFornecedor)
                 INNER JOIN filiais as ff USING(IDFilial)
                 INNER JOIN empresas as ef USING(IDEmpresa)
                 WHERE $whereF AND DTReposicao LIKE ?) as comprasMes,
                (SELECT SUM(NUCustoTotal) FROM produtos
                 INNER JOIN fornecedores USING(IDFornecedor)
                 INNER JOIN filiais as lv USING(IDFilial)
                 INNER JOIN empresas as ev USING(IDEmpresa)
                 WHERE $whereV AND DATE_FORMAT(DTEntradaProduto,'%Y-%m-%d') = DATE_FORMAT(NOW(),'%Y-%m-%d')) as comprasMercadoriaHoje,
                (SELECT SUM(NUCustoTotal) FROM produtos
                 INNER JOIN fornecedores USING(IDFornecedor)
                 INNER JOIN filiais as lv USING(IDFilial)
                 INNER JOIN empresas as ev USING(IDEmpresa)
                 WHERE $whereV AND DTEntradaProduto LIKE ?) as comprasMercadoriaMes,
                (SELECT SUM(NUCustoTotal) FROM produtos
                 INNER JOIN fornecedores USING(IDFornecedor)
                 INNER JOIN filiais as lv USING(IDFilial)
                 INNER JOIN empresas as ev USING(IDEmpresa)
                 WHERE $whereV) as comprasMercadoria
            FROM produtos 
            LEFT JOIN compras USING(IDProduto)
            INNER JOIN fornecedores USING(IDFornecedor)
            INNER JOIN filiais USING(IDFilial)
            INNER JOIN empresas USING(IDEmpresa)
            WHERE $where",
            array_merge($mainParams, $subFParams, ["%$mesHoje%"], $subVParams, ["%$mesHoje%"], $subVParams)
        );

        return !empty($result) ? $result[0] : null;
    }

    /**
     * Relatório de comissões sobre vendas de produtos.
     */
    public static function getComissoesVendas($dado)
    {
        $empresaId = $_SESSION['login']['empresa'] ?? 0;

        if ($dado == "filial") {
            $filial       = $_SESSION['login']['filial'];
            $where        = "IDEmpresa = ? AND filiais.IDFilial = ?";
            $whereF       = "ef.IDEmpresa = ? AND ff.IDFilial = ?";
            $whereV       = "ev.IDEmpresa = ? AND lv.IDFilial = ?";
            $mainParams   = [$empresaId, $filial];
            $subFParams   = [$empresaId, $filial];
            $subVParams   = [$empresaId, $filial];
        } elseif ($dado == "empresa") {
            $where        = "IDEmpresa = ?";
            $whereF       = "ef.IDEmpresa = ?";
            $whereV       = "ev.IDEmpresa = ?";
            $mainParams   = [$empresaId];
            $subFParams   = [$empresaId];
            $subVParams   = [$empresaId];
        } else {
            $where        = "IDEmpresa = ? AND filiais.IDFilial = ?";
            $whereF       = "ef.IDEmpresa = ? AND ff.IDFilial = ?";
            $whereV       = "ev.IDEmpresa = ? AND lv.IDFilial = ?";
            $mainParams   = [$empresaId, $dado];
            $subFParams   = [$empresaId, $dado];
            $subVParams   = [$empresaId, $dado];
        }

        $result = DB::select(
            "SELECT
                SUM(VLVenda) as totalVendas,
                (SELECT SUM(NUPorcentagem) FROM comissoes
                 INNER JOIN comissionados USING(IDComissao)
                 INNER JOIN filiais as ff USING(IDFilial)
                 INNER JOIN empresas as ef USING(IDEmpresa)
                 WHERE $whereF) as sumPorcentagem
            FROM vendas
            INNER JOIN filiais USING(IDFilial)
            INNER JOIN empresas USING(IDEmpresa)
            WHERE IDColaborador IN(
                SELECT IDColaborador FROM comissionados
                INNER JOIN comissoes com USING(IDComissao)
                INNER JOIN filiais lv USING(IDFilial)
                INNER JOIN empresas ev USING(IDEmpresa)
                WHERE $whereV
            ) AND $where",
            array_merge($mainParams, $subFParams, $subVParams)
        );

        return !empty($result) ? $result[0] : null;
    }

    /**
     * Relatório de comissões sobre serviços.
     */
    public static function getComissoesServicos($dado)
    {
        $empresaId = $_SESSION['login']['empresa'] ?? 0;

        if ($dado == "filial") {
            $filial       = $_SESSION['login']['filial'];
            $where        = "IDEmpresa = ? AND filiais.IDFilial = ?";
            $whereF       = "ef.IDEmpresa = ? AND ff.IDFilial = ?";
            $mainParams   = [$empresaId, $filial];
            $subFParams   = [$empresaId, $filial];
        } elseif ($dado == "empresa") {
            $where        = "IDEmpresa = ?";
            $whereF       = "ef.IDEmpresa = ?";
            $mainParams   = [$empresaId];
            $subFParams   = [$empresaId];
        } else {
            $where        = "IDEmpresa = ? AND filiais.IDFilial = ?";
            $whereF       = "ef.IDEmpresa = ? AND ff.IDFilial = ?";
            $mainParams   = [$empresaId, $dado];
            $subFParams   = [$empresaId, $dado];
        }

        $result = DB::select(
            "SELECT 
                CASE WHEN NUValorProduto IS NOT NULL THEN
                    CASE WHEN pagamentos.QTDesconto > 0 THEN
                        CASE WHEN pagamentos.TPDesconto = '%' THEN
                            SUM(VLBase) + SUM(VLvenda) - (pagamentos.QTDesconto*SUM(VLBase) + SUM(VLVenda))/100
                        ELSE SUM(VLBase) + SUM(VLVenda) - pagamentos.QTDesconto END
                    ELSE SUM(VLVenda) + SUM(VLBase) END
                ELSE
                    CASE WHEN pagamentos.QTDesconto > 0 THEN
                        CASE WHEN pagamentos.TPDesconto = '%' THEN
                            SUM(VLBase) - (pagamentos.QTDesconto*SUM(VLBase) + SUM(VLVenda))/100
                        ELSE SUM(VLBase) - pagamentos.QTDesconto END
                    ELSE SUM(VLBase) END
                END as totalServicos
            FROM ordemservico 
            LEFT JOIN custosordem USING(IDOrdem)
            LEFT JOIN pagamentos USING(IDPagamento)
            LEFT JOIN produtos USING(IDProduto) 
            LEFT JOIN servicos USING(IDServico)
            LEFT JOIN filiais ON(servicos.IDFilial = filiais.IDFilial)
            LEFT JOIN empresas USING(IDEmpresa) 
            LEFT JOIN vendas USING(IDProduto)
            LEFT JOIN promocoes USING(IDPromocao)
            LEFT JOIN promocionais USING(IDProduto)
            WHERE STServico = 1 AND ordemservico.IDColaborador IN(
                SELECT IDColaborador FROM comissionados
                INNER JOIN comissoes com USING(IDComissao)
                INNER JOIN filiais ff USING(IDFilial)
                INNER JOIN empresas ef USING(IDEmpresa)
                WHERE $whereF
            ) AND $where",
            array_merge($mainParams, $subFParams)
        );

        return !empty($result) ? $result[0] : null;
    }

    /**
     * Lista de filiais com lucro consolidado (vendas + serviços - despesas + comissões).
     * Chama os métodos internos para cada filial.
     */
    public static function getFiliaisLucro()
    {
        $empresaId = $_SESSION['login']['empresa'];

        // Obtém filiais da empresa
        $filiais = ContratosController::getFiliais($empresaId);
        $arrEmpresas = [];

        foreach ($filiais as $e) {
            $idFilial = $e->IDFilial ?? $e['IDFilial'];

            $relVendas    = self::getVendas($idFilial, "", "");
            $relServicos  = self::getServicos($idFilial, "", "");
            $relColabs    = self::getColaboradores($idFilial);
            $relDespesas  = self::getDespesas($idFilial);
            $relReps      = self::getReposicoes($idFilial, "", "");
            $relComVendas = self::getComissoesVendas($idFilial);
            $relComServ   = self::getComissoesServicos($idFilial);

            $arrEmpresas[] = [
                "Nome"                => $e->NMFilial ?? $e['NMFilial'],
                "faturamentoTotal"    => $relReps->comprasMercadoria ?? 0,
                "lucroTotal"          => ($relServicos->lucroservicos_total ?? 0) + ($relVendas->lucrovendastotal ?? 0),
                "faturamentoVendas"   => $relVendas->faturamentovendastotal ?? 0,
                "faturamentoServicos" => $relServicos->faturamentoservicos_total ?? 0,
                "lucroServicos"       => $relServicos->lucroservicos_total ?? 0,
                "lucroVendas"         => $relVendas->lucrovendastotal ?? 0,
            ];
        }

        return $arrEmpresas;
    }

    /**
     * Evolução de lucro por tempo (mensal ou diário).
     */
    public static function getEmpresaLucroTempo($area, $tipo, $quanto)
    {
        $tempos = [];

        if ($tipo == "M") {
            for ($i = 0; $i <= $quanto; $i++) {
                $tempos[] = date("Y-m", strtotime(date('Y-m-01') . " -$i months"));
            }
        } else {
            for ($i = 0; $i <= $quanto; $i++) {
                $tempos[] = date("Y-m-d", strtotime(date('Y-m-d') . " -$i days"));
            }
        }

        $arrEmpresas = [];

        foreach ($tempos as $t) {
            $relVendas   = self::getVendas($area, $t, $tipo);
            $relServicos = self::getServicos($area, $t, $tipo);
            $relReps     = self::getReposicoes($area, $t, $tipo);

            $arrEmpresas[] = [
                "tempo"             => ($tipo == "M") ? date('m/Y', strtotime($t)) : date('d/m', strtotime($t)),
                "lucroTotal"        => ($relServicos->lucroservicos_total ?? 0) + ($relVendas->lucrovendastotal ?? 0),
                "faturamentoTotal"  => $relReps->comprasMercadoria ?? 0,
            ];
        }

        return $arrEmpresas;
    }
}