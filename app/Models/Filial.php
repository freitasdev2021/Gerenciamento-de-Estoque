<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Filial extends Model
{
    use HasFactory;

    protected $table = 'filiais';
    protected $primaryKey = 'IDFilial';
    public $timestamps = false;

    protected $fillable = [
        'IDEmpresa',
        'DSEnderecoJSON',
        'NMFilial',
        'NUTelefoneFilial',
    ];
}