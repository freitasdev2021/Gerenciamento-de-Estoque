@extends('layouts.appinterno')

@section('content')

<div class="col-sm-12 container p-3">
    <a href="{{ route('reposicoes.index') }}" class="btn btn-secondary mb-3">Voltar</a>

    <form action="{{ route('reposicoes.store') }}" method="POST">
        @csrf

        <h5>Nova Reposição de Estoque</h5>
        <hr>

        <div class="row">
            <div class="col-sm-6 select mb-2">
                <label>Produto</label>
                <select name="IDProduto" id="IDProduto" class="form-select" required>
                    <option value="">-- Selecione um Produto --</option>
                    @foreach($produtos as $p)
                        @php
                            $selected = old('IDProduto', $produtoSelecionado->IDProduto ?? '') == $p->IDProduto ? 'selected' : '';
                        @endphp
                        <option value="{{ $p->IDProduto }}" {{ $selected }}>
                            {{ $p->NMProduto }} 
                            (Mín: {{ $p->NUEstoqueMinimo ?? 0 }}
                            | Fornecedor: {{ $p->fornecedor->NMFornecedor ?? 'N/D' }})
                        </option>
                    @endforeach
                </select>
                @error('IDProduto')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-3 input mb-2">
                <label>Quantidade</label>
                <input type="number" name="QTCompra" class="form-control" min="1" value="{{ old('QTCompra', 1) }}" required>
                @error('QTCompra')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-3 money mb-2">
                <label>Valor Unitário (R$)</label>
                <input type="text" name="VLUnitario" class="form-control money-input" value="{{ old('VLUnitario', '') }}" placeholder="Valor unitário da compra">
                @error('VLUnitario')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-sm-12">
                <button class="btn btn-success" type="submit">Registrar Reposição</button>
            </div>
        </div>
    </form>
</div>
@endsection