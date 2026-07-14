<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Crediario;
use App\Models\Cupom;
use App\Models\Devedor;
use App\Models\Filial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientesController extends Controller
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

        $clientes = Cliente::whereNull('STDelete')
            ->when($filialSelecionada, function ($query, $filialSelecionada) {
                return $query->where('IDFilial', $filialSelecionada);
            })
            ->with('devedor')
            ->orderBy('NMCliente')
            ->get()
            ->map(function ($cliente) {
                $cliente->divida = $cliente->devedor ? $cliente->devedor->VLDivida : 0;
                return $cliente;
            });

        $filiais = Filial::orderBy('NMFilial')->get();

        return view('clientes.index', compact('clientes', 'filiais', 'filialSelecionada'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $cliente = null;
        return view('clientes.create', compact('cliente'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nomeCliente'     => 'required|string|min:1|max:100',
            'emailCliente'    => 'nullable|email|max:150',
            'telefoneCliente' => 'nullable|string|max:20',
            'cpfCliente'      => 'nullable|string|max:20',
            'IDFilial'        => 'nullable|integer|exists:filiais,IDFilial',
        ]);

        $filialId = $request->IDFilial ?: ($_SESSION['login']['filial'] ?? null);

        Cliente::create([
            'NMCliente'         => $request->nomeCliente,
            'NMEmailCliente'    => $request->emailCliente,
            'NUTelefoneCliente' => $request->telefoneCliente,
            'NUCpfCliente'      => $request->cpfCliente,
            'IDFilial'          => $filialId,
        ]);

        return redirect()->route('clientes.index')->with('success', 'Cliente cadastrado com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     * Exibe também os cards de produtos comprados e serviços consumidos.
     */
    public function edit($id)
    {
        $cliente  = Cliente::findOrFail($id);
        $compras  = self::getCompras($id);

        // Busca as vendas de produtos do cliente (para o card "Produtos Comprados")
        $produtosComprados = DB::select(
            "SELECT 
                v.IDVenda,
                p.NMProduto,
                v.NUUnidadesVendidas,
                v.VLVenda,
                v.DTVenda,
                pag.NMPagamento
             FROM vendas v
             INNER JOIN produtos p ON p.IDProduto = v.IDProduto
             INNER JOIN pagamentos pag ON pag.IDPagamento = v.IDPagamento
             WHERE v.IDCliente = ?
             ORDER BY v.DTVenda DESC",
            [$id]
        );

        return view('clientes.create', compact('cliente', 'compras', 'produtosComprados'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nomeCliente'     => 'required|string|min:1|max:100',
            'emailCliente'    => 'nullable|email|max:150',
            'telefoneCliente' => 'nullable|string|max:20',
            'cpfCliente'      => 'nullable|string|max:20',
            'IDFilial'        => 'nullable|integer|exists:filiais,IDFilial',
        ]);

        $cliente = Cliente::findOrFail($id);
        $cliente->update([
            'NMCliente'         => $request->nomeCliente,
            'NMEmailCliente'    => $request->emailCliente,
            'NUTelefoneCliente' => $request->telefoneCliente,
            'NUCpfCliente'      => $request->cpfCliente,
            'IDFilial'          => $request->IDFilial ?: $cliente->IDFilial,
        ]);

        return redirect()->route('clientes.index')->with('success', 'Cliente atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $temDevedor   = Devedor::where('IDCliente', $id)->exists();
        $temCrediario = Crediario::where('IDCliente', $id)->exists();

        if ($temDevedor) {
            return redirect()->back()->with('error', 'Você não pode excluir esse cliente pois ele tem uma dívida.');
        }

        if ($temCrediario) {
            return redirect()->back()->with('error', 'Você não pode excluir esse cliente pois ele tem créditos.');
        }

        Cliente::destroy($id);

        return redirect()->route('clientes.index')->with('success', 'Cliente excluído com sucesso!');
    }

    // ================================
    // MÉTODOS ESTÁTICOS (COMPATIBILIDADE)
    // ================================

    /**
     * Sincroniza/Cria um cliente baseado no CPF.
     *
     * @param  array  $dados
     * @return void
     */
    public static function sincronizaCliente($dados)
    {
        Cliente::firstOrCreate(
            ['NUCpfCliente' => $dados['NUCpfCliente']],
            [
                'NMCliente'         => $dados['NMCliente'],
                'NMEmailCliente'    => $dados['NMEmailCliente'],
                'NUTelefoneCliente' => $dados['NUTelefoneCliente'],
                'IDFilial'          => $dados['IDFilial'],
            ]
        );
    }

    /**
     * Retorna as compras (cupons) de um cliente.
     *
     * @param  int  $IDCliente
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getCompras($IDCliente)
    {
        return Cupom::with('cliente')
            ->where('IDCliente', $IDCliente)
            ->get(['ANCupom', 'IDCliente']);
    }

    /**
     * Retorna os dados de pendências (dívidas) de um cliente.
     *
     * @param  int  $IDCliente
     * @return array
     */
    public static function getPendencias($IDCliente)
    {
        $divida = Devedor::where('IDCliente', $IDCliente)->first();

        if ($divida) {
            return [
                'temDivida'   => true,
                'valorDivida' => $divida->VLDivida,
            ];
        }

        return [
            'temDivida'   => false,
            'valorDivida' => 0,
        ];
    }

    /**
     * Retorna lista de clientes de uma filial, incluindo informação de dívida.
     *
     * @param  int  $IDFilial
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function listarClientes($IDFilial)
    {
        return Cliente::with('devedor')
            ->where('IDFilial', $IDFilial)
            ->get()
            ->map(function ($cliente) {
                $cliente->divida = $cliente->devedor ? $cliente->devedor->VLDivida : 0;
                return $cliente;
            });
    }

    /**
     * Retorna lista de clientes devedores de uma filial.
     *
     * @param  int  $IDFilial
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function listarDevedores($IDFilial)
    {
        return Devedor::with('cliente')
            ->whereHas('cliente', function ($query) use ($IDFilial) {
                $query->where('IDFilial', $IDFilial);
            })
            ->get();
    }

    /**
     * Retorna clientes que NÃO são devedores e NÃO possuem crediário.
     *
     * @param  int  $IDFilial
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getSelectDevedores($IDFilial)
    {
        $idsComCrediario = Crediario::whereHas('cliente', function ($query) use ($IDFilial) {
            $query->where('IDFilial', $IDFilial);
        })->pluck('IDCliente');

        $idsComDivida = Devedor::whereHas('cliente', function ($query) use ($IDFilial) {
            $query->where('IDFilial', $IDFilial);
        })->pluck('IDCliente');

        return Cliente::where('IDFilial', $IDFilial)
            ->whereNotIn('IDCliente', $idsComCrediario)
            ->whereNotIn('IDCliente', $idsComDivida)
            ->select('IDCliente', 'NMCliente')
            ->get();
    }

    /**
     * Retorna clientes que NÃO são devedores e NÃO possuem crediário.
     *
     * @param  int  $IDFilial
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getSelectCrediarios($IDFilial)
    {
        $idsComCrediario = Crediario::whereHas('cliente', function ($query) use ($IDFilial) {
            $query->where('IDFilial', $IDFilial);
        })->pluck('IDCliente');

        $idsComDivida = Devedor::whereHas('cliente', function ($query) use ($IDFilial) {
            $query->where('IDFilial', $IDFilial);
        })->pluck('IDCliente');

        return Cliente::where('IDFilial', $IDFilial)
            ->whereNotIn('IDCliente', $idsComDivida)
            ->whereNotIn('IDCliente', $idsComCrediario)
            ->select('IDCliente', 'NMCliente')
            ->get();
    }

    /**
     * Retorna lista de clientes com crediário de uma filial.
     *
     * @param  int  $IDFilial
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function listarCrediarios($IDFilial)
    {
        return Crediario::with('cliente')
            ->whereHas('cliente', function ($query) use ($IDFilial) {
                $query->where('IDFilial', $IDFilial);
            })
            ->get();
    }

    /**
     * Retorna um cliente específico pelo ID.
     *
     * @param  int  $IDCliente
     * @return \App\Models\Cliente|null
     */
    public function listarCliente($IDCliente)
    {
        return Cliente::find($IDCliente);
    }

    /**
     * Retorna um devedor específico com os dados do cliente.
     *
     * @param  int  $IDDevedor
     * @return \App\Models\Devedor|null
     */
    public function listarDevedor($IDDevedor)
    {
        return Devedor::with('cliente')->find($IDDevedor);
    }

    /**
     * Retorna um crediário específico com os dados do cliente.
     *
     * @param  int  $IDCrediario
     * @return \App\Models\Crediario|null
     */
    public function listarCrediario($IDCrediario)
    {
        return Crediario::with('cliente')->find($IDCrediario);
    }

    /**
     * Exclui um cliente (com verificação de dívidas e créditos).
     *
     * @param  int  $IDCliente
     * @param  int  $confirma  0 = verificar pendências, 1 = forçar exclusão
     * @return string  JSON encoded
     */
    public function excluirCliente($IDCliente, $confirma)
    {
        $temDevedor   = Devedor::where('IDCliente', $IDCliente)->exists();
        $temCrediario = Crediario::where('IDCliente', $IDCliente)->exists();

        if ($confirma == 0) {
            if ($temDevedor) {
                $retorno['podeExcluir'] = false;
                $retorno['msg'] = "Você não pode excluir esse cliente pois ele tem uma dívida";
            } elseif ($temCrediario) {
                $retorno['podeExcluir'] = false;
                $retorno['msg'] = "Você não pode excluir esse cliente pois ele tem créditos";
            } else {
                $retorno['podeExcluir'] = true;
            }
        } else {
            $retorno['podeExcluir'] = true;
            Cliente::destroy($IDCliente);
        }

        return json_encode($retorno);
    }

    /**
     * Exclui um crediário específico.
     *
     * @param  int  $IDCrediario
     * @return bool|null
     */
    public function excluirCrediario($IDCrediario)
    {
        return Crediario::destroy($IDCrediario);
    }

    /**
     * Exclui um devedor específico.
     *
     * @param  int  $IDDevedor
     * @return bool|null
     */
    public function excluirDevedor($IDDevedor)
    {
        return Devedor::destroy($IDDevedor);
    }

    /**
     * Salva ou atualiza um cliente.
     *
     * @param  array  $dados
     * @return \App\Models\Cliente
     */
    public function salvarCliente($dados)
    {
        $filial = $_SESSION['login']['filial'] ?? $dados['filial'];

        if (!empty($dados['IDCliente'])) {
            $cliente = Cliente::find($dados['IDCliente']);
            if ($cliente) {
                $cliente->update([
                    'NMCliente'         => $dados['nomeCliente'],
                    'NMEmailCliente'    => $dados['emailCliente'],
                    'NUTelefoneCliente' => $dados['telefoneCliente'],
                ]);
            }
        } else {
            $cliente = Cliente::create([
                'NMCliente'         => $dados['nomeCliente'],
                'NMEmailCliente'    => $dados['emailCliente'],
                'NUTelefoneCliente' => $dados['telefoneCliente'],
                'NUCpfCliente'      => $dados['cpfCliente'],
                'IDFilial'          => $filial,
            ]);
        }

        return $cliente;
    }

    /**
     * Salva ou atualiza um crediário.
     *
     * @param  array  $dados
     * @return \App\Models\Crediario
     */
    public function salvarCrediario($dados)
    {
        if (!empty($dados['IDCrediario'])) {
            $crediario = Crediario::find($dados['IDCrediario']);
            if ($crediario) {
                $crediario->update([
                    'NUCredito'        => $dados['creditoCrediario'],
                    'DTTerminoCredito' => $dados['creditoAte'],
                ]);
            }
        } else {
            $crediario = Crediario::create([
                'IDCliente'        => $dados['nomeCrediario'],
                'NUCredito'        => $dados['creditoCrediario'],
                'DTTerminoCredito' => $dados['creditoAte'],
            ]);
        }

        return $crediario;
    }

    /**
     * Salva ou atualiza um devedor.
     *
     * @param  array  $dados
     * @return \App\Models\Devedor
     */
    public function salvarDevedor($dados)
    {
        if (!empty($dados['IDDevedor'])) {
            $devedor = Devedor::find($dados['IDDevedor']);
            if ($devedor) {
                $devedor->update([
                    'VLDivida' => $dados['valorDivida'],
                ]);
            }
        } else {
            $devedor = Devedor::create([
                'IDCliente' => $dados['nomeDevedor'],
                'VLDivida'  => $dados['valorDivida'],
            ]);
        }

        return $devedor;
    }
}