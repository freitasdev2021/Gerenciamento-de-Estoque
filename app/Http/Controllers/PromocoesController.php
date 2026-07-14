<?php

namespace App\Http\Controllers;

use App\Models\Filial;
use App\Models\Promocao;
use App\Models\Promocional;
use App\Models\Produto;
use App\Models\Venda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PromocoesController extends Controller
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

        $promocoes = Promocao::whereNull('STDelete')
            ->when($filialSelecionada, function ($query, $filialSelecionada) {
                return $query->where('IDContrato', $filialSelecionada);
            })
            ->orderBy('NMPromo')
            ->get();

        $filiais = Filial::orderBy('NMFilial')->get();

        return view('promocoes.index', compact('promocoes', 'filiais', 'filialSelecionada'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $promocao = null;
        $filiais = Filial::orderBy('NMFilial')->get();
        return view('promocoes.create', compact('promocao', 'filiais'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nomePromo'     => 'required|string|min:1|max:100',
            'inicioPromo'   => 'required|date',
            'fimPromo'      => 'required|date|after_or_equal:inicioPromo',
            'descontoPromo' => 'required|string',
            'tipoPromo'     => 'required|string|in:1,%',
            'IDContrato'      => 'nullable|integer|exists:contratos,IDContrato',
        ]);

        $NUDescontoPromo = $request->tipoPromo == '%'
            ? intval($request->descontoPromo)
            : $this->decimal($request->descontoPromo);

        $filialId = $request->IDContrato ?: ($_SESSION['login']['filial'] ?? null);

        Promocao::create([
            'NMPromo'         => $request->nomePromo,
            'DTInicioPromo'   => $request->inicioPromo,
            'DTTerminoPromo'  => $request->fimPromo,
            'NUDescontoPromo' => $NUDescontoPromo,
            'TPDesconto'      => $request->tipoPromo,
            'IDContrato'        => $filialId,
        ]);

        return redirect()->route('promocoes.index')->with('success', 'Promoção cadastrada com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $promocao = Promocao::findOrFail($id);
        $filiais = Filial::orderBy('NMFilial')->get();
        return view('promocoes.create', compact('promocao', 'filiais'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nomePromo'     => 'required|string|min:1|max:100',
            'inicioPromo'   => 'required|date',
            'fimPromo'      => 'required|date|after_or_equal:inicioPromo',
            'descontoPromo' => 'required|string',
            'tipoPromo'     => 'required|string|in:1,%',
            'IDContrato'      => 'nullable|integer|exists:contratos,IDContrato',
        ]);

        $NUDescontoPromo = $request->tipoPromo == '%'
            ? intval($request->descontoPromo)
            : $this->decimal($request->descontoPromo);

        $promocao = Promocao::findOrFail($id);
        $promocao->update([
            'NMPromo'         => $request->nomePromo,
            'DTInicioPromo'   => $request->inicioPromo,
            'DTTerminoPromo'  => $request->fimPromo,
            'NUDescontoPromo' => $NUDescontoPromo,
            'TPDesconto'      => $request->tipoPromo,
            'IDContrato'        => $request->IDContrato ?: $promocao->IDContrato,
        ]);

        return redirect()->route('promocoes.index')->with('success', 'Promoção atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $temVenda = self::confVendaPromo($id);

        if ($temVenda) {
            Promocao::where('IDPromocao', $id)->update(['STDelete' => 1]);
        } else {
            self::delPromocional($id);
            Promocao::destroy($id);
        }

        return redirect()->route('promocoes.index')->with('success', 'Promoção excluída com sucesso!');
    }

    /**
     * Exibe a página de vinculação de produtos a uma promoção.
     */
    public function vincular($id)
    {
        $promocao = Promocao::findOrFail($id);
        $produtos = self::getPromocional($id);

        return view('promocoes.vincular', compact('promocao', 'produtos'));
    }

    /**
     * Salva os vínculos de produtos à promoção.
     * Chama setPromocional em loop para cada IDProduto enviado.
     */
    public function setVinculos(Request $request, $id)
    {
        $request->validate([
            'produtos' => 'nullable|array',
            'produtos.*' => 'integer|exists:produtos,IDProduto',
        ]);

        // Remove todos os vínculos atuais
        self::delPromocional($id);

        // Vincula os produtos selecionados
        if ($request->has('produtos')) {
            foreach ($request->produtos as $IDProduto) {
                self::setPromocional([
                    'IDPromocao' => $id,
                    'IDProduto'  => $IDProduto,
                ]);
            }
        }

        return redirect()->route('promocoes.index')->with('success', 'Produtos vinculados à promoção com sucesso!');
    }

    // ================================
    // MÉTODOS ESTÁTICOS (COMPATIBILIDADE)
    // ================================

    /**
     * Retorna a lista de promoções ativas de uma filial.
     *
     * @param  int  $IDContrato
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function listarPromocoes($IDContrato)
    {
        return Promocao::where('IDContrato', $IDContrato)
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
            return Promocao::where('IDPromocao', $IDPromocao)
                ->update(['STDelete' => 1]);
        }

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
        if ($dados['tipoPromo'] == '%') {
            $NUDescontoPromo = intval($dados['descontoPromo']);
        } else {
            $NUDescontoPromo = $this->decimal($dados['descontoPromo']);
        }

        if (!empty($dados['IDPromocao'])) {
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
            $promocao = Promocao::create([
                'NMPromo'         => $dados['nomePromo'],
                'DTInicioPromo'   => $dados['inicioPromo'],
                'DTTerminoPromo'  => $dados['fimPromo'],
                'NUDescontoPromo' => $NUDescontoPromo,
                'TPDesconto'      => $dados['tipoPromo'],
                'IDContrato'        => $_SESSION['login']['filial'],
            ]);
        }

        return $promocao;
    }

    /**
     * Retorna lista de produtos com indicador de vínculo a uma promoção.
     *
     * @param  int  $IDPromocao
     * @return array
     */
    public static function getPromocional($IDPromocao)
    {
        $filialId = 1;

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
            AND fornecedores.IDContrato = ?
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
     *
     * @param  int    $IDProduto
     * @param  float  $valorProduto
     * @param  int    $IDContrato
     * @return float  Valor com desconto aplicado (ou valor original se não houver promoção)
     */
    public static function confProdutoPromocional($IDProduto, $valorProduto, $IDContrato)
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
                AND promocoes.IDContrato = ?
                AND promocionais.IDProduto = ?
            GROUP BY promocoes.IDPromocao
        ";

        $result = DB::select($SQL, [$IDContrato, $IDProduto]);

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
            return str_replace(',', '.', str_replace('.', '', $valor));
        }
        return str_replace(',', '.', $valor);
    }
}