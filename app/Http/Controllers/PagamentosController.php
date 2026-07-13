<?php

namespace App\Http\Controllers;

use App\Models\Filial;
use App\Models\Pagamento;
use App\Models\Venda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PagamentosController extends Controller
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

        $pagamentos = Pagamento::whereNull('STDelete')
            ->when($filialSelecionada, function ($query, $filialSelecionada) {
                return $query->where('IDFilial', $filialSelecionada);
            })
            ->orderBy('NMPagamento')
            ->get();

        $filiais = Filial::orderBy('NMFilial')->get();

        return view('pagamentos.index', compact('pagamentos', 'filiais', 'filialSelecionada'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $pagamento = null;
        $filiais = Filial::orderBy('NMFilial')->get();
        return view('pagamentos.create', compact('pagamento', 'filiais'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nomeMetodo'     => 'required|string|min:1|max:100',
            'metodoMetodo'   => 'required|string|min:1|max:50',
            'parcelasMetodo' => 'required|integer|min:1|max:99',
            'tipoMetodo'     => 'required|string|in:1,%',
            'descontoMetodo' => 'required|string',
            'jurosMetodo'    => 'nullable|numeric|min:0',
            'IDFilial'       => 'nullable|integer|exists:filiais,IDFilial',
        ]);

        $descontoMetodo = $request->tipoMetodo == '1'
            ? intval($request->descontoMetodo)
            : $this->decimal($request->descontoMetodo);

        $filialId = $request->IDFilial ?: ($_SESSION['login']['filial'] ?? null);

        Pagamento::create([
            'NMPagamento' => $request->nomeMetodo,
            'QTDesconto'  => $descontoMetodo,
            'DSMetodo'    => $request->metodoMetodo,
            'QTParcelas'  => $request->parcelasMetodo,
            'TPDesconto'  => $request->tipoMetodo,
            'NUJuros'     => $request->jurosMetodo ?? 0,
            'IDFilial'    => $filialId,
        ]);

        return redirect()->route('pagamentos.index')->with('success', 'Pagamento cadastrado com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     * Reutiliza a view create.blade.php passando o pagamento.
     */
    public function edit($id)
    {
        $pagamento = Pagamento::findOrFail($id);
        $filiais = Filial::orderBy('NMFilial')->get();
        return view('pagamentos.create', compact('pagamento', 'filiais'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nomeMetodo'     => 'required|string|min:1|max:100',
            'metodoMetodo'   => 'required|string|min:1|max:50',
            'parcelasMetodo' => 'required|integer|min:1|max:99',
            'tipoMetodo'     => 'required|string|in:1,%',
            'descontoMetodo' => 'required|string',
            'jurosMetodo'    => 'nullable|numeric|min:0',
            'IDFilial'       => 'nullable|integer|exists:filiais,IDFilial',
        ]);

        $descontoMetodo = $request->tipoMetodo == '1'
            ? intval($request->descontoMetodo)
            : $this->decimal($request->descontoMetodo);

        $pagamento = Pagamento::findOrFail($id);
        $pagamento->update([
            'NMPagamento' => $request->nomeMetodo,
            'QTDesconto'  => $descontoMetodo,
            'DSMetodo'    => $request->metodoMetodo,
            'QTParcelas'  => $request->parcelasMetodo,
            'TPDesconto'  => $request->tipoMetodo,
            'NUJuros'     => $request->jurosMetodo ?? 0,
            'IDFilial'    => $request->IDFilial ?: $pagamento->IDFilial,
        ]);

        return redirect()->route('pagamentos.index')->with('success', 'Pagamento atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $temVenda = Venda::where('IDPagamento', $id)->exists();

        if ($temVenda) {
            Pagamento::where('IDPagamento', $id)->update(['STDelete' => 1]);
        } else {
            Pagamento::destroy($id);
        }

        return redirect()->route('pagamentos.index')->with('success', 'Pagamento excluído com sucesso!');
    }

    // ================================
    // MÉTODOS ESTÁTICOS (COMPATIBILIDADE)
    // ================================

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
            return Pagamento::where('IDPagamento', $IDPagamento)
                ->update(['STDelete' => 1]);
        }

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
        if ($dados['tipoMetodo'] == '1') {
            $descontoMetodo = intval($dados['descontoMetodo']);
        } else {
            $descontoMetodo = $this->decimal($dados['descontoMetodo']);
        }

        if (!empty($dados['IDPagamento'])) {
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

    // ======================
    // MÉTODO AUXILIAR
    // ======================

    /**
     * Converte valor monetário para decimal.
     * Ex: "1.234,56" → "1234.56"
     *
     * @param  string  $valor
     * @return string
     */
    public function decimal($valor)
    {
        if (substr_count($valor, ',') == 1 && substr_count($valor, '.') >= 1) {
            // Formato 1.234,56
            return str_replace(',', '.', str_replace('.', '', $valor));
        }
        // Formato simples 1234,56 → 1234.56
        return str_replace(',', '.', $valor);
    }
}