<?php
use App\Http\Controllers\geralController;
?>
@extends('layouts.appinterno')

@section('content')
<div class="col-sm-12 d-flex justify-content-between align-items-center flex-wrap">
    <a href="{{ route('pagamentos.create') }}" class="btn btn-success mb-2">Adicionar</a>

    <form method="GET" action="{{ route('pagamentos.index') }}" class="d-flex align-items-center gap-2 mb-2">
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
                <th>Método</th>
                <th>Parcelas</th>
                <th>Desconto</th>
                <th>Juros (%)</th>
                <th>Opções</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pagamentos as $p)
            <tr>
                <td>{{ $p->NMPagamento }}</td>
                <td>
                    @php
                        $filialNome = $filiais->where('IDFilial', $p->IDFilial)->first();
                    @endphp
                    {{ $filialNome->NMFilial ?? 'N/D' }}
                </td>
                <td>{{ $p->DSMetodo }}</td>
                <td>{{ $p->QTParcelas }}x</td>
                <td>
                    @if($p->TPDesconto == '%')
                        {{ $p->QTDesconto }}%
                    @else
                        {{ geralController::trataValor($p->QTDesconto, 0) }}
                    @endif
                </td>
                <td>{{ $p->NUJuros }}%</td>
                <td>
                    <form action="{{ route('pagamentos.destroy', $p->IDPagamento) }}" method="POST" style="display:inline;" onsubmit="return confirm('Deseja excluir este pagamento?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                    <a class="btn btn-primary" href="{{ route('pagamentos.edit', $p->IDPagamento) }}">
                        <i class="fa-solid fa-pen"></i>
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection