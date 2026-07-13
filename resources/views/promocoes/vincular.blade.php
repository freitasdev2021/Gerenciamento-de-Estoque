@extends('layouts.appinterno')

@section('content')

<div class="col-sm-12 container p-3">
    <a href="{{ route('promocoes.index') }}" class="btn btn-secondary mb-3">Voltar</a>

    <h5>Vincular Produtos à Promoção: <strong>{{ $promocao->NMPromo }}</strong></h5>
    <hr>

    <form action="{{ route('promocoes.setVinculos', $promocao->IDPromocao) }}" method="POST">
        @csrf

        <div class="table-responsive">
            <table class="table table-bordered text-center tabela">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selecionarTodos"></th>
                        <th>Produto</th>
                        <th>Vinculado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($produtos as $prod)
                    <tr>
                        <td>
                            <input type="checkbox" name="produtos[]" value="{{ $prod->IDProduto }}" class="checkbox-produto" {{ $prod->vinculo == 1 ? 'checked' : '' }}>
                        </td>
                        <td>{{ $prod->NMProduto }}</td>
                        <td>
                            @if($prod->vinculo == 1)
                                <span class="badge bg-success">Sim</span>
                            @else
                                <span class="badge bg-secondary">Não</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="row mt-3">
            <div class="col-sm-12">
                <button class="btn btn-success" type="submit">Vincular Produtos Selecionados</button>
            </div>
        </div>
    </form>
</div>

<script>
    document.getElementById('selecionarTodos').addEventListener('change', function() {
        var checkboxes = document.querySelectorAll('.checkbox-produto');
        checkboxes.forEach(function(cb) {
            cb.checked = this.checked;
        }, this);
    });
</script>
@endsection