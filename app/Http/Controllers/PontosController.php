<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\Venda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PontosController extends Controller
{
    /**
     * Retorna as vendas de um caixa (PDV) com nome do cliente.
     *
     * @param  int   $IDCaixa
     * @return array
     */
    public static function getVendas($IDCaixa)
    {
        return DB::select(
            "SELECT
                CASE WHEN cupons.IDCliente = 0 THEN 'Cliente não identificado' ELSE clientes.NMCliente END AS cliente,
                cupons.ANCupom
             FROM cupons 
             LEFT JOIN clientes USING(IDCliente) 
             WHERE IDCaixa = ?",
            [$IDCaixa]
        );
    }

    /**
     * Retorna lista de pontos de venda (caixas) de uma filial com o total de vendas.
     * Query complexa com subqueries correlacionadas e CASE WHEN aninhado.
     *
     * @param  int   $IDFilial
     * @return array
     */
    public static function listarPontos($IDFilial)
    {
        return DB::select(
            "SELECT 
                c.*,
                CASE WHEN c.IDCaixa = ven.IDCaixa THEN
                    CASE WHEN STCaixa = 0 THEN
                        (SELECT SUM(VLVenda)
                         FROM caixa
                         LEFT JOIN vendas v USING(IDCaixa)
                         WHERE caixa.IDFilial = ? 
                         AND DTVenda > DTAberturaCaixa AND DTVenda < DTFechamentoCaixa)
                    ELSE
                        (SELECT SUM(VLVenda)
                         FROM caixa
                         LEFT JOIN vendas v USING(IDCaixa)
                         WHERE caixa.IDFilial = ? 
                         AND DTVenda > DTAberturaCaixa AND DTVenda < NOW())
                    END
                ELSE
                    0
                END as vendas
             FROM caixa c 
             LEFT JOIN vendas ven USING(IDCaixa)
             WHERE c.IDFilial = ? AND STDelete IS NULL 
             GROUP BY c.IDCaixa",
            [$IDFilial, $IDFilial, $IDFilial]
        );
    }

    /**
     * Retorna um caixa (PDV) específico pelo ID.
     *
     * @param  int  $IDCaixa
     * @return \App\Models\Caixa|null
     */
    public function listarPonto($IDCaixa)
    {
        return Caixa::find($IDCaixa);
    }

    /**
     * Verifica se existe alguma venda vinculada a um caixa.
     *
     * @param  int  $ID
     * @return \App\Models\Venda|null
     */
    public static function confereVendaPonto($ID)
    {
        return Venda::where('IDCaixa', $ID)->first();
    }

    /**
     * Exclui um caixa (PDV).
     * Se houver vendas vinculadas, faz soft delete (STDelete = 1).
     * Caso contrário, faz hard delete.
     *
     * @param  int  $IDCaixa
     * @return bool|int
     */
    public function excluirPonto($IDCaixa)
    {
        if (self::confereVendaPonto($IDCaixa)) {
            // Soft delete: apenas marca como deletado
            return Caixa::where('IDCaixa', $IDCaixa)
                ->update(['STDelete' => 1]);
        }

        // Hard delete
        return Caixa::destroy($IDCaixa);
    }

    /**
     * Salva ou atualiza um caixa (PDV).
     *
     * @param  array  $dados  Dados do caixa (IDCaixa, nomePdv, senhaPdv)
     * @return \App\Models\Caixa
     */
    public function salvarPonto($dados)
    {
        if (!empty($dados['IDCaixa'])) {
            // Atualização
            $caixa = Caixa::find($dados['IDCaixa']);
            if ($caixa) {
                $caixa->update([
                    'NMPdv'      => $dados['nomePdv'],
                    'NMSenhaPDV' => $dados['senhaPdv'],
                ]);
            }
        } else {
            // Criação
            $caixa = Caixa::create([
                'IDFilial'   => $_SESSION['login']['filial'],
                'NMPdv'      => $dados['nomePdv'],
                'NMSenhaPDV' => $dados['senhaPdv'],
            ]);
        }

        return $caixa;
    }
}