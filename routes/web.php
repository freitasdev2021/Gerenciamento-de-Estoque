<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProdutosController;
use App\Http\Controllers\MovimentacoesController;
use App\Http\Controllers\CategoriasController;
use App\Http\Controllers\FornecedoresController;
use App\Http\Controllers\PagamentosController;
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

    // RELATÓRIOS
    Route::get('/relatorios', [MovimentacoesController::class, 'indexRel'])->name('relatorios.index');
    Route::post('/relatorios/graficos', [MovimentacoesController::class, 'getRelatorio'])->name('relatorios.graficos');
});

require __DIR__.'/auth.php';

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');