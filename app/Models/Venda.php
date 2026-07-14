<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venda extends Model
{
    use HasFactory;

    protected $table = 'vendas';
    protected $primaryKey = 'IDVenda';
    public $timestamps = false;

    protected $fillable = [
        'IDProduto',
        'IDCliente',
        'IDColaborador',
        'NUUnidadesVendidas',
        'DTVenda',
        'IDPagamento',
        'VLVenda'
    ];
}