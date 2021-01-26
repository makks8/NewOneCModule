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
Route::get('crm.refresh.token', [CRMController::class, 'refreshToken']);

Route::post('crm.save.product', [CRMController::class, 'saveProductObject']);
Route::get('crm.load.product', [CRMController::class, 'loadProductObject']);

Route::post('crm.save.company', [CRMController::class, 'saveCompanyObject']);
Route::get('crm.load.company', [CRMController::class, 'loadCompanyObject']);

Route::post('crm.add.company', [CRMController::class, 'addCompany']);
Route::post('crm.add.contact', [CRMController::class, 'addContact']);
Route::post('crm.add.product', [CRMController::class, 'addProduct']);
Route::post('crm.add.deal', [CRMController::class, 'addDeal']);
Route::post('crm.get.id', [CRMController::class, 'getID']);
Route::post('crm.add.timeline', [CRMController::class, 'addTimeline']);
Route::post('crm.add.requisite', [CRMController::class, 'addRequisite']);
Route::post('crm.add.requisite.bankdetail', [CRMController::class, 'addBankRequisite']);
Route::post('crm.add.address', [CRMController::class, 'addAddress']);
#endregion

#region Lists
Route::post('lists.add.elements', [ListsController::class, 'addElements']);
Route::post('lists.get.element', [ListsController::class, 'getElement']);
#endregion

