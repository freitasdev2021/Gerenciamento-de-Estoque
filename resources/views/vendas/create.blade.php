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
                <label>Cliente (Não Obrigatório)</label>
                <select name="IDCliente" class="form-select" required>
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
</div>
@endsection