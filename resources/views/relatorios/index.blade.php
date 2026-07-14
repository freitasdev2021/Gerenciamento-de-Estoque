@extends('layouts.appinterno')

@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="mb-3"><i class="fa-solid fa-chart-simple"></i> Relatório - Curva ABC de Produtos</h4>
        <hr>
    </div>
</div>

<!-- Filtro de Filial -->
<div class="row mb-3">
    <div class="col-md-4">
        <form method="GET" action="{{ route('relatorios.index') }}" id="formFiltro">
            <label for="filialId" class="form-label fw-bold"><i class="fa-solid fa-building"></i> Filial:</label>
            <select name="filialId" id="filialId" class="form-select" onchange="document.getElementById('formFiltro').submit();">
                @foreach($filiais as $filial)
                    <option value="{{ $filial->IDFilial }}" {{ $filial->IDFilial == $filialId ? 'selected' : '' }}>
                        {{ $filial->NMFilial }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>
</div>

@if(empty($abcData['tabela']))
    <!-- Sem dados -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center text-muted py-5">
                    <i class="fa-solid fa-chart-bar" style="font-size: 48px;"></i>
                    <p class="mt-2">Nenhuma venda registrada para esta filial.</p>
                </div>
            </div>
        </div>
    </div>
@else
    <!-- Resumo das Classes -->
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h5 class="card-title text-danger"><i class="fa-solid fa-star"></i> Classe A</h5>
                    <p class="card-text fs-4 fw-bold">{{ $abcData['countA'] }} produtos</p>
                    <small class="text-muted">~80% do faturamento</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h5 class="card-title text-warning"><i class="fa-solid fa-star-half-stroke"></i> Classe B</h5>
                    <p class="card-text fs-4 fw-bold">{{ $abcData['countB'] }} produtos</p>
                    <small class="text-muted">~15% do faturamento</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h5 class="card-title text-success"><i class="fa-regular fa-star"></i> Classe C</h5>
                    <p class="card-text fs-4 fw-bold">{{ $abcData['countC'] }} produtos</p>
                    <small class="text-muted">~5% do faturamento</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico Curva ABC -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <canvas id="curvaAbcChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela de Detalhes -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fa-solid fa-table-list"></i> Detalhamento por Produto</h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-hover tabela" id="tabelaAbc">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Produto</th>
                                <th>Total Vendido</th>
                                <th>% Individual</th>
                                <th>% Acumulado</th>
                                <th>Classe</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($abcData['tabela'] as $linha)
                                @php
                                    $badgeClass = match($linha['classe']) {
                                        'A' => 'bg-danger',
                                        'B' => 'bg-warning text-dark',
                                        default => 'bg-success',
                                    };
                                @endphp
                                <tr>
                                    <td>{{ $linha['posicao'] }}</td>
                                    <td>{{ $linha['produto'] }}</td>
                                    <td>R$ {{ number_format($linha['total'], 2, ',', '.') }}</td>
                                    <td>{{ number_format($linha['percentual'], 2, ',', '.') }}%</td>
                                    <td>{{ number_format($linha['acumulado'], 2, ',', '.') }}%</td>
                                    <td><span class="badge {{ $badgeClass }}">{{ $linha['classe'] }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- Relatório de Categorias Mais Vendidas --}}
<div class="row mt-4">
    <div class="col-12">
        <h4 class="mb-3"><i class="fa-solid fa-tags"></i> Categorias Mais Vendidas</h4>
        <hr>
    </div>
</div>

@if(empty($categoriasData['labels']))
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center text-muted py-5">
                    <i class="fa-solid fa-tag" style="font-size: 48px;"></i>
                    <p class="mt-2">Nenhuma venda registrada para esta filial.</p>
                </div>
            </div>
        </div>
    </div>
@else
    <!-- Resumo Categorias -->
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h5 class="card-title text-info"><i class="fa-solid fa-layer-group"></i> Total de Categorias</h5>
                    <p class="card-text fs-4 fw-bold">{{ $categoriasData['totalItens'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <h5 class="card-title text-primary"><i class="fa-solid fa-dollar-sign"></i> Faturamento Total</h5>
                    <p class="card-text fs-4 fw-bold">R$ {{ number_format($categoriasData['faturamentoTotal'], 2, ',', '.') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h5 class="card-title text-success"><i class="fa-solid fa-chart-line"></i> Lucro Total</h5>
                    <p class="card-text fs-4 fw-bold">R$ {{ number_format($categoriasData['lucroTotal'], 2, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de Categorias -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <canvas id="categoriasChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- Lista de Produtos Mais Vendidos --}}
<div class="row mt-4">
    <div class="col-12">
        <h4 class="mb-3"><i class="fa-solid fa-box"></i> Produtos Mais Vendidos</h4>
        <hr>
    </div>
</div>

@if(empty($produtosMaisVendidos))
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center text-muted py-5">
                    <i class="fa-solid fa-box-open" style="font-size: 48px;"></i>
                    <p class="mt-2">Nenhuma venda registrada para esta filial.</p>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-hover tabela" id="tabelaProdutos">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Produto</th>
                                <th>Estoque Atual</th>
                                <th>Valor Investido</th>
                                <th>Faturamento</th>
                                <th>Lucro</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($produtosMaisVendidos as $index => $produto)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $produto['nome'] }}</td>
                                    <td>{{ $produto['estoque_atual'] }} un</td>
                                    <td>R$ {{ number_format($produto['valor_investido'], 2, ',', '.') }}</td>
                                    <td>R$ {{ number_format($produto['faturamento'], 2, ',', '.') }}</td>
                                    <td class="{{ $produto['lucro'] >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
                                        R$ {{ number_format($produto['lucro'], 2, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- Gráfico Valor Investido em Estoque x Valor Vendido (Últimos 12 meses) --}}
<div class="row mt-4">
    <div class="col-12">
        <h4 class="mb-3"><i class="fa-solid fa-chart-column"></i> Valor Investido em Estoque x Valor Vendido (Últimos 12 Meses)</h4>
        <hr>
    </div>
</div>

@if(empty($investidoVendidoData['labels']))
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center text-muted py-5">
                    <i class="fa-solid fa-chart-simple" style="font-size: 48px;"></i>
                    <p class="mt-2">Nenhum dado disponível para esta filial.</p>
                </div>
            </div>
        </div>
    </div>
@else
    <!-- Resumo Investido vs Vendido -->
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h5 class="card-title text-danger"><i class="fa-solid fa-cart-shopping"></i> Total Investido</h5>
                    <p class="card-text fs-4 fw-bold">R$ {{ number_format($investidoVendidoData['totalInvestido'], 2, ',', '.') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h5 class="card-title text-success"><i class="fa-solid fa-dollar-sign"></i> Total Vendido</h5>
                    <p class="card-text fs-4 fw-bold">R$ {{ number_format($investidoVendidoData['totalVendido'], 2, ',', '.') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card {{ ($investidoVendidoData['totalVendido'] >= $investidoVendidoData['totalInvestido']) ? 'border-primary' : 'border-warning' }}">
                <div class="card-body text-center">
                    <h5 class="card-title {{ ($investidoVendidoData['totalVendido'] >= $investidoVendidoData['totalInvestido']) ? 'text-primary' : 'text-warning' }}"><i class="fa-solid fa-scale-balanced"></i> Resultado</h5>
                    @php
                        $resultado = $investidoVendidoData['totalVendido'] - $investidoVendidoData['totalInvestido'];
                    @endphp
                    <p class="card-text fs-4 fw-bold {{ $resultado >= 0 ? 'text-success' : 'text-danger' }}">
                        R$ {{ number_format($resultado, 2, ',', '.') }}
                    </p>
                    <small class="text-muted">{{ $resultado >= 0 ? 'Lucro' : 'Prejuízo' }} no período</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de Barras Investido vs Vendido -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <canvas id="investidoVendidoChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ============ GRÁFICO CURVA ABC ============
    const abcLabels    = @json($abcData['labels'] ?? []);
    const abcValores   = @json($abcData['valores'] ?? []);
    const abcClasses   = @json($abcData['classes'] ?? []);
    const abcAcumulado = @json($abcData['acumulado'] ?? []);

    if (abcLabels.length > 0) {
        // Cores por classe
        const cores = {
            'A': 'rgba(220, 53, 69, 0.8)',
            'B': 'rgba(255, 193, 7, 0.8)',
            'C': 'rgba(40, 167, 69, 0.8)'
        };
        const borderCores = {
            'A': 'rgba(220, 53, 69, 1)',
            'B': 'rgba(255, 193, 7, 1)',
            'C': 'rgba(40, 167, 69, 1)'
        };

        const bgColors  = abcClasses.map(c => cores[c] || 'rgba(108, 117, 125, 0.8)');
        const brdColors = abcClasses.map(c => borderCores[c] || 'rgba(108, 117, 125, 1)');

        const ctxAbc = document.getElementById('curvaAbcChart').getContext('2d');
        new Chart(ctxAbc, {
            type: 'bar',
            data: {
                labels: abcLabels,
                datasets: [
                    {
                        label: 'Valor Vendido (R$)',
                        data: abcValores,
                        backgroundColor: bgColors,
                        borderColor: brdColors,
                        borderWidth: 1,
                        yAxisID: 'y-axis-venda'
                    },
                    {
                        label: '% Acumulado',
                        data: abcAcumulado,
                        type: 'line',
                        fill: false,
                        borderColor: 'rgba(13, 110, 253, 1)',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        borderWidth: 2,
                        pointRadius: 4,
                        pointBackgroundColor: 'rgba(13, 110, 253, 1)',
                        tension: 0.1,
                        yAxisID: 'y-axis-percent'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                title: {
                    display: true,
                    text: 'Curva ABC - Faturamento por Produto',
                    fontSize: 16
                },
                tooltips: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(tooltipItem, data) {
                            if (tooltipItem.datasetIndex === 0) {
                                return 'R$ ' + parseFloat(tooltipItem.value).toLocaleString('pt-BR', { minimumFractionDigits: 2 });
                            } else {
                                return tooltipItem.value + '% acumulado';
                            }
                        }
                    }
                },
                scales: {
                    xAxes: [{
                        ticks: {
                            autoSkip: false,
                            maxRotation: 45,
                            minRotation: 45,
                            fontSize: 9
                        }
                    }],
                    yAxes: [
                        {
                            id: 'y-axis-venda',
                            type: 'linear',
                            position: 'left',
                            scaleLabel: {
                                display: true,
                                labelString: 'Valor (R$)'
                            },
                            ticks: {
                                beginAtZero: true,
                                callback: function(value) {
                                    return 'R$ ' + value.toLocaleString('pt-BR');
                                }
                            }
                        },
                        {
                            id: 'y-axis-percent',
                            type: 'linear',
                            position: 'right',
                            scaleLabel: {
                                display: true,
                                labelString: '% Acumulado'
                            },
                            ticks: {
                                beginAtZero: true,
                                max: 100,
                                callback: function(value) {
                                    return value + '%';
                                }
                            },
                            gridLines: {
                                drawOnChartArea: false
                            }
                        }
                    ]
                },
                plugins: {
                    datalabels: {
                        display: false
                    }
                }
            }
        });

        // Inicializa DataTables na tabela
        if (typeof $.fn.DataTable !== 'undefined' && $('#tabelaAbc tbody tr').length > 0) {
            $('#tabelaAbc').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json"
                },
                "pageLength": 25,
                "order": [[4, "asc"]]
            });
        }
    }

    // ============ GRÁFICO CATEGORIAS MAIS VENDIDAS ============
    const catLabels  = @json($categoriasData['labels'] ?? []);
    const catValores = @json($categoriasData['valores'] ?? []);

    if (catLabels.length > 0) {
        // Paleta de cores vibrantes para as categorias
        const catPalette = [
            'rgba(54, 162, 235, 0.8)',   // azul
            'rgba(255, 159, 64, 0.8)',   // laranja
            'rgba(75, 192, 192, 0.8)',   // verde água
            'rgba(153, 102, 255, 0.8)',  // roxo
            'rgba(255, 99, 132, 0.8)',   // vermelho
            'rgba(255, 205, 86, 0.8)',   // amarelo
            'rgba(201, 203, 207, 0.8)',  // cinza
            'rgba(34, 139, 34, 0.8)',    // verde escuro
            'rgba(220, 20, 60, 0.8)',    // carmesim
            'rgba(0, 191, 255, 0.8)',    // azul profundo
            'rgba(255, 140, 0, 0.8)',    // laranja escuro
            'rgba(147, 112, 219, 0.8)',  // lavanda
        ];

        const catBgColors  = catLabels.map((_, i) => catPalette[i % catPalette.length]);
        const catBrdColors = catBgColors.map(c => c.replace('0.8', '1'));

        const ctxCat = document.getElementById('categoriasChart').getContext('2d');
        new Chart(ctxCat, {
            type: 'bar',
            data: {
                labels: catLabels,
                datasets: [{
                    label: 'Total Vendido (R$)',
                    data: catValores,
                    backgroundColor: catBgColors,
                    borderColor: catBrdColors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                title: {
                    display: true,
                    text: 'Faturamento por Categoria',
                    fontSize: 16
                },
                legend: {
                    display: false
                },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem) {
                            return 'R$ ' + parseFloat(tooltipItem.value).toLocaleString('pt-BR', { minimumFractionDigits: 2 });
                        }
                    }
                },
                scales: {
                    xAxes: [{
                        ticks: {
                            autoSkip: false,
                            maxRotation: 45,
                            minRotation: 45,
                            fontSize: 10
                        }
                    }],
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            callback: function(value) {
                                return 'R$ ' + value.toLocaleString('pt-BR');
                            }
                        },
                        scaleLabel: {
                            display: true,
                            labelString: 'Valor (R$)'
                        }
                    }]
                },
                plugins: {
                    datalabels: {
                        display: false
                    }
                }
            }
        });
    }

    // ============ GRÁFICO VALOR INVESTIDO x VALOR VENDIDO (ÚLTIMOS 12 MESES) ============
    const invLabels    = @json($investidoVendidoData['labels'] ?? []);
    const invInvestido = @json($investidoVendidoData['investido'] ?? []);
    const invVendido   = @json($investidoVendidoData['vendido'] ?? []);

    if (invLabels.length > 0) {
        const ctxInv = document.getElementById('investidoVendidoChart').getContext('2d');
        new Chart(ctxInv, {
            type: 'bar',
            data: {
                labels: invLabels,
                datasets: [
                    {
                        label: 'Valor Investido (R$)',
                        data: invInvestido,
                        backgroundColor: 'rgba(220, 53, 69, 0.7)',
                        borderColor: 'rgba(220, 53, 69, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Valor Vendido (R$)',
                        data: invVendido,
                        backgroundColor: 'rgba(40, 167, 69, 0.7)',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                title: {
                    display: true,
                    text: 'Valor Investido em Estoque x Valor Vendido por Mês',
                    fontSize: 16
                },
                tooltips: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(tooltipItem) {
                            return tooltipItem.dataset.label.replace('(R$)', '') + 'R$ ' + parseFloat(tooltipItem.value).toLocaleString('pt-BR', { minimumFractionDigits: 2 });
                        }
                    }
                },
                scales: {
                    xAxes: [{
                        ticks: {
                            autoSkip: false,
                            maxRotation: 45,
                            minRotation: 45,
                            fontSize: 10
                        }
                    }],
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            callback: function(value) {
                                return 'R$ ' + value.toLocaleString('pt-BR');
                            }
                        },
                        scaleLabel: {
                            display: true,
                            labelString: 'Valor (R$)'
                        }
                    }]
                },
                plugins: {
                    datalabels: {
                        display: false
                    }
                }
            }
        });
    }
});
</script>
@endpush