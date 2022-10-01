<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Api\User as AdminApiUserController;

Route::middleware(['auth:sanctum', 'admin-token'])->group(function() {
  Route::prefix('/admin/api')->group(function() {
    Route::prefix('/user')->group(function() {
      Route::controller(AdminApiUserController::class)->group(function() {
        Route::get('/list/{id?}', 'list');
        Route::get('/list-horizontal/{id?}', 'listHorizontal');
        Route::post('/create', 'create');
        Route::delete('/destroy/{id}', 'destroy');
        Route::put('/edit/{id}', 'edit');
      });
    });
  });
});
