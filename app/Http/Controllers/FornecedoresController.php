<?php

namespace App\Http\Controllers;

use App\Models\Fornecedor;
use App\Models\Filial;
use App\Models\Produto;
use App\Models\Venda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FornecedoresController extends Controller
{
    // ======================
    // MÉTODOS RESOURCE (CRUD)
    // ======================

    /**
     * Display a listing of the resource.
     * Filtro opcional por filial via query string ?filial=ID
     */
    public function index(Request $request)
    {
        $filialSelecionada = $request->input('filial', $_SESSION['login']['filial'] ?? null);

        $fornecedores = Fornecedor::whereNull('STDelete')
            ->when($filialSelecionada, function ($query, $filialSelecionada) {
                return $query->where('IDFilial', $filialSelecionada);
            })
            ->orderBy('NMFornecedor')
            ->get();

        $filiais = Filial::orderBy('NMFilial')->get();

        return view('fornecedores.index', compact('fornecedores', 'filiais', 'filialSelecionada'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $filiais = Filial::orderBy('NMFilial')->get();
        return view('fornecedores.create', compact('filiais'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nomeFornecedor'    => 'required|string|min:1|max:100',
            'emailFornecedor'   => 'nullable|email|max:150',
            'telefoneFornecedor'=> 'nullable|string|max:20',
            'cepFornecedor'     => 'nullable|string|max:10',
            'ufFornecedor'      => 'nullable|string|max:2',
            'cidadeFornecedor'  => 'nullable|string|max:100',
            'bairroFornecedor'  => 'nullable|string|max:100',
            'ruaFornecedor'     => 'nullable|string|max:150',
            'numeroFornecedor'  => 'nullable|string|max:10',
            'complementoFornecedor' => 'nullable|string|max:100',
            'IDFilial'          => 'required|integer|exists:filiais,IDFilial',
        ]);

        $endFornecedor = json_encode([
            "cep"         => $request->cepFornecedor,
            "uf"          => $request->ufFornecedor,
            "cidade"      => $request->cidadeFornecedor,
            "bairro"      => $request->bairroFornecedor,
            "rua"         => $request->ruaFornecedor,
            "numero"      => $request->numeroFornecedor,
            "complemento" => $request->complementoFornecedor,
        ], JSON_UNESCAPED_UNICODE);

        Fornecedor::create([
            'NMFornecedor'        => $request->nomeFornecedor,
            'DSEmailFornecedor'   => $request->emailFornecedor,
            'DSTelefoneFornecedor'=> $request->telefoneFornecedor,
            'DSEndFornecedor'     => $endFornecedor,
            'IDFilial'            => $request->IDFilial,
        ]);

        return redirect()->route('fornecedores.index')->with('success', 'Fornecedor cadastrado com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $fornecedor = Fornecedor::findOrFail($id);
        $filiais = Filial::orderBy('NMFilial')->get();

        $endereco = json_decode($fornecedor->DSEndFornecedor, true) ?? [];

        return view('fornecedores.edit', compact('fornecedor', 'filiais', 'endereco'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nomeFornecedor'    => 'required|string|min:1|max:100',
            'emailFornecedor'   => 'nullable|email|max:150',
            'telefoneFornecedor'=> 'nullable|string|max:20',
            'cepFornecedor'     => 'nullable|string|max:10',
            'ufFornecedor'      => 'nullable|string|max:2',
            'cidadeFornecedor'  => 'nullable|string|max:100',
            'bairroFornecedor'  => 'nullable|string|max:100',
            'ruaFornecedor'     => 'nullable|string|max:150',
            'numeroFornecedor'  => 'nullable|string|max:10',
            'complementoFornecedor' => 'nullable|string|max:100',
            'IDFilial'          => 'required|integer|exists:filiais,IDFilial',
        ]);

        $endFornecedor = json_encode([
            "cep"         => $request->cepFornecedor,
            "uf"          => $request->ufFornecedor,
            "cidade"      => $request->cidadeFornecedor,
            "bairro"      => $request->bairroFornecedor,
            "rua"         => $request->ruaFornecedor,
            "numero"      => $request->numeroFornecedor,
            "complemento" => $request->complementoFornecedor,
        ], JSON_UNESCAPED_UNICODE);

        $fornecedor = Fornecedor::findOrFail($id);
        $fornecedor->update([
            'NMFornecedor'        => $request->nomeFornecedor,
            'DSEmailFornecedor'   => $request->emailFornecedor,
            'DSTelefoneFornecedor'=> $request->telefoneFornecedor,
            'DSEndFornecedor'     => $endFornecedor,
            'IDFilial'            => $request->IDFilial,
        ]);

        return redirect()->route('fornecedores.index')->with('success', 'Fornecedor atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $temProdutosEmEstoque = Produto::where('IDFornecedor', $id)
            ->where('NUEstoqueProduto', '>', 0)
            ->exists();

        if ($temProdutosEmEstoque) {
            return redirect()->back()->with('error', 'Você não pode excluir esse fornecedor, pois nele há produtos em estoque.');
        }

        $temVendas = Venda::where('IDFornecedor', $id)->exists();

        if ($temVendas) {
            Fornecedor::where('IDFornecedor', $id)->update(['STDelete' => 1]);
        } else {
            Fornecedor::destroy($id);
        }

        return redirect()->route('fornecedores.index')->with('success', 'Fornecedor excluído com sucesso!');
    }

    // ================================
    // MÉTODOS ESTÁTICOS (COMPATIBILIDADE)
    // ================================

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
                Fornecedor::where('IDFornecedor', $ID)
                    ->update(['STDelete' => 1]);
            } else {
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