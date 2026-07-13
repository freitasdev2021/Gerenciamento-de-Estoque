<?php
use App\Http\Controllers\geralController;
?>
@extends('layouts.appinterno')

@section('content')
<div class="col-sm-12 d-flex justify-content-between align-items-center flex-wrap">
    <a href="{{ route('promocoes.create') }}" class="btn btn-success mb-2">Adicionar</a>

    <form method="GET" action="{{ route('promocoes.index') }}" class="d-flex align-items-center gap-2 mb-2">
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
                <th>Nome</th>
                <th>Filial</th>
                <th>Início</th>
                <th>Término</th>
                <th>Desconto</th>
                <th>Opções</th>
            </tr>
        </thead>
        <tbody>
            @foreach($promocoes as $p)
            <tr>
                <td>{{ $p->NMPromo }}</td>
                <td>
                    @php
                        $filialNome = $filiais->where('IDFilial', $p->IDFilial)->first();
                    @endphp
                    {{ $filialNome->NMFilial ?? 'N/D' }}
                </td>
                <td>{{ \Carbon\Carbon::parse($p->DTInicioPromo)->format('d/m/Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($p->DTTerminoPromo)->format('d/m/Y') }}</td>
                <td>
                    @if($p->TPDesconto == '%')
                        {{ $p->NUDescontoPromo }}%
                    @else
                        {{ geralController::trataValor($p->NUDescontoPromo, 0) }}
                    @endif
                </td>
                <td>
                    <form action="{{ route('promocoes.destroy', $p->IDPromocao) }}" method="POST" style="display:inline;" onsubmit="return confirm('Deseja excluir esta promoção?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                    <a class="btn btn-primary" href="{{ route('promocoes.edit', $p->IDPromocao) }}">
                        <i class="fa-solid fa-pen"></i>
                    </a>
                    <a class="btn btn-warning" href="{{ route('promocoes.vincular', $p->IDPromocao) }}" title="Vincular Produtos">
                        <i class="fa-solid fa-link"></i>
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection