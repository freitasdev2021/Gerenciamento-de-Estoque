<?php
use App\Http\Controllers\geralController;
?>
@extends('layouts.appinterno')

@section('content')
<div class="col-sm-12 container p-3">
    <form action="{{ route('produtos.update', $produto->IDProduto) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-sm-4 input">
                <label>Nome</label>
                <input type="text" name="NMProduto" class="form-control" minlength="5" maxlength="50" value="{{ old('NMProduto', $produto->NMProduto) }}" required>
                @error('NMProduto')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-4 input">
                <label>Categoria</label>
                <select name="IDCategoria" class="form-control" required>
                    <option value="">Selecione</option>
                    @foreach($categorias as $cat)
                    <option value="{{ $cat->IDCategoria }}" {{ old('IDCategoria', $produto->IDCategoria) == $cat->IDCategoria ? 'selected' : '' }}>{{ $cat->DSCategoria }}</option>
                    @endforeach
                </select>
                @error('IDCategoria')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-4">
                <label>SKU / Código</label>
                <input type="text" name="DSCodigoProduto" class="form-control" minlength="5" maxlength="6" value="{{ old('DSCodigoProduto', $produto->DSCodigoProduto) }}" required>
                @error('DSCodigoProduto')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-4 money">
                <label>Valor(R$)</label>
                <input type="text" name="NUValorProduto" class="form-control money-input" minlength="1" maxlength="10" value="{{ old('NUValorProduto', geralController::trataValor($produto->NUValorProduto, 0)) }}" required>
                @error('NUValorProduto')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-4 input">
                <label>Vencimento (Opcional)</label>
                <input type="date" name="DTValidadeProduto" min="{{ date('Y-m-d') }}" class="form-control" value="{{ old('DTValidadeProduto', ($produto->DTValidadeProduto ? date('Y-m-d', strtotime($produto->DTValidadeProduto)) : '')) }}">
                @error('DTValidadeProduto')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-4 input">
                <label>Estoque</label>
                <input type="number" name="NUEstoqueProduto" class="form-control" min="0" value="{{ old('NUEstoqueProduto', $produto->NUEstoqueProduto) }}" required>
                @error('NUEstoqueProduto')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-4 input">
                <label>Estoque Mínimo</label>
                <input type="number" name="NUEstoqueMinimo" class="form-control" min="0" value="{{ old('NUEstoqueMinimo', $produto->NUEstoqueMinimo) }}">
                @error('NUEstoqueMinimo')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-4 input">
                <label>Custo (R$)</label>
                <input type="text" name="NUCustoProduto" class="form-control money-input" value="{{ old('NUCustoProduto', geralController::trataValor($produto->NUCustoProduto, 0)) }}">
                @error('NUCustoProduto')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-4 input">
                <label>Unidade</label>
                <select name="DSUnidadeProduto" class="form-control">
                    <option value="UN" {{ old('DSUnidadeProduto', $produto->DSUnidadeProduto) == 'UN' ? 'selected' : '' }}>UN</option>
                    <option value="KG" {{ old('DSUnidadeProduto', $produto->DSUnidadeProduto) == 'KG' ? 'selected' : '' }}>KG</option>
                    <option value="LT" {{ old('DSUnidadeProduto', $produto->DSUnidadeProduto) == 'LT' ? 'selected' : '' }}>LT</option>
                    <option value="CX" {{ old('DSUnidadeProduto', $produto->DSUnidadeProduto) == 'CX' ? 'selected' : '' }}>CX</option>
                    <option value="PC" {{ old('DSUnidadeProduto', $produto->DSUnidadeProduto) == 'PC' ? 'selected' : '' }}>PC</option>
                </select>
                @error('DSUnidadeProduto')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-4 input">
                <label>Fornecedor</label>
                <select name="IDFornecedor" class="form-control">
                    <option value="">Selecione</option>
                    @if(isset($fornecedores))
                        @foreach($fornecedores as $forn)
                        <option value="{{ $forn->IDFornecedor }}" {{ old('IDFornecedor', $produto->IDFornecedor) == $forn->IDFornecedor ? 'selected' : '' }}>{{ $forn->NMFornecedor }}</option>
                        @endforeach
                    @endif
                </select>
                @error('IDFornecedor')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-sm-12 textarea">
            <label>Descrição / Garantia (Opcional)</label>
            <textarea class="form-control" rows="5" name="DSGarantiaProduto" minlength="5" maxlength="250">{{ old('DSGarantiaProduto', $produto->DSGarantiaProduto) }}</textarea>
            @error('DSGarantiaProduto')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="row col-sm-12">
            <div class="col-sm-4 input">
                <label>Foto</label>
                <input type="file" id="fotoProduto" name="imagem_produto" class="form-control" accept="image/png, image/jpeg, image/jpg">
            </div>
            <div class="col-sm-8 input" align="center">
                <br>
                @if(!empty($produto->DSImagemProduto))
                    <img src="{{ $produto->DSImagemProduto }}" width="500px" height="500px" id="imagemProduto">
                @endif
            </div>
        </div>
        <br>
        <div class="col-sm-12">
            <button class="btn btn-success" type="submit">Salvar</button>
        </div>
    </form>
</div>
@endsection