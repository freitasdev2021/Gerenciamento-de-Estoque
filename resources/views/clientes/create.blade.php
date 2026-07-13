@extends('layouts.appinterno')

@section('content')

<div class="col-sm-12 container p-3">
    <a href="{{ route('clientes.index') }}" class="btn btn-secondary mb-3">Voltar</a>

    @php
        $editando = isset($cliente) && $cliente;
        $action = $editando ? route('clientes.update', $cliente->IDCliente) : route('clientes.store');
    @endphp

    <form action="{{ $action }}" method="POST">
        @csrf
        @if($editando)
            @method('PUT')
        @endif

        <h5>{{ $editando ? 'Editar Cliente' : 'Novo Cliente' }}</h5>
        <hr>

        <div class="row">
            <div class="col-sm-4 input mb-2">
                <label>Nome do Cliente</label>
                <input type="text" name="nomeCliente" class="form-control" minlength="1" maxlength="100" value="{{ old('nomeCliente', $cliente->NMCliente ?? '') }}" required>
                @error('nomeCliente')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-4 input mb-2">
                <label>Email</label>
                <input type="email" name="emailCliente" class="form-control" maxlength="150" value="{{ old('emailCliente', $cliente->NMEmailCliente ?? '') }}">
                @error('emailCliente')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-4 select mb-2">
                <label>Filial</label>
                <select name="IDFilial" class="form-select" required>
                    <option value="">-- Selecione uma Filial --</option>
                    @foreach($filiais as $f)
                        <option value="{{ $f->IDFilial }}" {{ old('IDFilial', $cliente->IDFilial ?? '') == $f->IDFilial ? 'selected' : '' }}>
                            {{ $f->NMFilial }}
                        </option>
                    @endforeach
                </select>
                @error('IDFilial')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-sm-4 input mb-2">
                <label>Telefone</label>
                <input type="text" name="telefoneCliente" class="form-control" maxlength="20" value="{{ old('telefoneCliente', $cliente->NUTelefoneCliente ?? '') }}">
                @error('telefoneCliente')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-4 input mb-2">
                <label>CPF</label>
                <input type="text" name="cpfCliente" class="form-control" maxlength="20" value="{{ old('cpfCliente', $cliente->NUCpfCliente ?? '') }}">
                @error('cpfCliente')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-sm-12">
                <button class="btn btn-success" type="submit">{{ $editando ? 'Atualizar' : 'Cadastrar' }}</button>
            </div>
        </div>
    </form>

    @if($editando)
    <hr class="mt-5">
    <div class="row mt-3">
        {{-- Card: Produtos Comprados --}}
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <i class="fa-solid fa-cart-shopping"></i> Produtos Comprados
                </div>
                <div class="card-body">
                    @if(isset($produtosComprados) && count($produtosComprados) > 0)
                        <table class="table table-sm table-bordered text-center">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Qtd</th>
                                    <th>Valor</th>
                                    <th>Pagamento</th>
                                    <th>Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($produtosComprados as $v)
                                <tr>
                                    <td>{{ $v->NMProduto }}</td>
                                    <td>{{ $v->NUUnidadesVendidas }}</td>
                                    <td>{{ \App\Http\Controllers\geralController::trataValor($v->VLVenda, 0) }}</td>
                                    <td>{{ $v->NMPagamento }}</td>
                                    <td>{{ \Carbon\Carbon::parse($v->DTVenda)->format('d/m/Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted mb-0">Nenhum produto comprado.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Card: Serviços Consumidos --}}
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <i class="fa-solid fa-wrench"></i> Serviços Consumidos
                </div>
                <div class="card-body">
                    @if(isset($servicos) && count($servicos) > 0)
                        <table class="table table-sm table-bordered text-center">
                            <thead>
                                <tr>
                                    <th>Serviço</th>
                                    <th>Atendente</th>
                                    <th>Método</th>
                                    <th>Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($servicos as $s)
                                <tr>
                                    <td>{{ $s->servico }}</td>
                                    <td>{{ $s->atendente }}</td>
                                    <td>{{ $s->metodo }}</td>
                                    <td>{{ \Carbon\Carbon::parse($s->dataHora)->format('d/m/Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted mb-0">Nenhum serviço consumido.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection