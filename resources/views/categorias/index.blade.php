<?php
use App\Http\Controllers\geralController;
?>
@extends('layouts.appinterno')

@section('content')
<div class="col-sm-12">
    <a href="{{ route('categorias.create') }}" class="btn btn-success">Adicionar</a>
</div>
<hr>
<div class="col-sm-12">
    <table class="table table-bordered text-center tabela">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Estoque</th>
                <th>Valor Total</th>
                <th>Opções</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categorias as $cat)
            <tr>
                <td>{{ $cat->DSCategoria }}</td>
                <td>{{ $cat->QTEstoque }}</td>
                <td>{{ geralController::trataValor($cat->VLInvestido, 0) }}</td>
                <td>
                    <form action="{{ route('categorias.destroy', $cat->IDCategoria) }}" method="POST" style="display:inline;" onsubmit="return confirm('Deseja Excluir Essa Categoria?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                    <a class="btn btn-primary" href="{{ route('categorias.edit', $cat->IDCategoria) }}">
                        <i class="fa-solid fa-pen"></i>
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection