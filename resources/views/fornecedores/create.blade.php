@extends('layouts.appinterno')

@section('content')

<div class="col-sm-12 container p-3">
    <a href="{{ route('fornecedores.index') }}" class="btn btn-secondary mb-3">Voltar</a>

    <form action="{{ route('fornecedores.store') }}" method="POST">
        @csrf
        <h5>Dados do Fornecedor</h5>
        <hr>
        <div class="row">
            <div class="col-sm-6 input mb-2">
                <label>Nome do Fornecedor</label>
                <input type="text" name="nomeFornecedor" class="form-control" minlength="1" maxlength="100" value="{{ old('nomeFornecedor') }}" required>
                @error('nomeFornecedor')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-6 select mb-2">
                <label>Filial</label>
                <select name="IDFilial" class="form-select" required>
                    <option value="">-- Selecione uma Filial --</option>
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
            <div class="col-sm-4 input mb-2">
                <label>Email</label>
                <input type="email" name="emailFornecedor" class="form-control" maxlength="150" value="{{ old('emailFornecedor') }}">
                @error('emailFornecedor')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-4 input mb-2">
                <label>Telefone</label>
                <input type="text" name="telefoneFornecedor" class="form-control" maxlength="20" value="{{ old('telefoneFornecedor') }}">
                @error('telefoneFornecedor')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <h5 class="mt-4">Endereço</h5>
        <hr>
        <div class="row">
            <div class="col-sm-3 input mb-2">
                <label>CEP</label>
                <input type="text" name="cepFornecedor" class="form-control" maxlength="10" value="{{ old('cepFornecedor') }}">
                @error('cepFornecedor')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-2 input mb-2">
                <label>UF</label>
                <input type="text" name="ufFornecedor" class="form-control" maxlength="2" value="{{ old('ufFornecedor') }}">
                @error('ufFornecedor')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-4 input mb-2">
                <label>Cidade</label>
                <input type="text" name="cidadeFornecedor" class="form-control" maxlength="100" value="{{ old('cidadeFornecedor') }}">
                @error('cidadeFornecedor')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-3 input mb-2">
                <label>Bairro</label>
                <input type="text" name="bairroFornecedor" class="form-control" maxlength="100" value="{{ old('bairroFornecedor') }}">
                @error('bairroFornecedor')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6 input mb-2">
                <label>Rua</label>
                <input type="text" name="ruaFornecedor" class="form-control" maxlength="150" value="{{ old('ruaFornecedor') }}">
                @error('ruaFornecedor')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-3 input mb-2">
                <label>Número</label>
                <input type="text" name="numeroFornecedor" class="form-control" maxlength="10" value="{{ old('numeroFornecedor') }}">
                @error('numeroFornecedor')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-3 input mb-2">
                <label>Complemento</label>
                <input type="text" name="complementoFornecedor" class="form-control" maxlength="100" value="{{ old('complementoFornecedor') }}">
                @error('complementoFornecedor')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-sm-12">
                <button class="btn btn-success" type="submit">Cadastrar</button>
            </div>
        </div>
    </form>
</div>
@endsection