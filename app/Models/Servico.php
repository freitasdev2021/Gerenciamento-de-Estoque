<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servico extends Model
{
    use HasFactory;

    protected $table = 'servicos';
    protected $primaryKey = 'IDServico';
    public $timestamps = false;

    protected $fillable = [
        'VLBase',
        'DSTipoServico',
        'IDFilial',
        'DSGarantiaServico',
        'STDelete',
    ];
}