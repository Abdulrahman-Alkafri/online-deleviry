<?php

use Illuminate\Support\Facades\Route;
//here web
Route::get('/', function () {
    dd(phpinfo());
});
