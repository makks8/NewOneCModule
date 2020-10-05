<?php

use App\Http\Controllers\CRMController;
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
