<?php

namespace App\Http\Controllers;

use App\Models\categorias;
use App\Models\produtos;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CategoriasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('categorias.index', [
            'categorias' => DB::select("SELECT categorias.IDCategoria, DSCategoria, CASE WHEN NUValorProduto IS NULL THEN 0 ELSE SUM(NUValorProduto) END as VLInvestido, SUM(produtos.NUEstoqueProduto) as QTEstoque FROM categorias LEFT JOIN produtos ON(categorias.IDCategoria = produtos.IDCategoria) GROUP BY categorias.IDCategoria")
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('categorias.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nomeCategoria' => 'required|string|min:1|max:50',
        ]);

        if (categorias::where('DSCategoria', $request->nomeCategoria)->exists()) {
            return redirect()->back()->with('error', 'Já Existe uma Categoria com Esse Nome!')->withInput();
        }

        $categoria = new categorias();
        $categoria->DSCategoria = $request->nomeCategoria;
        $categoria->save();

        return redirect()->route('categorias.index')->with('success', 'Categoria cadastrada com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('categorias.edit', [
            'categoria' => categorias::findOrFail($id)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nomeCategoria' => 'required|string|min:1|max:50',
        ]);

        $existe = categorias::where('DSCategoria', $request->nomeCategoria)
            ->where('IDCategoria', '!=', $id)
            ->exists();

        if ($existe) {
            return redirect()->back()->with('error', 'Já Existe uma Categoria com Esse Nome!')->withInput();
        }

        $categoria = categorias::findOrFail($id);
        $categoria->DSCategoria = $request->nomeCategoria;
        $categoria->save();

        return redirect()->route('categorias.index')->with('success', 'Categoria atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $temProdutos = Produto::where('IDCategoria', $id)->exists();

        if ($temProdutos) {
            return redirect()->back()->with('error', 'Exclusão não Permitida pois Existem Produtos com Essa Categoria!');
        }

        categorias::destroy($id);

        return redirect()->route('categorias.index')->with('success', 'Categoria excluída com sucesso!');
    }
}