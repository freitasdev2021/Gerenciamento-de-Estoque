<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conta extends Model
{
    use HasFactory;

    protected $table = 'contas';
    protected $primaryKey = 'IDContaPagar';
    public $timestamps = false;

    protected $fillable = [
        'IDFilial',
        'NMConta',
        'DTExpedicao',
        'DTVencimento',
        'STConta',
        'VLConta',
        'DSJustificativaConta',
    ];
}