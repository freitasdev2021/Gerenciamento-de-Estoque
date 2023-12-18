<?php
use App\Http\Controllers\geralController;
?>
@extends('layouts.appinterno')

@section('content')
<div class="col-sm-12">
    <a href="{{route('produtos.create')}}" class="btn btn-success">Adicionar</a>
</div>
<hr>
<div class="col-sm-12">
    <table class="table table-bordered text-center tabela">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Estoque</th>
                <th>Vencimento</th>
                <th>Valor</th>
                <th>SKU</th>
                <th>Opções</th>
            </tr>
        </thead>
        <tbody>
            @foreach($produtos as $p)
            <tr>
                <td>{{$p->NMProduto}}</td>
                <td>{{$p->NUEstoqueProduto}}</td>
                <td>{{($p->DTVencimento == '0000-00-00 00:00:00' ? 'Não Tem' : geralController::data($p->DTVencimento,'d/m/Y') )}}</td>
                <td>{{geralController::trataValor($p->VLProduto,0)}}</td>
                <td>{{$p->SKUProduto}}</td>
                <td>
                    <button class="btn btn-danger btn-excluir-produto" data-id="{{$p->IDProduto}}" data-csrf="{{ csrf_token() }}">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                    <a class="btn btn-primary" href="{{route('produtos.edit',$p->IDProduto)}}">
                        <i class="fa-solid fa-pen"></i>
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>    
    </table>
</div>
@endsection