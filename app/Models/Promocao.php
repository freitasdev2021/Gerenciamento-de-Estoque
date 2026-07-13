<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promocao extends Model
{
    use HasFactory;

    protected $table = 'promocoes';
    protected $primaryKey = 'IDPromocao';
    public $timestamps = false;

    protected $fillable = [
        'NMPromo',
        'DTInicioPromo',
        'DTTerminoPromo',
        'NUDescontoPromo',
        'TPDesconto',
        'IDFilial',
        'STDelete',
    ];
}