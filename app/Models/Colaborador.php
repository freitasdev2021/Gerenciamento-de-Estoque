<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Colaborador extends Model
{
    use HasFactory;

    protected $table = 'colaboradores';
    protected $primaryKey = 'IDColaborador';
    public $timestamps = false;

    protected $fillable = [
        'NMColaborador',
        'NMEmailColaborador',
        'NMCargoColaborador',
        'NUCpfColaborador',
        'VLSalario',
        'DTAdmissao',
        'STFerias',
        'STAcesso',
        'IDComissao',
        'IDFilial',
    ];
}