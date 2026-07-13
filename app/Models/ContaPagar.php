<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContaPagar extends Model
{
    use HasFactory;

    protected $table = 'contapagar';
    protected $primaryKey = 'IDConta';
    public $timestamps = false;

    protected $fillable = [
        'IDFilial',
        'NMConta',
        'DTExpedicaoConta',
        'DTVencimentoConta',
        'STConta',
        'VLConta',
        'DSJustificativaConta',
    ];
}