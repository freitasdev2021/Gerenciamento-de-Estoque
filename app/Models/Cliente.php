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
        'IDContrato',
        'STDelete',
    ];

    /**
     * Relacionamento com a Filial.
     */
    public function contratos()
    {
        return $this->belongsTo(Filial::class, 'IDContrato', 'IDContrato');
    }

    /**
     * Relacionamento com Devedor (um cliente pode ter uma dívida).
     */
    public function devedor()
    {
        return $this->hasOne(Devedor::class, 'IDCliente', 'IDCliente');
    }

    /**
     * Relacionamento com Crediarios (um cliente pode ter vários créditos).
     */
    public function crediarios()
    {
        return $this->hasMany(Crediario::class, 'IDCliente', 'IDCliente');
    }

    /**
     * Relacionamento com Cupons (compras do cliente).
     */
    public function cupons()
    {
        return $this->hasMany(Cupom::class, 'IDCliente', 'IDCliente');
    }

    /**
     * Relacionamento com Ordens de Serviço.
     */
    public function ordensServico()
    {
        return $this->hasMany(OrdemServico::class, 'IDCliente', 'IDCliente');
    }
}
