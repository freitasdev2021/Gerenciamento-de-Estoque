<?php

namespace App\Http\Controllers;

use App\Models\Pagamento;
use App\Models\Venda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PagamentosController extends Controller
{
    /**
     * Retorna dados de parcelas de um pagamento com JOINs em vendas e cupons.
     *
     * @param  int        $ID
     * @return object|null
     */
    public static function getDadosParcelas($ID)
    {
        $result = DB::select(
            "SELECT NUJuros, QTParcelas, DSMetodo 
             FROM pagamentos 
             INNER JOIN vendas USING(IDPagamento) 
             INNER JOIN cupons USING(CDVenda) 
             WHERE pagamentos.IDPagamento = ?",
            [$ID]
        );

        return !empty($result) ? $result[0] : null;
    }

    /**
     * Retorna dados de parcelas de um pagamento (sem JOINs adicionais).
     *
     * @param  int                 $ID
     * @return \App\Models\Pagamento|null
     */
    public static function getDadosParcelasPag($ID)
    {
        return Pagamento::select('NUJuros', 'QTParcelas', 'DSMetodo')
            ->find($ID);
    }

    /**
     * Calcula o valor das parcelas com juros.
     *
     * @param  float  $valor
     * @param  int    $parcelas
     * @param  float  $juros
     * @return array
     */
    public static function jurosParcelas($valor, $parcelas, $juros)
    {
        $valorParcela = ($valor / $parcelas);
        $valorParcelaComJuros = $valorParcela + ($juros / 100) * $valorParcela;

        return [
            "parcelas"     => $parcelas,
            "valorParcela" => $valorParcelaComJuros,
            "valorFinal"   => $valorParcelaComJuros * $parcelas,
        ];
    }

    /**
     * Calcula o valor com taxa da maquininha.
     *
     * @param  float  $valor
     * @param  float  $taxa
     * @return float
     */
    public static function taxaMaquininha($valor, $taxa)
    {
        return $valor + ($taxa / 100) * $valor;
    }

    /**
     * Retorna a lista de pagamentos ativos de uma filial.
     *
     * @param  int  $IDFilial
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function listarPagamentos($IDFilial)
    {
        return Pagamento::where('IDFilial', $IDFilial)
            ->whereNull('STDelete')
            ->get();
    }

    /**
     * Verifica se existe alguma venda vinculada a um pagamento.
     *
     * @param  int  $ID
     * @return \App\Models\Venda|null
     */
    public static function confVendaPagamento($ID)
    {
        return Venda::where('IDPagamento', $ID)->first();
    }

    /**
     * Exclui um pagamento.
     * Se houver vendas vinculadas, faz soft delete (STDelete = 1).
     * Caso contrário, faz hard delete.
     *
     * @param  int  $IDPagamento
     * @return bool|int
     */
    public function excluirPagamento($IDPagamento)
    {
        if (self::confVendaPagamento($IDPagamento)) {
            // Soft delete: apenas marca como deletado
            return Pagamento::where('IDPagamento', $IDPagamento)
                ->update(['STDelete' => 1]);
        }

        // Hard delete
        return Pagamento::destroy($IDPagamento);
    }

    /**
     * Retorna um pagamento específico pelo ID.
     *
     * @param  int  $IDPagamento
     * @return \App\Models\Pagamento|null
     */
    public static function listarPagamento($IDPagamento)
    {
        return Pagamento::find($IDPagamento);
    }

    /**
     * Salva ou atualiza um pagamento.
     *
     * @param  array  $dados  Dados do pagamento
     * @return \App\Models\Pagamento
     */
    public function salvarPagamento($dados)
    {
        // Calcula o valor do desconto baseado no tipo
        if ($dados['tipoMetodo'] == '1') {
            $descontoMetodo = intval($dados['descontoMetodo']);
        } else {
            // Converte valor monetário (ex: "1.234,56" → "1234.56")
            $descontoMetodo = $this->decimal($dados['descontoMetodo']);
        }

        if (!empty($dados['IDPagamento'])) {
            // Atualização
            $pagamento = Pagamento::find($dados['IDPagamento']);
            if ($pagamento) {
                $pagamento->update([
                    'NMPagamento' => $dados['nomeMetodo'],
                    'QTDesconto'  => $descontoMetodo,
                    'DSMetodo'    => $dados['metodoMetodo'],
                    'QTParcelas'  => $dados['parcelasMetodo'],
                    'TPDesconto'  => $dados['tipoMetodo'],
                    'NUJuros'     => $dados['jurosMetodo'],
                ]);
            }
        } else {
            // Criação
            $pagamento = Pagamento::create([
                'NMPagamento' => $dados['nomeMetodo'],
                'QTDesconto'  => $descontoMetodo,
                'DSMetodo'    => $dados['metodoMetodo'],
                'QTParcelas'  => $dados['parcelasMetodo'],
                'TPDesconto'  => $dados['tipoMetodo'],
                'IDFilial'    => $_SESSION['login']['filial'],
                'NUJuros'     => $dados['jurosMetodo'],
            ]);
        }

        return $pagamento;
    }
}