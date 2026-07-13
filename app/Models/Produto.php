<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    use HasFactory;

    protected $table = 'produtos';
    protected $primaryKey = 'IDProduto';
    public $timestamps = false;

    protected $fillable = [
        'IDFornecedor',
        'IDCategoria',
        'NMProduto',
        'NUEstoqueProduto',
        'NUEstoqueMinimo',
        'DSUnidadeProduto',
        'DTEntradaProduto',
        'DTValidadeProduto',
        'NUCustoProduto',
        'NUValorProduto',
        'DSImagemProduto',
        'DSCodigoProduto',
        'NULucroProduto',
        'DSGarantiaProduto',
        'STInsumo',
        'TPIdentificacao',
        'STDelete',
        'NUCustoTotal',
    ];

    /**
     * Relacionamento com o Fornecedor.
     */
    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'IDFornecedor', 'IDFornecedor');
    }

    /**
     * Relacionamento com a Categoria.
     */
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'IDCategoria', 'IDCategoria');
    }

    /**
     * Relacionamento com as Vendas deste produto.
     */
    public function vendas()
    {
        return $this->hasMany(Venda::class, 'IDProduto', 'IDProduto');
    }

    /**
     * Relacionamento com as Compras (lotes/reposições) deste produto.
     */
    public function compras()
    {
        return $this->hasMany(Compra::class, 'IDProduto', 'IDProduto');
    }

    /**
     * Relacionamento com os vínculos promocionais (promocionais).
     */
    public function promocionais()
    {
        return $this->hasMany(Promocional::class, 'IDProduto', 'IDProduto');
    }

    /**
     * Relacionamento com os Custos de Ordem de Serviço (insumos).
     */
    public function custosOrdem()
    {
        return $this->hasMany(CustosOrdem::class, 'IDProduto', 'IDProduto');
    }

    /**
     * Relacionamento com as Movimentações de estoque.
     */
    public function movimentacoes()
    {
        return $this->hasMany(Movimentacao::class, 'IDProduto', 'IDProduto');
    }
}
