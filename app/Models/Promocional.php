<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promocional extends Model
{
    use HasFactory;

    protected $table = 'promocionais';
    protected $primaryKey = 'IDPromocional';
    public $timestamps = false;

    protected $fillable = [
        'IDPromocao',
        'IDProduto',
    ];
}