<?php

namespace App\Http\Controllers;

use App\Models\Comissao;
use App\Models\Comissionado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ComissoesController extends Controller
{
    /**
     * Retorna a lista de comissões de uma filial.
     *
     * @param  int  $IDFilial
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getComissoes($IDFilial)
    {
        return Comissao::where('IDFilial', $IDFilial)->get();
    }

    /**
     * Exclui uma comissão e todos os vínculos de comissionados associados.
     *
     * @param  int  $IDComissao
     * @return bool|null
     */
    public static function delComissao($IDComissao)
    {
        self::delComissionado($IDComissao);
        return Comissao::destroy($IDComissao);
    }

    /**
     * Salva ou atualiza uma comissão.
     *
     * @param  array  $dados  Dados da comissão (IDComissao, nomeComissao, tipoComissao, porcentagemComissao)
     * @return \App\Models\Comissao
     */
    public static function setComissao($dados)
    {
        $filial = $_SESSION['login']['filial'];

        if (!empty($dados['IDComissao'])) {
            // Atualização
            $comissao = Comissao::find($dados['IDComissao']);
            if ($comissao) {
                $comissao->update([
                    'NMComissao'    => $dados['nomeComissao'],
                    'NUPorcentagem' => $dados['porcentagemComissao'],
                ]);
            }
        } else {
            // Criação
            $comissao = Comissao::create([
                'NMComissao'    => $dados['nomeComissao'],
                'TPComissao'    => $dados['tipoComissao'],
                'NUPorcentagem' => $dados['porcentagemComissao'],
                'IDFilial'      => $filial,
            ]);
        }

        return $comissao;
    }

    /**
     * Retorna uma comissão específica pelo ID.
     *
     * @param  int  $IDComissao
     * @return \App\Models\Comissao|null
     */
    public static function getComissao($IDComissao)
    {
        return Comissao::find($IDComissao);
    }

    /**
     * Retorna lista de colaboradores com indicador de vínculo a uma comissão.
     * Query complexa com subquery CASE WHEN e NOT IN - mantida em SQL raw.
     *
     * @param  int   $IDComissao
     * @return array
     */
    public static function getComissionados($IDComissao)
    {
        $SQL = "
            SELECT 
                NMColaborador,
                IDColaborador,
                CASE 
                    WHEN (SELECT COUNT(IDColaborador) FROM comissionados 
                          WHERE IDComissao = ? AND IDColaborador = colaboradores.IDColaborador) > 0 
                    THEN 1 
                    ELSE 0 
                END as vinculo 
            FROM colaboradores 
            WHERE IDColaborador NOT IN (
                SELECT IDColaborador FROM comissionados WHERE IDComissao != ?
            )
        ";

        return DB::select($SQL, [$IDComissao, $IDComissao]);
    }

    /**
     * Vincula um colaborador a uma comissão (cria registro em comissionados).
     *
     * @param  array  $dados  Dados do vínculo (IDComissao, IDColaborador)
     * @return \App\Models\Comissionado
     */
    public static function setComissionado($dados)
    {
        return Comissionado::create([
            'IDComissao'    => $dados['IDComissao'],
            'IDColaborador' => $dados['IDColaborador'],
        ]);
    }

    /**
     * Remove todos os vínculos de comissionados de uma comissão.
     *
     * @param  int  $ID  ID da comissão
     * @return int  Número de registros deletados
     */
    public static function delComissionado($ID)
    {
        return Comissionado::where('IDComissao', $ID)->delete();
    }
}