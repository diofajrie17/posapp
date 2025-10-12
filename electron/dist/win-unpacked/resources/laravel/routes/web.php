<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;  
use App\Http\Controllers\ReportController;    




/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/


Route::get('/', function () {
    return Inertia::render('Home'); // ini akan cari file Home.vue
});

// semua tetap butuh auth
Route::middleware('auth')->group(function () {

    // hanya Admin boleh kelola pengaturan
    Route::middleware('role:Admin')->group(function () {
        Route::resource('products', ProductController::class)->except(['index', 'show']);
        Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])
            ->name('transactions.destroy');
                Route::get('/reports/daily-sales', [ReportController::class, 'dailySales'])->name('reports.daily');
        Route::get('/reports/daily-sales/export', [ReportController::class, 'exportDailySalesCsv'])->name('reports.daily.export');
                Route::get('/inventory/stock', [StockController::class, 'index'])->name('stock.index');
        Route::get('/inventory/stock/opname', [StockController::class, 'create'])->name('stock.create');
        Route::post('/inventory/stock/opname', [StockController::class, 'store'])->name('stock.store');
        Route::get('/inventory/movements', [StockController::class, 'movements'])->name('stock.movements');
         Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::post('/expenses', [ExpenseController::class, 'store'])->middleware('permission:expenses.create')->name('expenses.store');
        Route::get('/expenses/{expense}/edit', [ExpenseController::class, 'edit'])->middleware('permission:expenses.update')->name('expenses.edit');
        Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->middleware('permission:expenses.update')->name('expenses.update');
        Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->middleware('permission:expenses.delete')->name('expenses.destroy');
    });
    });

    Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });
    // Kasir + Admin
    Route::middleware('permission:products.view')->group(function () {
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    });

    Route::middleware('permission:transactions.create')->group(function () {
        Route::get('/transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
        Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
        
    });

    Route::middleware('permission:reports.view')->group(function () {
        Route::get('/reports/daily-sales', [ReportController::class, 'dailySales'])->name('reports.daily');
        Route::get('/reports/daily-sales/export', [ReportController::class, 'exportDailySalesCsv'])->name('reports.daily.export');
    });

    Route::middleware('permission:expenses.view')->group(function () {
        Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::post('/expenses', [ExpenseController::class, 'store'])->middleware('permission:expenses.create')->name('expenses.store');
        Route::get('/expenses/{expense}/edit', [ExpenseController::class, 'edit'])->middleware('permission:expenses.update')->name('expenses.edit');
        Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->middleware('permission:expenses.update')->name('expenses.update');
        Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->middleware('permission:expenses.delete')->name('expenses.destroy');
    });

    Route::middleware('permission:notifications.view')->group(function () {
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{notification}/send', [NotificationController::class, 'send'])
            ->middleware('permission:notifications.send')
            ->name('notifications.send');
    });

    Route::middleware('permission:products.stockopname')->group(function () {
        Route::get('/inventory/stock', [StockController::class, 'index'])->name('stock.index');
        Route::get('/inventory/stock/opname', [StockController::class, 'create'])->name('stock.create');
        Route::post('/inventory/stock/opname', [StockController::class, 'store'])->name('stock.store');
        Route::get('/inventory/movements', [StockController::class, 'movements'])->name('stock.movements');
    });
    // Manajemen Member
Route::middleware('permission:members.view')->group(function () {
    Route::get('/members', [MemberController::class, 'index'])->name('members.index');
});

Route::middleware('permission:members.create')->group(function () {
    Route::get('/members/create', [MemberController::class, 'create'])->name('members.create');
    Route::post('/members', [MemberController::class, 'store'])->name('members.store');
});

Route::middleware('permission:members.update')->group(function () {
    Route::get('/members/{member}/edit', [MemberController::class, 'edit'])->name('members.edit');
    Route::put('/members/{member}', [MemberController::class, 'update'])->name('members.update');
});

Route::middleware('permission:members.delete')->group(function () {
    Route::delete('/members/{member}', [MemberController::class, 'destroy'])->name('members.destroy');
});



require __DIR__.'/auth.php';
