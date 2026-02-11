<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Auth::routes();

Route::middleware(['auth'])->group(function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/change-password', [ProfileController::class, 'changepassword'])->name('profile.change-password');
    Route::put('/profile/password', [ProfileController::class, 'password'])->name('profile.password');
    Route::get('/blank-page', [App\Http\Controllers\HomeController::class, 'blank'])->name('blank');

    Route::get('/hakakses', [App\Http\Controllers\HakaksesController::class, 'index'])->name('hakakses.index')->middleware('superadmin');
    Route::get('/hakakses/edit/{id}', [App\Http\Controllers\HakaksesController::class, 'edit'])->name('hakakses.edit')->middleware('superadmin');
    Route::put('/hakakses/update/{id}', [App\Http\Controllers\HakaksesController::class, 'update'])->name('hakakses.update')->middleware('superadmin');
    Route::delete('/hakakses/delete/{id}', [App\Http\Controllers\HakaksesController::class, 'destroy'])->name('hakakses.delete')->middleware('superadmin');

    // Admin Users Management (Only Superadmin)
    Route::middleware('superadmin')->group(function () {
        Route::resource('admin-users', App\Http\Controllers\AdminUserController::class);
        Route::resource('lokasi', App\Http\Controllers\LokasiController::class);
        Route::resource('layanan', App\Http\Controllers\LayananController::class);
    });

    // Kasir Users Management (Only Admin)
    Route::middleware('admin')->group(function () {
        Route::resource('kasir-users', App\Http\Controllers\KasirController::class);
        // Penerimaan Notices - Read Only for Admin
        Route::prefix('admin')->group(function () {
            Route::get('penerimaan-notices', [App\Http\Controllers\PenerimaanNoticeController::class, 'index'])->name('admin.penerimaan-notices.index');
            Route::get('penerimaan-notices/{penerimaan_notice}', [App\Http\Controllers\PenerimaanNoticeController::class, 'show'])->name('admin.penerimaan-notices.show');
            Route::get('penerimaan-notices-export', [App\Http\Controllers\PenerimaanNoticeController::class, 'exportAdmin'])->name('admin.penerimaan-notices.export');
        });
    });

    // Penerimaan Notices Management (Only Kasir)
    Route::middleware('kasir')->group(function () {
        Route::get('penerimaan-notices/export', [App\Http\Controllers\PenerimaanNoticeController::class, 'export'])->name('penerimaan-notices.export');
        Route::resource('penerimaan-notices', App\Http\Controllers\PenerimaanNoticeController::class)->except(['destroy']);
        Route::resource('pengeluaran-notices', App\Http\Controllers\PengeluaranNoticeController::class)->except(['destroy', 'show', 'edit', 'update']);
    });

    Route::get('/table-example', [App\Http\Controllers\ExampleController::class, 'table'])->name('table.example');
    Route::get('/clock-example', [App\Http\Controllers\ExampleController::class, 'clock'])->name('clock.example');
    Route::get('/chart-example', [App\Http\Controllers\ExampleController::class, 'chart'])->name('chart.example');
    Route::get('/form-example', [App\Http\Controllers\ExampleController::class, 'form'])->name('form.example');
    Route::get('/map-example', [App\Http\Controllers\ExampleController::class, 'map'])->name('map.example');
    Route::get('/calendar-example', [App\Http\Controllers\ExampleController::class, 'calendar'])->name('calendar.example');
    Route::get('/gallery-example', [App\Http\Controllers\ExampleController::class, 'gallery'])->name('gallery.example');
    Route::get('/todo-example', [App\Http\Controllers\ExampleController::class, 'todo'])->name('todo.example');
    Route::get('/contact-example', [App\Http\Controllers\ExampleController::class, 'contact'])->name('contact.example');
    Route::get('/faq-example', [App\Http\Controllers\ExampleController::class, 'faq'])->name('faq.example');
    Route::get('/news-example', [App\Http\Controllers\ExampleController::class, 'news'])->name('news.example');
    Route::get('/about-example', [App\Http\Controllers\ExampleController::class, 'about'])->name('about.example');
});
