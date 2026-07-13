<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Crediario extends Model
{
    use HasFactory;

    protected $table = 'crediarios';
    protected $primaryKey = 'IDCrediario';
    public $timestamps = false;

    protected $fillable = [
        'IDCliente',
        'NUCredito',
        'DTInicioCredito',
        'DTTerminoCredito',
    ];

    /**
     * Relacionamento com o Cliente.
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'IDCliente', 'IDCliente');
    }
}
