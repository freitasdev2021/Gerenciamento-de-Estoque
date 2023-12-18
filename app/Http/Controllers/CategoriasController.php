<?php

namespace App\Http\Controllers;

use App\Models\Categorias;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
class CategoriasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(){
        return view('categorias.index',[
            "categorias" => DB::select('SELECT categorias.id,NMCategoria, CASE WHEN VLProduto IS NULL THEN 0 ELSE SUM(VLProduto) END as VLInvestido,SUM(produtos.NUEstoqueProduto) as QTEstoque FROM categorias LEFT JOIN produtos ON(categorias.ID = produtos.IDCategoria) GROUP BY categorias.id ')
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(){
        return view('categorias.create');
    }

    /**
     * Display the specified resource.
     */
    public function set(Request $request){
        //INICIO DAS VALIDAÇÕES
        $retorno['status'] = true;
        $retorno['mensagem'] = "Salvamento Realizado com Sucesso";
        $categoria = new Categorias();
        if(Categorias::where('NMCategoria',$request->nomeCategoria)->exists()){
            $retorno['mensagem'] = "Já Existe uma Categoria com Esse Nome!";
            $retorno['status'] = false;
        }else{
            $categoria->NMCategoria = $request->nomeCategoria;
            $categoria->save();
        }
        return json_encode($retorno);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id){
        return view('categorias.edit',[
            "categoria" => Categorias::findOrFail($id)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request){
        //INICIO DAS VALIDAÇÕES
        $retorno['status'] = true;
        $retorno['mensagem'] = "Salvamento Realizado com Sucesso";
        if(DB::select("SELECT id FROM categorias WHERE NMCategoria = '$request->nomeCategoria' AND id != '$request->idCategoria'")){
            $retorno['mensagem'] = "Já Existe uma Categoria com Esse Nome!";
            $retorno['status'] = false;
        }else{
            DB::update("UPDATE categorias SET NMCategoria = '$request->nomeCategoria' WHERE id = '$request->idCategoria' ");
        }
        return json_encode($retorno);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request){
        $retorno['status'] = true;
        $retorno['mensagem'] = "Categoria Excluida com Sucesso";
        if(DB::select("SELECT id FROM produtos WHERE IDCategoria = '$request->IDCategoria' ")){
            $retorno['mensagem'] = "Exclusão não Permitida pois Existem Produtos com Essa Categoria!";
            $retorno['status'] = false;
        }else{
            if($request->confirmar == 0){
                $retorno['status'] = true;
                $retorno['mensagem'] = "Deseja Excluir Essa Categoria?";
            }else{
                $retorno['status'] = true;
                DB::delete("DELETE FROM categorias WHERE id = '$request->IDCategoria'");
            }
        }
        return json_encode($retorno);
    }
}
