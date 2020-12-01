<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\CRMController;
use App\Http\Controllers\ListsController;
use Illuminate\Support\Facades\Route;

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

#region Client
Route::get('clients-list', [ClientController::class, 'renderClientList']);
#endregion

#region Crm
Route::get('crm.sync', [CRMController::class, 'sync']);
Route::get('crm.test', [CRMController::class, 'test']);
Route::post('crm.add.company', [CRMController::class, 'addCompany']);
Route::post('crm.add.contact', [CRMController::class, 'addContact']);
Route::post('crm.add.product', [CRMController::class, 'addProduct']);
Route::post('crm.add.deal', [CRMController::class, 'addDeal']);
Route::post('crm.get.id', [CRMController::class, 'getID']);
Route::post('crm.add.timeline', [CRMController::class, 'addTimeline']);
#endregion

#region Lists
Route::post('lists.add.elements', [ListsController::class, 'addElements']);
Route::post('lists.get.element', [ListsController::class, 'getElement']);
#endregion

