<?php
use App\Http\Controllers\geralController;
?>
@extends('layouts.appinterno')

@section('content')
<div class="col-sm-12 container p-3">
    <form id="formMovimentacao" class="form-controls">
    <meta name="csrf-token" content="{{ csrf_token() }}">
        <div class="row">
            <div class="col-sm-4 input">
                <label>Quantidade</label>
                <input type="text" name="quantidadeProduto" class="form-control" minlength="1">
                <div class="error-input text-danger">
                    Preenchimento incorreto!
                </div>
            </div>
            <div class="col-sm-4 input">
                <label>Tipo de Movimentação</label>
                <select name="tipoMovimentacao" class="form-control">
                    <option value="">Selecione</option>
                    <option value="VEN">Venda</option>
                    <option value="REP">Reposição</option>
                </select>
                <div class="error-input text-danger">
                Preenchimento incorreto!
                </div>
            </div>
            <div class="col-sm-4 input">
                <label>Produto</label>
                <select name="produtoMovimentacao" class="form-control">
                    <option value="">Selecione</option>
                    @foreach($estoque as $es)
                    <option value="{{$es['id']}}">{{$es['NMProduto'] }} - R$ {{geralController::trataValor($es['VLProduto'],0) }} - {{$es['NUEstoqueProduto'] }} Unidades Disponiveis</option>
                    @endforeach
                </select>
                <div class="error-input text-danger">
                Preenchimento incorreto!
                </div>
            </div>
            <div class="col-sm-4 money">
                <label>Valor(R$)</label>
                <input type="text" name="valorMovimentacao" class="form-control" minlength="1" maxlength="10">
                <div class="error-input text-danger">
                    P.incorreto!
                </div>
            </div>
        </div>
        <div class="col-sm-12">
            <br>
            <button class="btn btn-success" type="submit">Salvar</button>
        </div>
        </div>
        <br>
    </form>
</div>
@endsection