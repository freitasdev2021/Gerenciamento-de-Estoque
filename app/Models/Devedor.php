<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Devedor extends Model
{
    use HasFactory;

    protected $table = 'devedores';
    protected $primaryKey = 'IDDevedor';
    public $timestamps = false;

    protected $fillable = [
        'IDCliente',
        'VLDivida',
        'DTInicioDivida',
    ];
}