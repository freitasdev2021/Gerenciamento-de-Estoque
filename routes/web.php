<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProdutosController;
use App\Http\Controllers\MovimentacoesController;
use App\Http\Controllers\CategoriasController;
use App\Http\Controllers\ClientesController;
use App\Http\Controllers\FornecedoresController;
use App\Http\Controllers\PagamentosController;
use App\Http\Controllers\PromocoesController;
use App\Http\Controllers\ReposicoesController;
use App\Http\Controllers\VendasController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // CLIENTES
    Route::get('/clientes', [ClientesController::class, 'index'])->name('clientes.index');
    Route::get('/clientes/create', [ClientesController::class, 'create'])->name('clientes.create');
    Route::post('/clientes', [ClientesController::class, 'store'])->name('clientes.store');
    Route::get('/clientes/{id}/edit', [ClientesController::class, 'edit'])->name('clientes.edit');
    Route::put('/clientes/{id}', [ClientesController::class, 'update'])->name('clientes.update');
    Route::delete('/clientes/{id}', [ClientesController::class, 'destroy'])->name('clientes.destroy');

    // CATEGORIAS
    Route::get('/categorias', [CategoriasController::class, 'index'])->name('categorias.index');
    Route::get('/categorias/create', [CategoriasController::class, 'create'])->name('categorias.create');
    Route::post('/categorias', [CategoriasController::class, 'store'])->name('categorias.store');
    Route::get('/categorias/{id}/edit', [CategoriasController::class, 'edit'])->name('categorias.edit');
    Route::put('/categorias/{id}', [CategoriasController::class, 'update'])->name('categorias.update');
    Route::delete('/categorias/{id}', [CategoriasController::class, 'destroy'])->name('categorias.destroy');

    // MOVIMENTAÇÕES
    Route::get('/movimentacoes', [MovimentacoesController::class, 'index'])->name('movimentacoes.index');
    Route::get('/movimentacoes/create', [MovimentacoesController::class, 'create'])->name('movimentacoes.create');
    Route::post('/movimentacoes/set', [MovimentacoesController::class, 'set'])->name('movimentacoes.setMovimentacao');

    // PRODUTOS
    Route::get('/produtos', [ProdutosController::class, 'index'])->name('produtos.index');
    Route::get('/produtos/create', [ProdutosController::class, 'create'])->name('produtos.create');
    Route::post('/produtos', [ProdutosController::class, 'store'])->name('produtos.store');
    Route::get('/produtos/{id}/edit', [ProdutosController::class, 'edit'])->name('produtos.edit');
    Route::put('/produtos/{id}', [ProdutosController::class, 'update'])->name('produtos.update');
    Route::delete('/produtos/{id}', [ProdutosController::class, 'destroy'])->name('produtos.destroy');

    // FORNECEDORES
    Route::get('/fornecedores', [FornecedoresController::class, 'index'])->name('fornecedores.index');
    Route::get('/fornecedores/create', [FornecedoresController::class, 'create'])->name('fornecedores.create');
    Route::post('/fornecedores', [FornecedoresController::class, 'store'])->name('fornecedores.store');
    Route::get('/fornecedores/{id}/edit', [FornecedoresController::class, 'edit'])->name('fornecedores.edit');
    Route::put('/fornecedores/{id}', [FornecedoresController::class, 'update'])->name('fornecedores.update');
    Route::delete('/fornecedores/{id}', [FornecedoresController::class, 'destroy'])->name('fornecedores.destroy');

    // PAGAMENTOS
    Route::get('/pagamentos', [PagamentosController::class, 'index'])->name('pagamentos.index');
    Route::get('/pagamentos/create', [PagamentosController::class, 'create'])->name('pagamentos.create');
    Route::post('/pagamentos', [PagamentosController::class, 'store'])->name('pagamentos.store');
    Route::get('/pagamentos/{id}/edit', [PagamentosController::class, 'edit'])->name('pagamentos.edit');
    Route::put('/pagamentos/{id}', [PagamentosController::class, 'update'])->name('pagamentos.update');
    Route::delete('/pagamentos/{id}', [PagamentosController::class, 'destroy'])->name('pagamentos.destroy');

    // PROMOÇÕES
    Route::get('/promocoes', [PromocoesController::class, 'index'])->name('promocoes.index');
    Route::get('/promocoes/create', [PromocoesController::class, 'create'])->name('promocoes.create');
    Route::post('/promocoes', [PromocoesController::class, 'store'])->name('promocoes.store');
    Route::get('/promocoes/{id}/edit', [PromocoesController::class, 'edit'])->name('promocoes.edit');
    Route::put('/promocoes/{id}', [PromocoesController::class, 'update'])->name('promocoes.update');
    Route::delete('/promocoes/{id}', [PromocoesController::class, 'destroy'])->name('promocoes.destroy');
    Route::get('/promocoes/{id}/vincular', [PromocoesController::class, 'vincular'])->name('promocoes.vincular');
    Route::post('/promocoes/{id}/vincular', [PromocoesController::class, 'setVinculos'])->name('promocoes.setVinculos');

    // REPOSIÇÕES
    Route::get('/reposicoes', [ReposicoesController::class, 'index'])->name('reposicoes.index');
    Route::get('/reposicoes/create', [ReposicoesController::class, 'create'])->name('reposicoes.create');
    Route::post('/reposicoes', [ReposicoesController::class, 'store'])->name('reposicoes.store');
    
    // VENDAS
    Route::get('/vendas', [VendasController::class, 'index'])->name('vendas.index');
    Route::get('/vendas/create', [VendasController::class, 'create'])->name('vendas.create');
    Route::post('/vendas', [VendasController::class, 'store'])->name('vendas.store');
    Route::delete('/vendas/{id}', [VendasController::class, 'destroy'])->name('vendas.destroy');

    // RELATÓRIOS
    Route::get('/relatorios', [MovimentacoesController::class, 'indexRel'])->name('relatorios.index');
    Route::post('/relatorios/graficos', [MovimentacoesController::class, 'getRelatorio'])->name('relatorios.graficos');
});

require __DIR__.'/auth.php';

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');