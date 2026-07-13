@extends('layouts.appinterno')

@section('content')

<div class="col-sm-12 container p-3">
    <form action="{{ route('categorias.store') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-sm-4 input">
                <label>Nome da Categoria</label>
                <input type="text" name="nomeCategoria" class="form-control" minlength="1" maxlength="50" value="{{ old('nomeCategoria') }}" required>
                @error('nomeCategoria')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-4 input">
                <br>
                <button class="btn btn-success" type="submit">Enviar</button>
            </div>
        </div>
    </form>
</div>
@endsection