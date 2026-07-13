<?php
use App\Http\Controllers\geralController;
?>
@extends('layouts.appinterno')

@section('content')
<div class="col-sm-12 d-flex justify-content-between align-items-center flex-wrap">
    <a href="{{ route('clientes.create') }}" class="btn btn-success mb-2">Adicionar</a>

    <form method="GET" action="{{ route('clientes.index') }}" class="d-flex align-items-center gap-2 mb-2">
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
                <th>CPF</th>
                <th>Dívida</th>
                <th>Opções</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clientes as $c)
            <tr>
                <td>{{ $c->NMCliente }}</td>
                <td>{{ $c->NMEmailCliente }}</td>
                <td>{{ $c->NUTelefoneCliente }}</td>
                <td>{{ $c->NUCpfCliente }}</td>
                <td>
                    @if($c->divida > 0)
                        <span class="text-danger">{{ geralController::trataValor($c->divida, 0) }}</span>
                    @else
                        <span class="text-success">Sem dívida</span>
                    @endif
                </td>
                <td>
                    <form action="{{ route('clientes.destroy', $c->IDCliente) }}" method="POST" style="display:inline;" onsubmit="return confirm('Deseja excluir este cliente?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                    <a class="btn btn-primary" href="{{ route('clientes.edit', $c->IDCliente) }}">
                        <i class="fa-solid fa-pen"></i>
                    </a>
                    @if($c->NUTelefoneCliente)
                    <a class="btn btn-success" href="https://wa.me/55{{ preg_replace('/\D/', '', $c->NUTelefoneCliente) }}" target="_blank" title="WhatsApp">
                        <i class="fa-brands fa-whatsapp"></i>
                    </a>
                    @endif
                    @if($c->NMEmailCliente)
                    <a class="btn btn-info" href="mailto:{{ $c->NMEmailCliente }}" target="_blank" title="Email">
                        <i class="fa-solid fa-envelope"></i>
                    </a>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection