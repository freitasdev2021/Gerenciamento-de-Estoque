<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cupom extends Model
{
    use HasFactory;

    protected $table = 'cupons';
    protected $primaryKey = 'IDCupom';
    public $timestamps = false;

    protected $fillable = [
        'IDCaixa',
        'ANCupom',
        'CDVenda',
        'IDCliente',
        'IDFilial',
    ];

    /**
     * Relacionamento com o Cliente.
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'IDCliente', 'IDCliente');
    }
}
