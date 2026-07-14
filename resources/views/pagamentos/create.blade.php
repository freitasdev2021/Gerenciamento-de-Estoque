@extends('layouts.appinterno')

@section('content')

<div class="col-sm-12 container p-3">
    <a href="{{ route('pagamentos.index') }}" class="btn btn-secondary mb-3">Voltar</a>

    @php
        $editando = isset($pagamento) && $pagamento;
        $action = $editando ? route('pagamentos.update', $pagamento->IDPagamento) : route('pagamentos.store');
        $method = $editando ? 'PUT' : 'POST';
    @endphp

    <form action="{{ $action }}" method="POST">
        @csrf
        @if($editando)
            @method('PUT')
        @endif

        <h5>{{ $editando ? 'Editar Pagamento' : 'Novo Pagamento' }}</h5>
        <hr>

        <div class="row">
            <div class="col-sm-4 input mb-2">
                <label>Nome do Pagamento</label>
                <input type="text" name="nomeMetodo" class="form-control" minlength="1" maxlength="100" value="{{ old('nomeMetodo', $pagamento->NMPagamento ?? '') }}" required>
                @error('nomeMetodo')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-4 select mb-2">
                <label>Método</label>
                <select name="metodoMetodo" class="form-select" required>
                    <option value="">-- Selecione --</option>
                    <option value="Dinheiro" {{ old('metodoMetodo', $pagamento->DSMetodo ?? '') == 'Dinheiro' ? 'selected' : '' }}>Dinheiro</option>
                    <option value="Cartão de Crédito" {{ old('metodoMetodo', $pagamento->DSMetodo ?? '') == 'Cartão de Crédito' ? 'selected' : '' }}>Cartão de Crédito</option>
                    <option value="Cartão de Débito" {{ old('metodoMetodo', $pagamento->DSMetodo ?? '') == 'Cartão de Débito' ? 'selected' : '' }}>Cartão de Débito</option>
                    <option value="PIX" {{ old('metodoMetodo', $pagamento->DSMetodo ?? '') == 'PIX' ? 'selected' : '' }}>PIX</option>
                    <option value="Boleto" {{ old('metodoMetodo', $pagamento->DSMetodo ?? '') == 'Boleto' ? 'selected' : '' }}>Boleto</option>
                    <option value="Vale" {{ old('metodoMetodo', $pagamento->DSMetodo ?? '') == 'Vale' ? 'selected' : '' }}>Vale</option>
                    <option value="Outros" {{ old('metodoMetodo', $pagamento->DSMetodo ?? '') == 'Outros' ? 'selected' : '' }}>Outros</option>
                </select>
                @error('metodoMetodo')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-2 input mb-2">
                <label>Parcelas</label>
                <input type="number" name="parcelasMetodo" class="form-control" min="1" max="99" value="{{ old('parcelasMetodo', $pagamento->QTParcelas ?? 1) }}" required>
                @error('parcelasMetodo')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-2 input mb-2">
                <label>Juros (%)</label>
                <input type="number" name="jurosMetodo" class="form-control" step="0.01" min="0" value="{{ old('jurosMetodo', $pagamento->NUJuros ?? 0) }}">
                @error('jurosMetodo')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-sm-3 select mb-2">
                <label>Tipo de Desconto</label>
                <select name="tipoMetodo" id="tipoMetodo" class="form-select">
                    <option value="">-- Selecione --</option>
                    <option value="1" {{ old('tipoMetodo', $pagamento->TPDesconto ?? '') == '1' ? 'selected' : '' }}>R$ (Valor Fixo)</option>
                    <option value="%" {{ old('tipoMetodo', $pagamento->TPDesconto ?? '') == '%' ? 'selected' : '' }}>% (Porcentagem)</option>
                </select>
                @error('tipoMetodo')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-3 input mb-2">
                <label>Desconto</label>
                <input type="text" name="descontoMetodo" class="form-control" value="{{ old('descontoMetodo', $pagamento->QTDesconto ?? 0) }}" required>
                @error('descontoMetodo')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-sm-12">
                <button class="btn btn-success" type="submit">{{ $editando ? 'Atualizar' : 'Cadastrar' }}</button>
            </div>
        </div>
    </form>
</div>
@endsection