<?php
use App\Http\Controllers\geralController;
?>
@extends('layouts.appinterno')

@section('content')
<canvas id="graficoProdutos" data-csrf="{{ csrf_token()}}"></canvas>
@endsection