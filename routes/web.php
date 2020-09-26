<?php

use AliSyria\LDOG\Http\Controllers\RealResourceController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;


Route::domain('{sector}.'.config('ldog.domain'))->group(function(){
    Route::get('resoucre/{concept}/{reference}/{subConcept}/{subReference}',
        [RealResourceController::class,'subResource']);
    Route::get('page/{concept}/{reference}/{subConcept}/{subReference}',
        [RealResourceController::class,'subPage']);
    Route::get('data/{concept}/{reference}/{subConcept}/{subReference}',
        [RealResourceController::class,'subData']);

    Route::get('resoucre/{concept}/{reference}',[RealResourceController::class,'resource']);
    Route::get('page/{concept}/{reference}',[RealResourceController::class,'page']);
    Route::get('data/{concept}/{reference}',[RealResourceController::class,'data']);

});