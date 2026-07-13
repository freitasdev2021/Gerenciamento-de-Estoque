@extends('layouts.appinterno')

@section('content')
<div class="col-sm-12 container p-3">
    <form action="{{ route('produtos.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-sm-4 input">
                <label>Nome</label>
                <input type="text" name="NMProduto" class="form-control" minlength="5" maxlength="50" value="{{ old('NMProduto') }}" required>
                @error('NMProduto')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-4 input">
                <label>Categoria</label>
                <select name="IDCategoria" class="form-control" required>
                    <option value="">Selecione</option>
                    @foreach($categorias as $cat)
                    <option value="{{ $cat->IDCategoria }}" {{ old('IDCategoria') == $cat->IDCategoria ? 'selected' : '' }}>{{ $cat->DSCategoria }}</option>
                    @endforeach
                </select>
                @error('IDCategoria')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-4">
                <label>SKU / Código</label>
                <input type="text" name="DSCodigoProduto" class="form-control" minlength="5" maxlength="6" value="{{ old('DSCodigoProduto') }}" required>
                @error('DSCodigoProduto')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-4 money">
                <label>Valor(R$)</label>
                <input type="text" name="NUValorProduto" class="form-control money-input" minlength="1" maxlength="10" value="{{ old('NUValorProduto') }}" required>
                @error('NUValorProduto')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-4 input">
                <label>Vencimento (Opcional)</label>
                <input type="date" name="DTValidadeProduto" min="{{ date('Y-m-d') }}" class="form-control" value="{{ old('DTValidadeProduto') }}">
                @error('DTValidadeProduto')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-4 input">
                <label>Estoque</label>
                <input type="number" name="NUEstoqueProduto" class="form-control" min="0" value="{{ old('NUEstoqueProduto') }}" required>
                @error('NUEstoqueProduto')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-4 input">
                <label>Estoque Mínimo</label>
                <input type="number" name="NUEstoqueMinimo" class="form-control" min="0" value="{{ old('NUEstoqueMinimo', 0) }}">
                @error('NUEstoqueMinimo')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-4 input">
                <label>Custo (R$)</label>
                <input type="text" name="NUCustoProduto" class="form-control money-input" value="{{ old('NUCustoProduto', '0') }}">
                @error('NUCustoProduto')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-4 input">
                <label>Unidade</label>
                <select name="DSUnidadeProduto" class="form-control">
                    <option value="UN" {{ old('DSUnidadeProduto') == 'UN' ? 'selected' : '' }}>UN</option>
                    <option value="KG" {{ old('DSUnidadeProduto') == 'KG' ? 'selected' : '' }}>KG</option>
                    <option value="LT" {{ old('DSUnidadeProduto') == 'LT' ? 'selected' : '' }}>LT</option>
                    <option value="CX" {{ old('DSUnidadeProduto') == 'CX' ? 'selected' : '' }}>CX</option>
                    <option value="PC" {{ old('DSUnidadeProduto') == 'PC' ? 'selected' : '' }}>PC</option>
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
                        <option value="{{ $forn->IDFornecedor }}" {{ old('IDFornecedor') == $forn->IDFornecedor ? 'selected' : '' }}>{{ $forn->NMFornecedor }}</option>
                        @endforeach
                    @endif
                </select>
                @error('IDFornecedor')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-sm-12 textarea">
            <label>Descrição do Produto (Opcional)</label>
            <textarea class="form-control" rows="5" name="DSGarantiaProduto" minlength="5" maxlength="250">{{ old('DSGarantiaProduto') }}</textarea>
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
                <img src="" width="500px" height="500px" id="imagemProduto" style="display:none;">
            </div>
        </div>
        <br>
        <div class="col-sm-12">
            <button class="btn btn-success" type="submit">Salvar</button>
        </div>
    </form>
</div>
@endsection