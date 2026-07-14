<?php

namespace App\Http\Controllers;

use App\Models\Filial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RelatoriosController extends Controller
{
    /**
     * Exibe a página de relatórios com gráfico Curva ABC.
     */
    public function index(Request $request)
    {
        $contratoId = Auth::user()->IDContrato;

        // Busca as filiais pertencentes às empresas do contrato atual
        $filiais = Filial::whereHas('empresa', function ($query) use ($contratoId) {
            $query->where('IDContrato', $contratoId);
        })->orderBy('NMFilial')->get();

        // Filial selecionada via GET ou a primeira da lista como padrão
        $filialId = $request->input('filialId', $filiais->first()->IDFilial ?? 0);

        // Processa a Curva ABC
        $abcData = $this->processarCurvaAbc($filialId);

        // Processa as Categorias Mais Vendidas
        $categoriasData = $this->processarCategoriasMaisVendidas($filialId);

        // Processa os Produtos Mais Vendidos
        $produtosMaisVendidos = $this->processarProdutosMaisVendidos($filialId);

        // Processa o gráfico de Investido vs Vendido (últimos 12 meses)
        $investidoVendidoData = $this->processarInvestidoVsVendidoMensal($filialId);

        return view('relatorios.index', compact('filialId', 'filiais', 'abcData', 'categoriasData', 'produtosMaisVendidos', 'investidoVendidoData'));
    }

    /**
     * Processa os dados da Curva ABC e retorna array completo para a view.
     *
     * @param  int   $filialId
     * @return array
     */
    private function processarCurvaAbc($filialId)
    {
        // Busca total de vendas por produto, agrupado e somado
        // Usa v.IDFilial diretamente (salvo na venda)
        $vendas = DB::select(
            "SELECT 
                p.NMProduto,
                SUM(v.VLVenda) as total_vendido
             FROM vendas v
             INNER JOIN produtos p ON p.IDProduto = v.IDProduto
             WHERE v.IDFilial = ?
             GROUP BY p.IDProduto, p.NMProduto
             ORDER BY total_vendido DESC",
            [$filialId]
        );

        if (empty($vendas)) {
            return [
                'labels'      => [],
                'valores'     => [],
                'classes'     => [],
                'acumulado'   => [],
                'totalGeral'  => 0,
                'totalItens'  => 0,
                'tabela'      => [],
                'countA'      => 0,
                'countB'      => 0,
                'countC'      => 0,
            ];
        }

        $totalGeral = array_sum(array_column($vendas, 'total_vendido'));
        $somaAcumulada = 0;
        $totalItens = count($vendas);

        $labels      = [];
        $valores     = [];
        $classes     = [];
        $acumulado   = [];
        $tabela      = [];
        $countA = $countB = $countC = 0;

        foreach ($vendas as $index => $venda) {
            $somaAcumulada += $venda->total_vendido;
            $percentualAcumulado = ($totalGeral > 0) ? ($somaAcumulada / $totalGeral) * 100 : 0;
            $percentualIndividual = ($totalGeral > 0) ? ($venda->total_vendido / $totalGeral) * 100 : 0;

            // Classificação ABC
            if ($percentualAcumulado <= 80) {
                $classe = 'A';
            } elseif ($percentualAcumulado <= 95) {
                $classe = 'B';
            } else {
                $classe = 'C';
            }

            // Contagem
            if ($classe === 'A') $countA++;
            elseif ($classe === 'B') $countB++;
            else $countC++;

            $labels[]    = $venda->NMProduto;
            $valores[]   = (float) $venda->total_vendido;
            $classes[]   = $classe;
            $acumulado[] = round($percentualAcumulado, 2);

            // Dados da tabela
            $tabela[] = [
                'posicao'    => $index + 1,
                'produto'    => $venda->NMProduto,
                'total'      => (float) $venda->total_vendido,
                'percentual' => round($percentualIndividual, 2),
                'acumulado'  => round($percentualAcumulado, 2),
                'classe'     => $classe,
            ];
        }

        return [
            'labels'      => $labels,
            'valores'     => $valores,
            'classes'     => $classes,
            'acumulado'   => $acumulado,
            'totalGeral'  => round($totalGeral, 2),
            'totalItens'  => $totalItens,
            'tabela'      => $tabela,
            'countA'      => $countA,
            'countB'      => $countB,
            'countC'      => $countC,
        ];
    }

    /**
     * Processa os dados de categorias mais vendidas.
     *
     * @param  int   $filialId
     * @return array
     */
    private function processarCategoriasMaisVendidas($filialId)
    {
        $categorias = DB::select(
            "SELECT 
                c.DSCategoria,
                SUM(v.VLVenda) as total_vendido,
                SUM(v.NUUnidadesVendidas * COALESCE(comp.custo_medio, 0)) as custo_total
             FROM vendas v
             INNER JOIN produtos p ON p.IDProduto = v.IDProduto
             INNER JOIN categorias c ON c.IDCategoria = p.IDCategoria
             LEFT JOIN (
                 SELECT IDProduto, 
                        SUM(VLUnitario * QTCompra) / SUM(QTCompra) as custo_medio
                 FROM compras
                 GROUP BY IDProduto
             ) comp ON comp.IDProduto = v.IDProduto
             WHERE v.IDFilial = ?
             GROUP BY c.IDCategoria, c.DSCategoria
             ORDER BY total_vendido DESC",
            [$filialId]
        );

        if (empty($categorias)) {
            return [
                'labels'           => [],
                'valores'          => [],
                'faturamentoTotal' => 0,
                'lucroTotal'       => 0,
                'totalItens'       => 0,
            ];
        }

        $faturamentoTotal = array_sum(array_column($categorias, 'total_vendido'));
        $custoTotalGeral  = array_sum(array_column($categorias, 'custo_total'));
        $lucroTotal       = $faturamentoTotal - $custoTotalGeral;
        $totalItens       = count($categorias);

        $labels  = [];
        $valores = [];

        foreach ($categorias as $categoria) {
            $labels[]  = $categoria->DSCategoria;
            $valores[] = (float) $categoria->total_vendido;
        }

        return [
            'labels'           => $labels,
            'valores'          => $valores,
            'faturamentoTotal' => round($faturamentoTotal, 2),
            'lucroTotal'       => round($lucroTotal, 2),
            'totalItens'       => $totalItens,
        ];
    }

    /**
     * Processa os dados de produtos mais vendidos com indicadores financeiros.
     *
     * @param  int   $filialId
     * @return array
     */
    private function processarProdutosMaisVendidos($filialId)
    {
        $produtos = DB::select(
            "SELECT 
                p.NMProduto,
                p.QTEstoque,
                SUM(v.VLVenda) as faturamento,
                COALESCE(comp.investido, 0) as valor_investido
             FROM vendas v
             INNER JOIN produtos p ON p.IDProduto = v.IDProduto
             LEFT JOIN (
                 SELECT IDProduto, SUM(VLUnitario * QTCompra) as investido
                 FROM compras
                 GROUP BY IDProduto
             ) comp ON comp.IDProduto = v.IDProduto
             WHERE v.IDFilial = ?
             GROUP BY p.IDProduto, p.NMProduto, p.QTEstoque, comp.investido
             ORDER BY faturamento DESC",
            [$filialId]
        );

        $resultado = [];

        foreach ($produtos as $produto) {
            $faturamento   = (float) $produto->faturamento;
            $investido     = (float) $produto->valor_investido;
            $lucro         = round($faturamento - $investido, 2);

            $resultado[] = [
                'nome'            => $produto->NMProduto,
                'estoque_atual'   => (int) $produto->QTEstoque,
                'valor_investido' => round($investido, 2),
                'faturamento'     => round($faturamento, 2),
                'lucro'           => $lucro,
            ];
        }

        return $resultado;
    }

    /**
     * Retorna os dados da Curva ABC em JSON para o Chart.js.
     * Endpoint mantido para compatibilidade AJAX.
     */
    public function curvaAbcData(Request $request)
    {
        $filialId = $request->input('filialId', Auth::user()->IDContrato);
        $data = $this->processarCurvaAbc($filialId);

        return response()->json([
            'labels'    => $data['labels'],
            'valores'   => $data['valores'],
            'classes'   => $data['classes'],
            'acumulado' => $data['acumulado'],
            'totalGeral'=> $data['totalGeral'],
            'totalItens'=> $data['totalItens'],
        ]);
    }

    /**
     * Processa os dados de Valor Investido x Valor Vendido por mês nos últimos 12 meses.
     *
     * @param  int   $filialId
     * @return array
     */
    private function processarInvestidoVsVendidoMensal($filialId)
    {
        // Últimos 12 meses (labels)
        $labels = [];
        $investidoMeses = [];
        $vendidoMeses = [];
        $contratoId = Auth::user()->IDContrato;

        for ($i = 11; $i >= 0; $i--) {
            $data = now()->subMonths($i);
            $ano = $data->year;
            $mes = $data->month;

            $labels[] = $data->translatedFormat('M/Y');

            // Valor investido em compras no mês (VLUnitario * QTCompra)
            $compras = DB::select(
                "SELECT COALESCE(SUM(c.VLUnitario * c.QTCompra), 0) as total_investido
                 FROM compras c
                 INNER JOIN produtos p ON p.IDProduto = c.IDProduto
                 INNER JOIN fornecedores f ON f.IDFornecedor = p.IDFornecedor
                 INNER JOIN filiais fil USING(IDFilial)
                 INNER JOIN empresas em ON(em.IDEmpresa = fil.IDEmpresa)
                 WHERE em.IDContrato = ?
                   AND YEAR(c.DTReposicao) = ?
                   AND MONTH(c.DTReposicao) = ?",
                [$contratoId, $ano, $mes]
            );
            $investidoMeses[] = round((float) $compras[0]->total_investido, 2);

            // Valor vendido no mês (soma de VLVenda)
            $vendas = DB::select(
                "SELECT COALESCE(SUM(v.VLVenda), 0) as total_vendido
                 FROM vendas v
                 WHERE v.IDFilial = ?
                   AND YEAR(v.DTVenda) = ?
                   AND MONTH(v.DTVenda) = ?",
                [$filialId, $ano, $mes]
            );
            $vendidoMeses[] = round((float) $vendas[0]->total_vendido, 2);
        }

        return [
            'labels'           => $labels,
            'investido'        => $investidoMeses,
            'vendido'          => $vendidoMeses,
            'totalInvestido'   => round(array_sum($investidoMeses), 2),
            'totalVendido'     => round(array_sum($vendidoMeses), 2),
            'totalMeses'       => 12,
        ];
    }
}
