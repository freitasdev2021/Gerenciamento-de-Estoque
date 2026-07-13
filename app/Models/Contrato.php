<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contrato extends Model
{
    use HasFactory;

    protected $table = 'contratos';
    protected $primaryKey = 'IDContrato';
    public $timestamps = false;

    protected $fillable = [
        'IDPlano',
        'STContrato',
        'DSEndContrato',
        'NMContratante',
        'NMEmailContratante',
        'NUCpfContratante',
        'NUTelefoneContato',
    ];
}