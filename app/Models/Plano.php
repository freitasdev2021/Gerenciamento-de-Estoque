<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plano extends Model
{
    use HasFactory;

    protected $table = 'planos';
    protected $primaryKey = 'IDPlano';
    public $timestamps = false;

    protected $fillable = [
        'NMPlano',
        'DSPlano',
        'VLPlano',
        'TMPlano',
    ];
}