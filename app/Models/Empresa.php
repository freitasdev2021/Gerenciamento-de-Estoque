<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;

    protected $table = 'empresas';
    protected $primaryKey = 'IDEmpresa';
    public $timestamps = false;

    protected $fillable = [
        'IDContrato',
        'NMFantasiaEmpresa',
        'NMRazaoEmpresa',
        'NUCnpjEmpresa',
        'STEmpresa',
    ];
}