<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caixa extends Model
{
    use HasFactory;

    protected $table = 'caixa';
    protected $primaryKey = 'IDCaixa';
    public $timestamps = false;

    protected $fillable = [
        'IDFilial',
        'DTAberturaCaixa',
        'DTFechamentoCaixa',
        'STCaixa',
        'NMPdv',
        'NMSenhaPDV',
        'STDelete',
    ];
}