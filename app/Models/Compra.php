<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    use HasFactory;

    protected $table = 'compras';
    protected $primaryKey = 'IDLote';
    public $timestamps = false;

    protected $fillable = [
        'IDProduto',
        'QTCompra',
        'DTReposicao',
    ];
}