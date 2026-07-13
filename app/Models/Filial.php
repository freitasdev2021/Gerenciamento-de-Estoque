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

    /**
     * Relacionamento com a Empresa.
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'IDEmpresa', 'IDEmpresa');
    }

    /**
     * Relacionamento com os Colaboradores da filial.
     */
    public function colaboradores()
    {
        return $this->hasMany(Colaborador::class, 'IDFilial', 'IDFilial');
    }
}
