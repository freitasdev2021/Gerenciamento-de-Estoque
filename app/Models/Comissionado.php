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
}