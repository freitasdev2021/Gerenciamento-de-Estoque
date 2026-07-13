<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promocao extends Model
{
    use HasFactory;

    protected $table = 'promocoes';
    protected $primaryKey = 'IDPromocao';
    public $timestamps = false;

    protected $fillable = [
        'NMPromo',
        'DTInicioPromo',
        'DTTerminoPromo',
        'NUDescontoPromo',
        'TPDesconto',
        'IDFilial',
        'STDelete',
    ];

    /**
     * Relacionamento com a Filial.
     */
    public function filial()
    {
        return $this->belongsTo(Filial::class, 'IDFilial', 'IDFilial');
    }

    /**
     * Relacionamento com os produtos promocionais vinculados.
     */
    public function promocionais()
    {
        return $this->hasMany(Promocional::class, 'IDPromocao', 'IDPromocao');
    }

    /**
     * Relacionamento com as vendas que usaram esta promoção.
     */
    public function vendas()
    {
        return $this->hasMany(Venda::class, 'IDPromocao', 'IDPromocao');
    }
}
