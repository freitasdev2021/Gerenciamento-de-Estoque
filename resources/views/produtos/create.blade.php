@extends('layouts.appinterno')

@section('content')

<div class="col-sm-12 container p-3">
    <a href="{{ route('produtos.index') }}" class="btn btn-secondary mb-3">Voltar</a>

    @php
        $editando = isset($produto) && $produto;
        $action = $editando ? route('produtos.update', $produto->IDProduto) : route('produtos.store');
    @endphp

    <form action="{{ $action }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if($editando)
            @method('PUT')
        @endif

        <h5>{{ $editando ? 'Editar Produto' : 'Novo Produto' }}</h5>
        <hr>

        <div class="row">
            <div class="col-sm-4 input mb-2">
                <label>Nome</label>
                <input type="text" name="NMProduto" class="form-control" minlength="5" maxlength="250" value="{{ old('NMProduto', $produto->NMProduto ?? '') }}" required>
                @error('NMProduto')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-4 select mb-2">
                <label>Categoria</label>
                <select name="IDCategoria" class="form-select" required>
                    <option value="">Selecione</option>
                    @foreach($categorias as $cat)
                    <option value="{{ $cat->IDCategoria }}" {{ old('IDCategoria', $produto->IDCategoria ?? '') == $cat->IDCategoria ? 'selected' : '' }}>{{ $cat->DSCategoria }}</option>
                    @endforeach
                </select>
                @error('IDCategoria')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-4 input mb-2">
                <label>SKU / Código</label>
                <input type="text" name="DSCodigoProduto" class="form-control" minlength="5" maxlength="6" value="{{ old('DSCodigoProduto', $produto->DSCodigoProduto ?? '') }}" required>
                @error('DSCodigoProduto')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-4 money mb-2">
                <label>Valor (R$)</label>
                <input type="text" name="NUValorProduto" class="form-control money-input" minlength="1" maxlength="10" value="{{ old('NUValorProduto', isset($produto) ? \App\Http\Controllers\geralController::trataValor($produto->NUValorProduto, 0) : '') }}" required>
                @error('NUValorProduto')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-4 input mb-2">
                <label>Estoque Mínimo</label>
                <input type="number" name="NUEstoqueMinimo" class="form-control" min="0" value="{{ old('NUEstoqueMinimo', $produto->NUEstoqueMinimo ?? 0) }}">
                @error('NUEstoqueMinimo')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-4 input mb-2">
                <label>Vencimento (Opcional)</label>
                <input type="date" name="DTValidadeProduto" min="{{ date('Y-m-d') }}" class="form-control" value="{{ old('DTValidadeProduto', isset($produto) && $produto->DTValidadeProduto ? date('Y-m-d', strtotime($produto->DTValidadeProduto)) : '') }}">
                @error('DTValidadeProduto')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-4 select mb-2">
                <label>Unidade</label>
                <select name="DSUnidadeProduto" class="form-select">
                    <option value="UN" {{ old('DSUnidadeProduto', $produto->DSUnidadeProduto ?? '') == 'UN' ? 'selected' : '' }}>UN</option>
                    <option value="KG" {{ old('DSUnidadeProduto', $produto->DSUnidadeProduto ?? '') == 'KG' ? 'selected' : '' }}>KG</option>
                    <option value="LT" {{ old('DSUnidadeProduto', $produto->DSUnidadeProduto ?? '') == 'LT' ? 'selected' : '' }}>LT</option>
                    <option value="CX" {{ old('DSUnidadeProduto', $produto->DSUnidadeProduto ?? '') == 'CX' ? 'selected' : '' }}>CX</option>
                    <option value="PC" {{ old('DSUnidadeProduto', $produto->DSUnidadeProduto ?? '') == 'PC' ? 'selected' : '' }}>PC</option>
                </select>
                @error('DSUnidadeProduto')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-4 select mb-2">
                <label>Fornecedor</label>
                <select name="IDFornecedor" class="form-select">
                    <option value="">Selecione</option>
                    @if(isset($fornecedores))
                        @foreach($fornecedores as $forn)
                        <option value="{{ $forn->IDFornecedor }}" {{ old('IDFornecedor', $produto->IDFornecedor ?? '') == $forn->IDFornecedor ? 'selected' : '' }}>{{ $forn->NMFornecedor }}</option>
                        @endforeach
                    @endif
                </select>
                @error('IDFornecedor')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-sm-4 input mb-2">
                <label>Foto</label>
                <input type="file" id="fotoProduto" name="imagem_produto" class="form-control" accept="image/png, image/jpeg, image/jpg">
            </div>
            <div class="col-sm-8 input" align="center">
                @if($editando && !empty($produto->DSImagemProduto))
                    <img src="{{ $produto->DSImagemProduto }}" width="500px" height="500px" id="imagemProduto">
                @else
                    <img src="" width="500px" height="500px" id="imagemProduto" style="display:none;">
                @endif
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