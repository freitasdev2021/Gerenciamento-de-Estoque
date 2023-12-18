<?php

namespace App\Http\Controllers;

use App\Models\Produtos;
use App\Models\Categorias;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ProdutosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(){
        return view('produtos.index',[
            "produtos" => DB::select('SELECT *,produtos.id as IDProduto FROM produtos INNER JOIN categorias ON(produtos.IDCategoria = categorias.id)')
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(){
        return view('produtos.create',[
            "categorias" => Categorias::all()
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function set(Request $request){
        //INICIO DAS VALIDAÇÕES
        $retorno['status'] = true;
        $retorno['mensagem'] = "Salvamento Realizado com Sucesso";
        $produtos = new Produtos();
        if(Produtos::where('SKUProduto',$request->skuProduto)->exists()){
            $retorno['mensagem'] = "Já Existe um Produto com Esse SKU!";
            $retorno['status'] = false;
        }else{
            $produtos->NMProduto = $request->nomeProduto;
            $produtos->SKUProduto = $request->skuProduto;
            $produtos->DSProduto = $request->descricaoProduto;
            $produtos->VLProduto = $request->valorProduto;
            $produtos->IDCategoria = $request->categoriaProduto;
            $produtos->NUEstoqueProduto = $request->estoqueProduto;
            $produtos->DTVencimento = $request->vencimentoProduto;
            $produtos->IMGProduto = $request->imagemProduto;
            $produtos->save();
        }
        return json_encode($retorno);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id){
        return view('produtos.edit',[
            "produto" => Produtos::findOrFail($id),
            "categorias" => Categorias::all()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request){
        //INICIO DAS VALIDAÇÕES
        $retorno['status'] = true;
        $retorno['mensagem'] = "Salvamento Realizado com Sucesso";
        if(DB::select("SELECT id FROM produtos WHERE SKUProduto = '$request->skuProduto' AND id != '$request->idProduto'")){
            $retorno['mensagem'] = "Já Existe um Produto com Esse SKU!";
            $retorno['status'] = false;
        }else{
            DB::update("UPDATE produtos SET NMProduto = '$request->nomeProduto', SKUProduto = '$request->skuProduto', DSProduto = '$request->descricaoProduto',DTEdicao = NOW(), VLProduto = '$request->valorProduto', IMGProduto = '$request->imagemProduto' WHERE id = '$request->idProduto' ");
        }
        return json_encode($retorno);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request){
        DB::delete("DELETE FROM produtos WHERE id = '$request->IDProduto'");
    }
}
