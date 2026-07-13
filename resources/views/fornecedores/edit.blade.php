@extends('layouts.appinterno')

@section('content')

<div class="col-sm-12 container p-3">
    <a href="{{ route('fornecedores.index') }}" class="btn btn-secondary mb-3">Voltar</a>

    <form action="{{ route('fornecedores.update', $fornecedor->IDFornecedor) }}" method="POST">
        @csrf
        @method('PUT')
        <h5>Dados do Fornecedor</h5>
        <hr>
        <div class="row">
            <div class="col-sm-6 input mb-2">
                <label>Nome do Fornecedor</label>
                <input type="text" name="nomeFornecedor" class="form-control" minlength="1" maxlength="100" value="{{ old('nomeFornecedor', $fornecedor->NMFornecedor) }}" required>
                @error('nomeFornecedor')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-6 select mb-2">
                <label>Filial</label>
                <select name="IDFilial" class="form-select" required>
                    <option value="">-- Selecione uma Filial --</option>
                    @foreach($filiais as $f)
                        <option value="{{ $f->IDFilial }}" {{ old('IDFilial', $fornecedor->IDFilial) == $f->IDFilial ? 'selected' : '' }}>
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
                <input type="email" name="emailFornecedor" class="form-control" maxlength="150" value="{{ old('emailFornecedor', $fornecedor->DSEmailFornecedor) }}">
                @error('emailFornecedor')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-4 input mb-2">
                <label>Telefone</label>
                <input type="text" name="telefoneFornecedor" class="form-control" maxlength="20" value="{{ old('telefoneFornecedor', $fornecedor->DSTelefoneFornecedor) }}">
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
                <input type="text" name="cepFornecedor" class="form-control" maxlength="10" value="{{ old('cepFornecedor', $endereco['cep'] ?? '') }}">
                @error('cepFornecedor')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-2 input mb-2">
                <label>UF</label>
                <input type="text" name="ufFornecedor" class="form-control" maxlength="2" value="{{ old('ufFornecedor', $endereco['uf'] ?? '') }}">
                @error('ufFornecedor')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-4 input mb-2">
                <label>Cidade</label>
                <input type="text" name="cidadeFornecedor" class="form-control" maxlength="100" value="{{ old('cidadeFornecedor', $endereco['cidade'] ?? '') }}">
                @error('cidadeFornecedor')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-3 input mb-2">
                <label>Bairro</label>
                <input type="text" name="bairroFornecedor" class="form-control" maxlength="100" value="{{ old('bairroFornecedor', $endereco['bairro'] ?? '') }}">
                @error('bairroFornecedor')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6 input mb-2">
                <label>Rua</label>
                <input type="text" name="ruaFornecedor" class="form-control" maxlength="150" value="{{ old('ruaFornecedor', $endereco['rua'] ?? '') }}">
                @error('ruaFornecedor')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-3 input mb-2">
                <label>Número</label>
                <input type="text" name="numeroFornecedor" class="form-control" maxlength="10" value="{{ old('numeroFornecedor', $endereco['numero'] ?? '') }}">
                @error('numeroFornecedor')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-3 input mb-2">
                <label>Complemento</label>
                <input type="text" name="complementoFornecedor" class="form-control" maxlength="100" value="{{ old('complementoFornecedor', $endereco['complemento'] ?? '') }}">
                @error('complementoFornecedor')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-sm-12">
                <button class="btn btn-success" type="submit">Atualizar</button>
            </div>
        </div>
    </form>
</div>
@endsection