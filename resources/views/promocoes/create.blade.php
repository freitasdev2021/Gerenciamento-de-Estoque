@extends('layouts.appinterno')

@section('content')

<div class="col-sm-12 container p-3">
    <a href="{{ route('promocoes.index') }}" class="btn btn-secondary mb-3">Voltar</a>

    @php
        $editando = isset($promocao) && $promocao;
        $action = $editando ? route('promocoes.update', $promocao->IDPromocao) : route('promocoes.store');
    @endphp

    <form action="{{ $action }}" method="POST">
        @csrf
        @if($editando)
            @method('PUT')
        @endif

        <h5>{{ $editando ? 'Editar Promoção' : 'Nova Promoção' }}</h5>
        <hr>

        <div class="row">
            <div class="col-sm-4 input mb-2">
                <label>Nome da Promoção</label>
                <input type="text" name="nomePromo" class="form-control" minlength="1" maxlength="100" value="{{ old('nomePromo', $promocao->NMPromo ?? '') }}" required>
                @error('nomePromo')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-4 select mb-2">
                <label>Filial</label>
                <select name="IDFilial" class="form-select" required>
                    <option value="">-- Selecione uma Filial --</option>
                    @foreach($filiais as $f)
                        <option value="{{ $f->IDFilial }}" {{ old('IDFilial', $promocao->IDFilial ?? '') == $f->IDFilial ? 'selected' : '' }}>
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
            <div class="col-sm-3 input mb-2">
                <label>Data de Início</label>
                <input type="date" name="inicioPromo" class="form-control" value="{{ old('inicioPromo', isset($promocao) ? \Carbon\Carbon::parse($promocao->DTInicioPromo)->format('Y-m-d') : '') }}" required>
                @error('inicioPromo')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-3 input mb-2">
                <label>Data de Término</label>
                <input type="date" name="fimPromo" class="form-control" value="{{ old('fimPromo', isset($promocao) ? \Carbon\Carbon::parse($promocao->DTTerminoPromo)->format('Y-m-d') : '') }}" required>
                @error('fimPromo')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-3 select mb-2">
                <label>Tipo de Desconto</label>
                <select name="tipoPromo" id="tipoPromo" class="form-select" required>
                    <option value="">-- Selecione --</option>
                    <option value="1" {{ old('tipoPromo', $promocao->TPDesconto ?? '') == '1' ? 'selected' : '' }}>R$ (Valor Fixo)</option>
                    <option value="%" {{ old('tipoPromo', $promocao->TPDesconto ?? '') == '%' ? 'selected' : '' }}>% (Porcentagem)</option>
                </select>
                @error('tipoPromo')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-3 input mb-2">
                <label>Desconto</label>
                <input type="text" name="descontoPromo" class="form-control" value="{{ old('descontoPromo', $promocao->NUDescontoPromo ?? 0) }}" required>
                @error('descontoPromo')
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