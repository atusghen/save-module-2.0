<?php

use Illuminate\Support\Facades\Route;
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

Route::get('plants', [SaveToolController::class, 'readPlantsView'])->name('savetool_read_view_plants');
Route::get('has', [SaveToolController::class, 'readHasView'])->name('savetool_read_view_has');
Route::get('tryCalculate', [SaveToolController::class, 'showSpesaEnergeticaPerHA'])->name('savetool_try');
Route::get('tryCalculate2', [SaveToolController::class, 'showImportoInvestimentoPerHA'])->name('savetool_try2');
Route::get('tryCalculate3', [SaveToolController::class, 'showFlussiDiCassaPerPlant'])->name('savetool_try3');
Route::get('tryCalculate4', [SaveToolController::class, 'showDebug'])->name('savetool_try4');
Route::get('VanETir', [SaveToolController::class, 'calcoloVanETir'])->name('vanetir');

