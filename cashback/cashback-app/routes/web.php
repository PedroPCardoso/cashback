<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/transactions/create');
});

Route::get('/transactions/create', function () {
    return view('transactions.create');
});

Route::get('/transactions', function () {
    return view('transactions.index');
});

Route::get('/categories', function () {
    return view('categories.index');
});

Route::get('/summary', function () {
    return view('summary.index');
});
