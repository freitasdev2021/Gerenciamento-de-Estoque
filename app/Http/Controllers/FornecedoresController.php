<?php

namespace App\Http\Controllers;

use App\Models\Fornecedor;
use App\Models\Produto;
use App\Models\Venda;
use Illuminate\Http\Request;

class FornecedoresController extends Controller
{
    /**
     * Retorna a lista de fornecedores ativos de uma filial.
     *
     * @param  int  $IDFilial
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function listarFornecedores($IDFilial)
    {
        return Fornecedor::where('IDFilial', $IDFilial)
            ->whereNull('STDelete')
            ->get();
    }

    /**
     * Exclui um fornecedor.
     * Se houver produtos em estoque, bloqueia a exclusão.
     * Se houver vendas vinculadas, faz soft delete (STDelete = 1).
     * Caso contrário, faz hard delete.
     *
     * @param  int  $ID
     * @param  int  $confirmacao  0 = verificar, 1 = confirmado
     * @return string  JSON encoded
     */
    public static function excluirFornecedor($ID, $confirmacao)
    {
        $temProdutosEmEstoque = Produto::where('IDFornecedor', $ID)
            ->where('NUEstoqueProduto', '>', 0)
            ->exists();

        if ($temProdutosEmEstoque) {
            $retorno['erro']     = true;
            $retorno['mensagem'] = "Você não pode excluir esse fornecedor, pois nele há produtos em estoque.";
            return json_encode($retorno);
        }

        $retorno['mensagem'] = "Deseja excluir esse fornecedor?";
        $retorno['erro']     = false;

        if ($confirmacao == 1) {
            $temVendas = Venda::where('IDFornecedor', $ID)->exists();

            if ($temVendas) {
                // Soft delete: apenas marca como deletado
                Fornecedor::where('IDFornecedor', $ID)
                    ->update(['STDelete' => 1]);
            } else {
                // Hard delete
                Fornecedor::destroy($ID);
            }
        }

        return json_encode($retorno);
    }

    /**
     * Retorna um fornecedor específico pelo ID.
     *
     * @param  int  $IDFornecedor
     * @return \App\Models\Fornecedor|null
     */
    public function listarFornecedor($IDFornecedor)
    {
        return Fornecedor::find($IDFornecedor);
    }

    /**
     * Salva ou atualiza um fornecedor.
     *
     * @param  array  $dados  Dados do fornecedor
     * @return \App\Models\Fornecedor
     */
    public function salvarFornecedor($dados)
    {
        $endFornecedor = json_encode([
            "cep"         => $dados['cepFornecedor'],
            "uf"          => $dados['ufFornecedor'],
            "cidade"      => $dados['cidadeFornecedor'],
            "bairro"      => $dados['bairroFornecedor'],
            "rua"         => $dados['ruaFornecedor'],
            "numero"      => $dados['numeroFornecedor'],
            "complemento" => $dados['complementoFornecedor'],
        ], JSON_UNESCAPED_UNICODE);

        if (!empty($dados['IDFornecedor'])) {
            // Atualização
            $fornecedor = Fornecedor::find($dados['IDFornecedor']);
            if ($fornecedor) {
                $fornecedor->update([
                    'NMFornecedor'       => $dados['nomeFornecedor'],
                    'DSEmailFornecedor'  => $dados['emailFornecedor'],
                    'DSTelefoneFornecedor' => $dados['telefoneFornecedor'],
                    'DSEndFornecedor'    => $endFornecedor,
                ]);
            }
        } else {
            // Criação
            $fornecedor = Fornecedor::create([
                'NMFornecedor'        => $dados['nomeFornecedor'],
                'DSEmailFornecedor'   => $dados['emailFornecedor'],
                'DSTelefoneFornecedor' => $dados['telefoneFornecedor'],
                'DSEndFornecedor'     => $endFornecedor,
                'IDFilial'            => $_SESSION['login']['filial'],
            ]);
        }

        return $fornecedor;
    }
}