<?php
use App\Http\Controllers\geralController;
?>
@extends('layouts.appinterno')

@section('content')
<div class="col-sm-12">
    <a href="{{ route('produtos.index') }}" class="btn btn-success">Realizar Venda</a>
</div>

<hr>

<div class="col-sm-12">
    <table class="table table-bordered text-center tabela">
        <thead>
            <tr>
                <th>Produto</th>
                <th>Cliente</th>
                <th>Qtd</th>
                <th>Valor</th>
                <th>Pagamento</th>
                <th>Promoção</th>
                <th>Data</th>
                <th>Opções</th>
            </tr>
        </thead>
        <tbody>
            @forelse($vendas as $v)
            <tr>
                <td>{{ $v->NMProduto }}</td>
                <td>{{ $v->NMCliente }}</td>
                <td>{{ $v->NUUnidadesVendidas }}</td>
                <td>{{ geralController::trataValor($v->VLVenda, 0) }}</td>
                <td>{{ $v->NMPagamento }}</td>
                <td>{{ $v->NMPromo ?? '--' }}</td>
                <td>{{ \Carbon\Carbon::parse($v->DTVenda)->format('d/m/Y H:i') }}</td>
                <td>
                    <form action="{{ route('vendas.destroy', $v->IDVenda) }}" method="POST" style="display:inline;" onsubmit="return confirm('Deseja cancelar esta venda?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fa-solid fa-ban"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8">Nenhuma venda registrada.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection