<?php

namespace App\Http\Controllers;

use App\Models\Cupom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CuponsController extends Controller
{
    /**
     * Sincroniza/Insere um cupom (usado por sistemas externos).
     *
     * @param  array  $dados  Dados do cupom (IDCaixa, ANCupom, CDVenda, IDCliente, IDFilial)
     * @return \App\Models\Cupom
     */
    public static function sincronizaCupons($dados)
    {
        return Cupom::create([
            'IDCaixa'   => $dados['IDCaixa'],
            'ANCupom'   => $dados['ANCupom'],
            'CDVenda'   => $dados['CDVenda'],
            'IDCliente' => $dados['IDCliente'],
            'IDFilial'  => $dados['IDFilial'],
        ]);
    }

    /**
     * Insere um cupom, determinando a filial pela sessão ou pelo parâmetro.
     *
     * @param  array  $dados  Dados do cupom (IDCaixa, CDVenda, ANCupom, IDCliente, IDFilial)
     * @return \App\Models\Cupom
     */
    public static function setCupom($dados)
    {
        $filial = $_SESSION['login']['filial'] ?? $dados['IDFilial'];

        return Cupom::create([
            'IDCaixa'   => $dados['IDCaixa'],
            'CDVenda'   => $dados['CDVenda'],
            'ANCupom'   => $dados['ANCupom'],
            'IDCliente' => $dados['IDCliente'],
            'IDFilial'  => $filial,
        ]);
    }

    /**
     * Retorna os dados do cabeçalho do cupom (filial + empresa).
     * Query com JOIN entre filiais e empresas.
     *
     * @param  int   $IDFilial
     * @return array
     */
    public static function getHeaderCupom($IDFilial)
    {
        return DB::select(
            "SELECT DSEnderecoJSON, NMFilial, NUCnpjEmpresa, NMRazaoEmpresa 
             FROM filiais 
             INNER JOIN empresas USING(IDEmpresa) 
             WHERE IDFilial = ?",
            [$IDFilial]
        );
    }
}