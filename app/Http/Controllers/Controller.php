<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function decimal($valor) {
        // Remove pontos de milhar e substitui a vírgula decimal por ponto
        $valorLimpo = str_replace(['.', ','], ['', '.'], $valor);
        
        // Força o valor a ser um número decimal (float) com 2 casas
        return number_format((float)$valorLimpo, 2, '.', '');
    }
}
