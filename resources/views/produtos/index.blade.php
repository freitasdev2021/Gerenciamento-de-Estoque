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
                <th>Categoria</th>
                <th>Fornecedor</th>
                <th>Vencimento</th>
                <th>Valor</th>
                <th>SKU</th>
                <th>Opções</th>
            </tr>
        </thead>
        <tbody>
            @foreach($produtos as $p)
            <tr>
                <td>{{ $p->NMProduto }}</td>
                <td>{{ $p->categoria->DSCategoria ?? 'N/D' }}</td>
                <td>{{ $p->fornecedor->NMFornecedor ?? 'N/D' }}</td>
                <td>{{ ($p->DTValidadeProduto ? \Carbon\Carbon::parse($p->DTValidadeProduto)->format('d/m/Y') : 'Não Tem') }}</td>
                <td>{{ geralController::trataValor($p->NUValorProduto, 0) }}</td>
                <td>{{ $p->DSCodigoProduto }}</td>
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