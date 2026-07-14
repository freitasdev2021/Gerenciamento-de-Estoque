<?php

namespace App\Http\Controllers;

use App\Models\Cupom;
use App\Models\Venda;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class CuponsController extends Controller
{
   
    public function imprimirCupom($vendaId)
    {
        // 1. Busca os dados da venda com os relacionamentos necessários
        $venda = Venda::with(['produto', 'cliente', 'colaborador.filial', 'pagamento'])
                      ->findOrFail($vendaId);

        // 2. Define o tamanho do papel personalizado em Pontos (pt)
        // 80mm de largura = 226.77pt
        $largura = 226.77;
        $altura = 841.89;

        // 3. Renderiza a view passando os dados
        $pdf = Pdf::loadView('pdf.cupom', compact('venda'))
                ->setPaper([0, 0, $largura, $altura]);

        // 4. Retorna o PDF diretamente no navegador (stream) para impressão/download
        return $pdf->stream("cupom-venda-{$venda->IDVenda}.pdf");
    }

    /**
     * Insere um cupom, determinando a filial pela sessão ou pelo parâmetro.
     *
     * @param  array  $dados  Dados do cupom (IDCaixa, CDVenda, ANCupom, IDCliente, IDFilial)
     * @return \App\Models\Cupom
     */
    public static function setCupom($dados)
    {
        $filial = $_SESSION['login']['filial'] ?? $dados['IDFilial'];

        return Cupom::create([
            'IDCaixa'   => $dados['IDCaixa'],
            'CDVenda'   => $dados['CDVenda'],
            'ANCupom'   => $dados['ANCupom'],
            'IDCliente' => $dados['IDCliente'],
            'IDFilial'  => $filial,
        ]);
    }

    /**
     * Retorna os dados do cabeçalho do cupom (filial + empresa).
     * Query com JOIN entre filiais e empresas.
     *
     * @param  int   $IDFilial
     * @return array
     */
    public static function getHeaderCupom($IDFilial)
    {
        return DB::select(
            "SELECT DSEnderecoJSON, NMFilial, NUCnpjEmpresa, NMRazaoEmpresa 
             FROM filiais 
             INNER JOIN empresas USING(IDEmpresa) 
             WHERE IDFilial = ?",
            [$IDFilial]
        );
    }
}