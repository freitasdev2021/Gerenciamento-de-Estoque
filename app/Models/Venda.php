<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venda extends Model
{
    use HasFactory;

    protected $table = 'vendas';
    protected $primaryKey = 'IDVenda';
    public $timestamps = false;

    protected $fillable = [
        'IDProduto',
        'IDCliente',
        'IDColaborador',
        'NUUnidadesVendidas',
        'DTVenda',
        'IDPagamento',
        'VLVenda'
    ];

    /**
     * Relacionamento com o Produto.
     */
    public function produto()
    {
        return $this->belongsTo(Produto::class, 'IDProduto', 'IDProduto');
    }

    /**
     * Relacionamento com o Cliente.
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'IDCliente', 'IDCliente');
    }

    /**
     * Relacionamento com o Colaborador (atendente).
     */
    public function colaborador()
    {
        return $this->belongsTo(Colaborador::class, 'IDColaborador', 'IDColaborador');
    }

    /**
     * Relacionamento com o Pagamento.
     */
    public function pagamento()
    {
        return $this->belongsTo(Pagamento::class, 'IDPagamento', 'IDPagamento');
    }
}
