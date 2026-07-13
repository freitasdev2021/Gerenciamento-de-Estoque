<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comissao extends Model
{
    use HasFactory;

    protected $table = 'comissoes';
    protected $primaryKey = 'IDComissao';
    public $timestamps = false;

    protected $fillable = [
        'NMComissao',
        'NUPorcentagem',
        'IDFilial',
        'TPComissao',
    ];
}