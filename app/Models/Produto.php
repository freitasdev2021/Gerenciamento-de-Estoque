<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    use HasFactory;

    protected $table = 'produtos';
    protected $primaryKey = 'IDProduto';
    public $timestamps = false;

    protected $fillable = [
        'IDFornecedor',
        'IDCategoria',
        'NMProduto',
        'NUEstoqueProduto',
        'NUEstoqueMinimo',
        'DSUnidadeProduto',
        'DTEntradaProduto',
        'DTValidadeProduto',
        'NUCustoProduto',
        'NUValorProduto',
        'DSImagemProduto',
        'DSCodigoProduto',
        'NULucroProduto',
        'DSGarantiaProduto',
        'STInsumo',
        'TPIdentificacao',
        'STDelete',
        'NUCustoTotal',
    ];
}