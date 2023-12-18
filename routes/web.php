<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProdutosController;
use App\Http\Controllers\MovimentacoesController;
use App\Http\Controllers\CategoriasController;
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

Route::get('/produtos', [ProdutosController::class, 'index'])->middleware(['auth', 'verified'])->name('produtos.index');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    //CATEGORIAS
    Route::get('/categorias/create', [CategoriasController::class, 'create'])->name('categorias.create'); //FORMULARIO DE CRIAÇÃO
    Route::get('/categorias/editar/{id}', [CategoriasController::class, 'edit'])->name('categorias.edit'); //IMPRESSÃO PARA EDIÇÃO
    Route::post('/categorias/editar/update', [CategoriasController::class, 'update'])->name('categorias.update'); //ATUALIZAR CATEGORIA
    Route::post('/categorias/set', [CategoriasController::class, 'set'])->name('categorias.setCategoria'); //SALVAR CATEGORIA
    Route::post('/categorias/delete', [CategoriasController::class, 'destroy'])->name('categorias.delete'); //EXCLUIR CATEGORIA
    Route::get('/categorias', [CategoriasController::class, 'index'])->name('categorias.index'); //MOSTRAR CATEGORIAS
    //MOVIMENTAÇÕES
    Route::get('/movimentacoes', [MovimentacoesController::class, 'index'])->name('movimentacoes.index'); //MOSTRAR MOVIMENTAÇÕES
    Route::get('/movimentacoes/create', [MovimentacoesController::class, 'create'])->name('movimentacoes.create'); //FORMULARIO MOVIMENTAÇÃO
    Route::post('/movimentacoes/set', [MovimentacoesController::class,'set'])->name('movimentacoes.setMovimentacao'); //SALVAR MOVIMENTAÇÃO
    //PRODUTOS
    Route::get('/produtos/create', [ProdutosController::class, 'create'])->name('produtos.create'); //FORMULARIO DE PRODUTOS
    Route::get('/produtos/editar/{id}', [ProdutosController::class, 'edit'])->name('produtos.edit'); //IMPRESSÃO PARA EDIÇÃO
    Route::post('/produtos/editar/update', [ProdutosController::class, 'update'])->name('produtos.update'); //ATUALIZAR PRODUTO
    Route::post('/produtos/set', [ProdutosController::class,'set'])->name('categorias.setProduto'); //SALVAR PRODUTO
    Route::post('/produtos/delete', [ProdutosController::class,'destroy'])->name('produtos.delete'); //EXCLUIR PRODUTO
    //
});

require __DIR__.'/auth.php';

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
