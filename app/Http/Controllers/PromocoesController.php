<?php

namespace App\Http\Controllers;

use App\Models\Promocao;
use App\Models\Promocional;
use App\Models\Venda;
use App\Models\Produto;
use App\Models\Fornecedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PromocoesController extends Controller
{
    /**
     * Retorna a lista de promoções ativas de uma filial.
     *
     * @param  int  $IDFilial
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function listarPromocoes($IDFilial)
    {
        return Promocao::where('IDFilial', $IDFilial)
            ->whereNull('STDelete')
            ->get();
    }

    /**
     * Retorna uma promoção específica pelo ID.
     *
     * @param  int  $IDPromocao
     * @return \App\Models\Promocao|null
     */
    public function listarPromocao($IDPromocao)
    {
        return Promocao::find($IDPromocao);
    }

    /**
     * Verifica se existe alguma venda vinculada a uma promoção.
     *
     * @param  int  $ID
     * @return \App\Models\Venda|null
     */
    public static function confVendaPromo($ID)
    {
        return Venda::where('IDPromocao', $ID)->first();
    }

    /**
     * Exclui uma promoção.
     * Se houver vendas vinculadas, faz soft delete (STDelete = 1).
     * Caso contrário, faz hard delete e remove os vínculos promocionais.
     *
     * @param  int  $IDPromocao
     * @return bool
     */
    public function excluirPromocao($IDPromocao)
    {
        $temVenda = self::confVendaPromo($IDPromocao);

        if ($temVenda) {
            // Soft delete: apenas marca como deletada
            return Promocao::where('IDPromocao', $IDPromocao)
                ->update(['STDelete' => 1]);
        }

        // Hard delete: remove a promoção e seus vínculos promocionais
        self::delPromocional($IDPromocao);
        return Promocao::destroy($IDPromocao);
    }

    /**
     * Salva ou atualiza uma promoção.
     *
     * @param  array  $dados  Dados da promoção (IDPromocao, nomePromo, inicioPromo, fimPromo, descontoPromo, tipoPromo)
     * @return \App\Models\Promocao
     */
    public function salvarPromocao($dados)
    {
        // Calcula o valor do desconto baseado no tipo
        if ($dados['tipoPromo'] == '%') {
            $NUDescontoPromo = intval($dados['descontoPromo']);
        } else {
            // Converte valor monetário (ex: "1.234,56" → "1234.56")
            $NUDescontoPromo = $this->decimal($dados['descontoPromo']);
        }

        if (!empty($dados['IDPromocao'])) {
            // Atualização
            $promocao = Promocao::find($dados['IDPromocao']);
            if ($promocao) {
                $promocao->update([
                    'NMPromo'         => $dados['nomePromo'],
                    'DTInicioPromo'   => $dados['inicioPromo'],
                    'DTTerminoPromo'  => $dados['fimPromo'],
                    'NUDescontoPromo' => $NUDescontoPromo,
                    'TPDesconto'      => $dados['tipoPromo'],
                ]);
            }
        } else {
            // Criação
            $promocao = Promocao::create([
                'NMPromo'         => $dados['nomePromo'],
                'DTInicioPromo'   => $dados['inicioPromo'],
                'DTTerminoPromo'  => $dados['fimPromo'],
                'NUDescontoPromo' => $NUDescontoPromo,
                'TPDesconto'      => $dados['tipoPromo'],
                'IDFilial'        => $_SESSION['login']['filial'],
            ]);
        }

        return $promocao;
    }

    /**
     * Retorna lista de produtos com indicador de vínculo a uma promoção.
     * Query complexa com subquery CASE WHEN e NOT IN - mantida em SQL raw.
     *
     * @param  int  $IDPromocao
     * @return array
     */
    public static function getPromocional($IDPromocao)
    {
        $filialId = $_SESSION['login']['filial'];

        $SQL = "
            SELECT 
                NMProduto,
                IDProduto,
                CASE 
                    WHEN (SELECT COUNT(IDProduto) FROM promocionais 
                          WHERE IDPromocao = ? AND IDProduto = produtos.IDProduto) > 0 
                    THEN 1 
                    ELSE 0 
                END as vinculo 
            FROM produtos 
            LEFT JOIN fornecedores USING(IDFornecedor) 
            WHERE IDProduto NOT IN (
                SELECT IDProduto FROM promocionais WHERE IDPromocao != ?
            ) 
            AND fornecedores.IDFilial = ?
        ";

        return DB::select($SQL, [$IDPromocao, $IDPromocao, $filialId]);
    }

    /**
     * Vincula um produto a uma promoção (cria registro em promocionais).
     *
     * @param  array  $dados  Dados do vínculo (IDPromocao, IDProduto)
     * @return \App\Models\Promocional
     */
    public static function setPromocional($dados)
    {
        return Promocional::create([
            'IDPromocao' => $dados['IDPromocao'],
            'IDProduto'  => $dados['IDProduto'],
        ]);
    }

    /**
     * Remove todos os vínculos promocionais de uma promoção.
     *
     * @param  int  $ID  ID da promoção
     * @return int  Número de registros deletados
     */
    public static function delPromocional($ID)
    {
        return Promocional::where('IDPromocao', $ID)->delete();
    }

    /**
     * Confere se um produto está em alguma promoção ativa e calcula o valor com desconto.
     * Query complexa com JOINs e condicional de data - mantida em SQL raw.
     *
     * @param  int    $IDProduto
     * @param  float  $valorProduto
     * @param  int    $IDFilial
     * @return float  Valor com desconto aplicado (ou valor original se não houver promoção)
     */
    public static function confProdutoPromocional($IDProduto, $valorProduto, $IDFilial)
    {
        $SQL = "
            SELECT
                promocoes.NUDescontoPromo,
                promocoes.TPDesconto,
                promocoes.NMPromo
            FROM 
                promocoes
            INNER JOIN
                promocionais
            USING(IDPromocao)
            WHERE
                NOW() >= promocoes.DTInicioPromo 
                AND NOW() <= promocoes.DTTerminoPromo
                AND promocoes.IDFilial = ?
                AND promocionais.IDProduto = ?
            GROUP BY promocoes.IDPromocao
        ";

        $result = DB::select($SQL, [$IDFilial, $IDProduto]);

        if (count($result) > 0) {
            $desc = $result[0];
            if ($desc->TPDesconto == '%') {
                $desconto = $valorProduto - ($desc->NUDescontoPromo * $valorProduto) / 100;
            } else {
                $desconto = $valorProduto - $desc->NUDescontoPromo;
            }
        } else {
            $desconto = $valorProduto;
        }

        return $desconto;
    }
}