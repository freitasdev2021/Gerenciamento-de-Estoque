<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustosOrdem extends Model
{
    use HasFactory;

    protected $table = 'custosordem';
    protected $primaryKey = 'IDCusto';
    public $timestamps = false;

    protected $fillable = [
        'IDProduto',
        'IDOrdem',
        'NUQuantidade',
    ];
}