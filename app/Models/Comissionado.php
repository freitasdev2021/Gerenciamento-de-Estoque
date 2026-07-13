<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comissionado extends Model
{
    use HasFactory;

    protected $table = 'comissionados';
    protected $primaryKey = 'IDComissionado';
    public $timestamps = false;

    protected $fillable = [
        'IDComissao',
        'IDColaborador',
    ];

    /**
     * Relacionamento com a Comissão.
     */
    public function comissao()
    {
        return $this->belongsTo(Comissao::class, 'IDComissao', 'IDComissao');
    }

    /**
     * Relacionamento com o Colaborador.
     */
    public function colaborador()
    {
        return $this->belongsTo(Colaborador::class, 'IDColaborador', 'IDColaborador');
    }
}
