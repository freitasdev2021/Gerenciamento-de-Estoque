<?php
use App\Http\Controllers\geralController;
?>
@extends('layouts.appinterno')

@section('content')
<div class="col-sm-12">
    <a href="{{ route('produtos.create') }}" class="btn btn-success">Adicionar</a>
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
                <th>Descrição</th>
                <th>SKU</th>
                <th>Cadastro</th>
                <th>Opções</th>
            </tr>
        </thead>
        <tbody>
            @foreach($produtos as $p)
            <tr>
                <td>{{ $p->NMProduto }}</td>
                <td>{{ $p->NUEstoqueProduto }}</td>
                <td>{{ ($p->DTVencimento == '0000-00-00 00:00:00' ? 'Não Tem' : geralController::data($p->DTVencimento, 'd/m/Y')) }}</td>
                <td>{{ geralController::trataValor($p->NUValorProduto, 0) }}</td>
                <td>{{ $p->DSProduto }}</td>
                <td>{{ $p->SKUProduto }}</td>
                <td>{{ geralController::data($p->DTCadastro, 'd/m/Y H:i') }}</td>
                <td>
                    <form action="{{ route('produtos.destroy', $p->IDProduto) }}" method="POST" style="display:inline;" onsubmit="return confirm('Deseja Excluir Esse Produto?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                    <a class="btn btn-primary" href="{{ route('produtos.edit', $p->IDProduto) }}">
                        <i class="fa-solid fa-pen"></i>
                    </a>
                    <a class="btn btn-success" href="{{ route('vendas.create', ['produto' => $p->IDProduto]) }}" title="Vender">
                        <i class="fa-solid fa-cart-shopping"></i>
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection