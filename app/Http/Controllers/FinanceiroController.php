<?php

namespace App\Http\Controllers;

use App\Models\ContaPagar;
use Illuminate\Http\Request;

class FinanceiroController extends Controller
{
    /**
     * Retorna a lista de contas a pagar de uma filial.
     *
     * @param  int  $IDFilial
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function listarPagar($IDFilial)
    {
        return ContaPagar::where('IDFilial', $IDFilial)->get();
    }

    /**
     * Marca uma conta como paga (STConta = 1).
     *
     * @param  int  $ID  ID da conta
     * @return int  Número de registros afetados
     */
    public static function pagarConta($ID)
    {
        return ContaPagar::where('IDConta', $ID)
            ->update(['STConta' => 1]);
    }

    /**
     * Exclui uma conta a pagar.
     *
     * @param  int  $IDConta
     * @return bool|null
     */
    public function excluirContaPagar($IDConta)
    {
        return ContaPagar::destroy($IDConta);
    }

    /**
     * Retorna uma conta a pagar específica.
     *
     * @param  int  $IDConta
     * @return \App\Models\ContaPagar|null
     */
    public function mostrarContaPagar($IDConta)
    {
        return ContaPagar::find($IDConta);
    }

    /**
     * Salva ou atualiza uma conta a pagar.
     *
     * @param  array  $dados  Dados da conta (IDContaPagar, nomeContaPagar, vencimentoContaPagar, valorContaPagar, justificativaContaPagar)
     * @return \App\Models\ContaPagar
     */
    public function salvarContaPagar($dados)
    {
        if (!empty($dados['IDContaPagar'])) {
            // Atualização
            $conta = ContaPagar::find($dados['IDContaPagar']);
            if ($conta) {
                $conta->update([
                    'NMConta'              => $dados['nomeContaPagar'],
                    'DTVencimentoConta'    => $dados['vencimentoContaPagar'],
                    'VLConta'              => $dados['valorContaPagar'],
                    'DSJustificativaConta' => $dados['justificativaContaPagar'],
                ]);
            }
        } else {
            // Criação
            $conta = ContaPagar::create([
                'NMConta'              => $dados['nomeContaPagar'],
                'DTVencimentoConta'    => $dados['vencimentoContaPagar'],
                'VLConta'              => $dados['valorContaPagar'],
                'DSJustificativaConta' => $dados['justificativaContaPagar'],
                'STConta'              => 0,
                'IDFilial'             => $_SESSION['login']['filial'],
            ]);
        }

        return $conta;
    }
}