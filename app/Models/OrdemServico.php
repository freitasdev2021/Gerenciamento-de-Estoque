<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdemServico extends Model
{
    use HasFactory;

    protected $table = 'ordemservico';
    protected $primaryKey = 'IDOrdem';
    public $timestamps = false;

    protected $fillable = [
        'IDServico',
        'IDCliente',
        'IDColaborador',
        'DTServico',
        'STServico',
        'DSOrdemServico',
        'DSServico',
        'DSNota',
        'DTSaida',
        'IDPagamento',
    ];
}