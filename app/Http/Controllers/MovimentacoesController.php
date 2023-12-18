<?php

namespace App\Http\Controllers;

use App\Models\Produtos;
use App\Models\Movimentacoes;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class MovimentacoesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(){
        return view('movimentacoes.index',[
            "movimentacoes" => DB::select("SELECT NMProduto,NMCategoria,TPMovimentacao,VLMovimentacao,QTMovimentacao FROM movimentacoes INNER JOIN produtos ON(produtos.id = movimentacoes.IDProduto) INNER JOIN categorias ON(produtos.IDCategoria = categorias.id)")
        ]);
    }

       /**
     * Show the form for creating a new resource.
     */
    public function create(){
        return view('movimentacoes.create',[
            "estoque" => Produtos::all()
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function set(Request $request){
        //INICIO DAS VALIDAÇÕES
        $retorno['status'] = true;
        $retorno['mensagem'] = "Movimentação Realizada com Sucesso";
        $movimentacoes = new Movimentacoes();
        if($request->tipoMovimentacao == "VEN"){
            $qtEstq = Produtos::where('id',$request->idProduto)->select('NUEstoqueProduto')->first();
            if($qtEstq->NUEstoqueProduto < $request->quantidadeProduto  ){
                $retorno['mensagem'] = "A Quantidade a ser Vendida e maior que a que Está no Estoque!";
                $retorno['status'] = false;
            }else{
                DB::update("UPDATE produtos SET NUEstoqueProduto = produtos.NUEstoqueProduto - '$request->quantidadeProduto' WHERE id = '$request->idProduto' ");
                $movimentacoes->IDProduto = $request->idProduto;
                $movimentacoes->TPMovimentacao = $request->tipoMovimentacao;
                $movimentacoes->VLMovimentacao = $request->valorMovimentacao;
                $movimentacoes->QTMovimentacao = $request->quantidadeProduto;
                $movimentacoes->save();
            }
        }else{
            DB::update("UPDATE produtos SET NUEstoqueProduto = produtos.NUEstoqueProduto + '$request->quantidadeProduto' WHERE id = '$request->idProduto' ");
            $movimentacoes->IDProduto = $request->idProduto;
            $movimentacoes->TPMovimentacao = $request->tipoMovimentacao;
            $movimentacoes->VLMovimentacao = $request->valorMovimentacao;
            $movimentacoes->QTMovimentacao = $request->quantidadeProduto;
            $movimentacoes->save();
        }
        return json_encode($retorno);
    }
}
