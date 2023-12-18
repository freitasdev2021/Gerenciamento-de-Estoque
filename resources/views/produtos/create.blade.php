@extends('layouts.appinterno')

@section('content')
<div class="col-sm-12 container p-3">
    <form id="formProdutos" class="form-controls">
        @csrf
        @method('POST')
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <div class="row">
            <div class="col-sm-4 input">
                <label>Nome</label>
                <input type="text" name="nomeProduto" class="form-control" minlength="5" maxlength="50">
                <div class="error-input text-danger">
                Preenchimento incorreto!
                </div>
            </div>
            <div class="col-sm-4 input">
                <label>Categoria</label>
                <select name="categoriaProduto" class="form-control">
                    <option value="">Selecione</option>
                    @foreach($categorias as $cat)
                    <option value="{{$cat['id']}}">{{$cat['NMCategoria']}}</option>
                    @endforeach
                </select>
                <div class="error-input text-danger">
                Preenchimento incorreto!
                </div>
            </div>
            <div class="col-sm-4">
                <label>SKU</label>
                <input type="number" name="skuProduto" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" minlength="5" maxlength="6">
                <div class="error-input text-danger">
                Preenchimento incorreto!
                </div>
            </div>
            <div class="col-sm-4 money">
                <label>Valor(R$)</label>
                <input type="text" name="valorProduto" class="form-control" minlength="1" maxlength="10">
                <div class="error-input text-danger">
                    P.incorreto!
                </div>
            </div>
            <div class="col-sm-4 input">
                <label>Vencimento (Opcional)</label>
                <input type="date" name="vencimentoProduto" min="{{date('Y-m-d')}}" class="form-control norequire" minlength="9">
                <div class="error-input text-danger">
                Preenchimento incorreto!
                </div>
            </div>
            <div class="col-sm-4 input">
                <label>Estoque</label>
                <input type="number" name="estoqueProduto" class="form-control" minlength="1">
                <div class="error-input text-danger">
                Preenchimento incorreto!
                </div>
            </div>
        </div>
        <div class="col-sm-12 textarea">
            <label>Descrição do Produto(Opcional)</label>
            <textarea class="form-control norequire" rows="5" name="descricaoProduto" minlength="5" maxlength="250"></textarea>
            <div class="error-input text-danger">
                Preenchimento incorreto!
            </div>
        </div>
        <div class="row col-sm-12">
            <div class="col-sm-4 input">
                <label>Foto</label>
                <input type="file" id="fotoProduto" name="ftprod" class="form-control" minlength="1" accept="image/png, image/jpeg, image/jpg">
                <div class="error-input text-danger">
                Preenchimento incorreto!
                </div>
            </div>
            <div class="col-sm-8 input" align="center">
                <br>
                <img src="" width="500px" height="500px" id="imagemProduto">
            </div>
        </div>
        <br>
        <div class="col-sm-12">
            <button class="btn btn-success" type="submit">Salvar</button>
        </div>
    </form>
</div>
@endsection