<?php
use App\Http\Controllers\geralController;
?>
@extends('layouts.appinterno')

@section('content')
<div class="col-sm-12 d-flex justify-content-between align-items-center flex-wrap">
    <a href="{{ route('fornecedores.create') }}" class="btn btn-success mb-2">Adicionar</a>

    <form method="GET" action="{{ route('fornecedores.index') }}" class="d-flex align-items-center gap-2 mb-2">
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
                <th>Email</th>
                <th>Telefone</th>
                <th>Filial</th>
                <th>Opções</th>
            </tr>
        </thead>
        <tbody>
            @foreach($fornecedores as $f)
            <tr>
                <td>{{ $f->NMFornecedor }}</td>
                <td>{{ $f->DSEmailFornecedor }}</td>
                <td>{{ $f->DSTelefoneFornecedor }}</td>
                <td>
                    @php
                        $filialNome = $filiais->where('IDFilial', $f->IDFilial)->first();
                    @endphp
                    {{ $filialNome->NMFilial ?? 'N/D' }}
                </td>
                <td>
                    <form action="{{ route('fornecedores.destroy', $f->IDFornecedor) }}" method="POST" style="display:inline;" onsubmit="return confirm('Deseja excluir este fornecedor?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                    <a class="btn btn-primary" href="{{ route('fornecedores.edit', $f->IDFornecedor) }}">
                        <i class="fa-solid fa-pen"></i>
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection