<?php
use App\Http\Controllers\geralController;
?>
@extends('layouts.appinterno')

@section('content')
<div class="header">
    <a href="{{route('movimentacoes.create')}}" class="btn btn-success">Adicionar</a>
</div>
<hr>
<div class="col-sm-12">
    <table class="table table-bordered text-center tabela">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Quantidade</th>
                <th>Valor</th>
                <th>Tipo de Movimentação</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movimentacoes as $mv)
            <tr>
                <td>{{$mv->NMProduto}}</td>
                <td>{{$mv->QTMovimentacao}}</td>
                <td>{{geralController::trataValor($mv->VLMovimentacao,0)}}</td>
                <td>{{($mv->TPMovimentacao == 'REP') ? 'Reposição' : 'Venda' }}</td>
            </tr>
            @endforeach
        </tbody>    
    </table>
</div>
@endsection