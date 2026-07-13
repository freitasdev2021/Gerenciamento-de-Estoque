<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';
    protected $primaryKey = 'IDCliente';
    public $timestamps = false;

    protected $fillable = [
        'NMCliente',
        'NMEmailCliente',
        'NUTelefoneCliente',
        'NUCpfCliente',
        'IDFilial',
        'DSEnderecoJSON',
        'STDelete',
    ];
}