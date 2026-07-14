<?php
use App\Http\Controllers\geralController;
?>
@extends('layouts.appinterno')

@section('content')
<div class="col-sm-12 d-flex justify-content-between align-items-center flex-wrap">
    <a href="{{ route('reposicoes.create') }}" class="btn btn-success mb-2">Nova Reposição</a>

    <form method="GET" action="{{ route('reposicoes.index') }}" class="d-flex align-items-center gap-2 mb-2">
        <label for="filial" class="form-label mb-0 me-2">Filtrar por Filial:</label>
        <select name="filial" id="filial" class="form-select" style="width: auto;" onchange="this.form.submit()">
            <option value="">-- Todas as Filiais --</option>
            @foreach($filiais as $f)
                <option value="{{ $f->IDFilial }}" {{ $filialSelecionada == $f->IDFilial ? 'selected' : '' }}>
                    {{ $f->NMFilial }}
                </option>
            @endforeach
        </select>
    </form>
</div>

<hr>

<div class="col-sm-12">
    <table class="table table-bordered text-center tabela">
        <thead>
            <tr>
                <th>Lote</th>
                <th>Produto</th>
                <th>SKU</th>
                <th>Fornecedor</th>
                <th>Categoria</th>
                <th>Qtd</th>
                <th>Vl. Unitário</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reposicoes as $r)
            <tr>
                <td>{{ $r->IDLote }}</td>
                <td>{{ $r->NMProduto }}</td>
                <td>{{ $r->DSCodigoProduto }}</td>
                <td>{{ $r->NMFornecedor }}</td>
                <td>{{ $r->DSCategoria }}</td>
                <td>{{ $r->QTCompra }}</td>
                <td>{{ geralController::trataValor($r->VLUnitario, 0) }}</td>
                <td>{{ \Carbon\Carbon::parse($r->DTReposicao)->format('d/m/Y H:i') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8">Nenhuma reposição registrada.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection