<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\Filial;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReposicoesController extends Controller
{
    /**
     * Display a listing of the resource.
     * Filtro opcional por filial via query string ?filial=ID
     */
    public function index(Request $request)
    {
        $filialSelecionada = 1;

        $reposicoes = DB::select(
            "SELECT 
                c.IDLote,
                c.IDProduto,
                c.QTCompra,
                c.DTReposicao,
                c.VLUnitario,
                p.NMProduto,
                p.DSCodigoProduto,
                f.NMFornecedor,
                cat.DSCategoria
             FROM compras c
             INNER JOIN produtos p ON p.IDProduto = c.IDProduto
             INNER JOIN fornecedores f ON f.IDFornecedor = p.IDFornecedor
             INNER JOIN categorias cat ON cat.IDCategoria = p.IDCategoria
             WHERE f.IDFilial = ?
             ORDER BY c.DTReposicao DESC",
            [$filialSelecionada]
        );

        $filiais = Filial::orderBy('NMFilial')->get();

        return view('reposicoes.index', compact('reposicoes', 'filiais', 'filialSelecionada'));
    }

    /**
     * Show the form for creating a new reposição.
     */
    public function create(Request $request)
    {
        $filialId = 1;

        $produtos = Produto::with('fornecedor', 'categoria')
            ->whereHas('fornecedor', function ($query) use ($filialId) {
                $query->where('IDFilial', $filialId);
            })
            ->orderBy('NMProduto')
            ->get();

        $produtoSelecionado = null;
        if ($request->has('produto')) {
            $produtoSelecionado = Produto::with('fornecedor', 'categoria')
                ->find($request->input('produto'));
        }

        return view('reposicoes.create', compact('produtos', 'produtoSelecionado'));
    }

    /**
     * Store a newly created reposição.
     */
    public function store(Request $request)
    {
        $request->merge([
            'VLUnitario' => $this->decimal($request->VLUnitario),
        ]);

        $request->validate([
            'IDProduto'  => 'required|integer|exists:produtos,IDProduto',
            'QTCompra'   => 'required|integer|min:1',
            'VLUnitario' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $produto = Produto::findOrFail($request->IDProduto);

            Compra::create([
                'IDProduto'   => $produto->IDProduto,
                'QTCompra'    => $request->QTCompra,
                'DTReposicao' => now(),
                'VLUnitario'  => $request->VLUnitario ?? $produto->NUValorProduto,
            ]);

            DB::commit();

            return redirect()->route('reposicoes.index')->with('success', 'Reposição registrada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erro ao registrar reposição: ' . $e->getMessage())->withInput();
        }
    }

    // ======================
    // MÉTODO AUXILIAR
    // ======================

    /**
     * Converte valor monetário para decimal.
     * Ex: "1.234,56" → "1234.56"
     *
     * @param  string  $valor
     * @return string
     */
    public function decimal($valor)
    {
        if (substr_count($valor, ',') == 1 && substr_count($valor, '.') >= 1) {
            // Formato 1.234,56
            return str_replace(',', '.', str_replace('.', '', $valor));
        }
        // Formato simples 1234,56 → 1234.56
        return str_replace(',', '.', $valor);
    }
}