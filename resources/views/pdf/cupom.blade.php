<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cupom de Venda #{{ $venda->IDVenda }}</title>
    <style>
        @page {
            margin: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 10px;
            line-height: 1.2;
            color: #000;
            margin: 0;
            padding: 8px;
            width: 72mm;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .divisor {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }
        .tabela-itens {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        .tabela-itens th {
            border-bottom: 1px solid #000;
            text-align: left;
        }
    </style>
</head>
<body>

    @php
        $filial = $venda->colaborador->filial ?? null;
    @endphp

    <!-- Cabeçalho -->
    <div class="text-center">
        <span class="bold" style="font-size: 12px;">{{ $filial->NMFilial ?? 'NOME DA LOJA' }}</span><br>
        <span>{{ $filial->NUTelefoneFilial ?? 'Contato' }}</span>
    </div>

    <div class="divisor"></div>

    <!-- Informações de Identificação -->
    <div>
        <span class="bold">CUPOM NÃO FISCAL</span><br>
        <span class="bold">VENDA Nº:</span> {{ str_pad($venda->IDVenda, 6, '0', STR_PAD_LEFT) }}<br>
        <span class="bold">DATA:</span> {{ \Carbon\Carbon::parse($venda->DTVenda)->format('d/m/Y H:i:s') }}<br>
        <span class="bold">ATENDENTE:</span> {{ $venda->colaborador->NMColaborador ?? 'Colaborador' }}<br>
        <span class="bold">CLIENTE:</span> {{ $venda->cliente->NMCliente ?? 'CONSUMIDOR PADRÃO' }}
    </div>

    <div class="divisor"></div>

    <!-- Tabela de Itens -->
    <table class="tabela-itens">
        <thead>
            <tr>
                <th>Item (Cód) / Descrição</th>
                <th class="text-right">Qtd x Unit</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    #{{ $venda->IDProduto }} - {{ $venda->produto->NMProduto ?? 'Produto' }}
                </td>
                <td class="text-right" style="vertical-align: bottom;">
                    {{ $venda->NUUnidadesVendidas }} x
                    R$ {{ number_format($venda->VLVenda / max($venda->NUUnidadesVendidas, 1), 2, ',', '.') }}
                </td>
                <td class="text-right" style="vertical-align: bottom; width: 60px;">
                    R$ {{ number_format($venda->VLVenda, 2, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>

    <div class="divisor"></div>

    <!-- Totais e Pagamento -->
    <div>
        <table style="width: 100%;">
            <tr class="bold" style="font-size: 11px;">
                <td>VALOR TOTAL:</td>
                <td class="text-right">R$ {{ number_format($venda->VLVenda, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td>FORMA PAGTO:</td>
                <td class="text-right">{{ $venda->pagamento->NMPagamento ?? 'A Vista' }}</td>
            </tr>
        </table>
    </div>

    <div class="divisor"></div>

    <!-- Rodapé -->
    <div class="text-center" style="font-size: 9px; margin-top: 10px;">
        Obrigado pela preferência!<br>
        Volte sempre.
    </div>

</body>
</html>