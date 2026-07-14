<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\Categoria;
use App\Models\Fornecedor;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProdutosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $produtos = Produto::with('fornecedor', 'categoria')
            ->get();

        return view('produtos.index', compact('produtos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('produtos.create', [
            'categorias'   => Categoria::all(),
            'fornecedores' => Fornecedor::whereNull('STDelete')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->merge([
            'NUValorProduto' => $this->limparValorMonetario($request->NUValorProduto),
        ]);

        $dados = $request->validate([
            'IDFornecedor'     => 'required|integer',
            'IDCategoria'      => 'required|integer|exists:categorias,IDCategoria',
            'NMProduto'        => 'required|string|min:5|max:250',
            'NUEstoqueMinimo'  => 'nullable|integer|min:0',
            'DSUnidadeProduto' => 'required|string|max:2',
            'DTValidadeProduto'=> 'nullable|date|after_or_equal:today',
            'NUValorProduto'   => 'required|numeric|min:0',
            'DSCodigoProduto'  => 'required|string|min:5|max:45|unique:produtos,DSCodigoProduto',
        ]);

        if (Produto::where('DSCodigoProduto', $request->DSCodigoProduto)->exists()) {
            return redirect()->back()->with('error', 'Já Existe um Produto com Esse Código/SKU!')->withInput();
        }

        Produto::create($dados);

        return redirect()->route('produtos.index')->with('success', 'Produto cadastrado com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('produtos.create', [
            'produto'      => Produto::findOrFail($id),
            'categorias'   => Categoria::all(),
            'fornecedores' => Fornecedor::whereNull('STDelete')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $produto = Produto::findOrFail($id);

        $request->merge([
            'NUValorProduto' => $this->limparValorMonetario($request->NUValorProduto),
        ]);

        $dados = $request->validate([
            'IDFornecedor'     => 'required|integer',
            'IDCategoria'      => 'required|integer|exists:categorias,IDCategoria',
            'NMProduto'        => 'required|string|min:5|max:250',
            'NUEstoqueMinimo'  => 'nullable|integer|min:0',
            'DSUnidadeProduto' => 'required|string|max:2',
            'DTValidadeProduto'=> 'nullable|date|after_or_equal:today',
            'NUValorProduto'   => 'required|numeric|min:0',
            'DSCodigoProduto'  => 'required|string|min:5|max:45|unique:produtos,DSCodigoProduto,' . $id . ',IDProduto',
        ]);

        $existe = Produto::where('DSCodigoProduto', $request->DSCodigoProduto)
            ->where('IDProduto', '!=', $id)
            ->exists();

        if ($existe) {
            return redirect()->back()->with('error', 'Já Existe um Produto com Esse Código/SKU!')->withInput();
        }

        $produto->update($dados);

        return redirect()->route('produtos.index')->with('success', 'Produto atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Produto::destroy($id);
        return redirect()->route('produtos.index')->with('success', 'Produto excluído com sucesso!');
    }

    /**
     * Limpa valor monetário: "1.234,56" -> "1234.56"
     */
    private function limparValorMonetario($valor)
    {
        if (empty($valor)) {
            return '0';
        }
        // Remove pontos de milhar e troca vírgula por ponto
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);
        return $valor;
    }
}