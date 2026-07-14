@extends('layouts.appinterno')

@section('content')

<div class="col-sm-12 container p-3">
    <a href="{{ route('vendas.index') }}" class="btn btn-secondary mb-3">Voltar</a>

    <h5>Nova Venda</h5>
    <hr>

    <form action="{{ route('vendas.store') }}" method="POST">
        @csrf

        <div class="row">
            <div class="col-sm-6 select mb-2">
                <label>Produto</label>
                <select name="IDProduto" id="IDProduto" class="form-select" required>
                    <option value="">-- Selecione um Produto --</option>
                    @if($produto)
                        <option value="{{ $produto->IDProduto }}" selected>
                            {{ $produto->NMProduto }} | 
                            R$ {{ number_format($produto->NUValorProduto, 2, ',', '.') }}
                        </option>
                    @endif
                </select>
                @error('IDProduto')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-3 input mb-2">
                <label>Quantidade</label>
                <input type="number" name="NUUnidadesVendidas" class="form-control" min="1" value="{{ old('NUUnidadesVendidas', 1) }}" required>
                @error('NUUnidadesVendidas')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6 select mb-2">
                <label>Filial</label>
                <select name="IDFilial" class="form-select" required>
                    <option value="">-- Selecione a Filial --</option>
                    @foreach($filiais as $f)
                        <option value="{{ $f->IDFilial }}" {{ old('IDFilial') == $f->IDFilial ? 'selected' : '' }}>
                            {{ $f->NMFilial }}
                        </option>
                    @endforeach
                </select>
                @error('IDFilial')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6 select mb-2">
                <label>Cliente (Não Obrigatório)</label>
                <select name="IDCliente" class="form-select">
                    <option value="">-- Identifique um Cliente --</option>
                    @foreach($clientes as $c)
                        <option value="{{ $c->IDCliente }}" {{ old('IDCliente') == $c->IDCliente ? 'selected' : '' }}>
                            {{ $c->NMCliente }}
                        </option>
                    @endforeach
                </select>
                @error('IDCliente')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-6 select mb-2">
                <label>Forma de Pagamento</label>
                <select name="IDPagamento" class="form-select" required>
                    <option value="">-- Selecione o Pagamento --</option>
                    @foreach($pagamentos as $pg)
                        <option value="{{ $pg->IDPagamento }}" {{ old('IDPagamento') == $pg->IDPagamento ? 'selected' : '' }}>
                            {{ $pg->NMPagamento }} ({{ $pg->DSMetodo }})
                        </option>
                    @endforeach
                </select>
                @error('IDPagamento')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-sm-12">
                <button class="btn btn-success" type="submit">Finalizar Venda</button>
            </div>
        </div>
    </form>

    @if($produto)
    <div class="mt-5">
        <h5>Histórico de Vendas - {{ $produto->NMProduto }}</h5>
        <hr>
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Data</th>
                        <th>Qtd</th>
                        <th>Valor (R$)</th>
                        <th>Cliente</th>
                        <th>Pagamento</th>
                        <th>Vendedor</th>
                        <th width="180">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vendasDoProduto as $v)
                    <tr>
                        <td>{{ $v->IDVenda }}</td>
                        <td>{{ \Carbon\Carbon::parse($v->DTVenda)->format('d/m/Y H:i') }}</td>
                        <td>{{ $v->NUUnidadesVendidas }}</td>
                        <td>R$ {{ number_format($v->VLVenda, 2, ',', '.') }}</td>
                        <td>{{ $v->NMCliente ?? '-' }}</td>
                        <td>{{ $v->NMPagamento }}</td>
                        <td>{{ $v->NMColaborador ?? '-' }}</td>
                        <td class="text-center">
                            <a href="{{ route('cupons.imprimir', $v->IDVenda) }}" 
                               class="btn btn-sm btn-outline-primary" 
                               target="_blank"
                               title="Imprimir Cupom">
                                <i class="bi bi-printer"></i> Cupom
                            </a>
                            <form action="{{ route('vendas.destroy', $v->IDVenda) }}" 
                                  method="POST" 
                                  class="d-inline"
                                  onsubmit="return confirm('Tem certeza que deseja cancelar esta venda?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="btn btn-sm btn-outline-danger" 
                                        title="Cancelar Venda">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @elseif($produto)
    <div class="mt-5">
        <h5>Histórico de Vendas - {{ $produto->NMProduto }}</h5>
        <hr>
        <div class="alert alert-info">
            Nenhuma venda registrada para este produto.
        </div>
    </div>
    @endif
</div>
@endsection