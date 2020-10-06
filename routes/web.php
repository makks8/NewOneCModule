<?php

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

#region CRM
Route::get('crm.sync', [CRMController::class, 'sync']);
Route::get('crm.test', [CRMController::class, 'test']);
Route::post('crm.add.company', [CRMController::class, 'addCompany']);
#endregion

#region Lists
Route::post('lists.add.elements', [ListsController::class, 'addElements']);
Route::post('lists.get.element', [ListsController::class, 'getElement']);
#endregion