<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LabController;
use App\Http\Controllers\SaveToolController;
use App\Http\Controllers\CalculateController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('lab', [LabController::class, 'index'])->name('lab');

Route::get('flussiDiCassa', [SaveToolController::class, 'showFlussiDiCassaPerPlant'])->name('flussidicassa');
Route::get('CalcoloImpianto', [SaveToolController::class, 'showCalcoloImpianto'])->name('calcoloimpianto');
Route::get('VanETir', [SaveToolController::class, 'calcoloVanETir'])->name('vanetir');
Route::get('PayBack', [SaveToolController::class, 'calcoloPaybackMinEMax'])->name('payback');
Route::get('calcolaAltreModalita', [SaveToolController::class, 'calcolaAltreModalità'])->name('altro');

