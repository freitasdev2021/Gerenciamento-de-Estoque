<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pagamento extends Model
{
    use HasFactory;

    protected $table = 'pagamentos';
    protected $primaryKey = 'IDPagamento';
    public $timestamps = false;

    protected $fillable = [
        'NMPagamento',
        'QTDesconto',
        'DSMetodo',
        'QTParcelas',
        'TPDesconto',
        'IDContrato',
        'NUJuros',
        'STDelete',
    ];
}