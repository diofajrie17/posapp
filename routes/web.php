<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;  
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AdController;
use App\Http\Controllers\FacilityController;    




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
        Route::resource('categories', CategoryController::class);
        Route::resource('units', UnitController::class);
        Route::get('/api/units-for-select', [UnitController::class, 'getUnitsForSelect'])->name('units.for-select');
        Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])
            ->name('transactions.destroy');
                Route::get('/reports/daily-sales', [ReportController::class, 'dailySales'])->name('reports.daily');
        Route::get('/reports/daily-sales/export', [ReportController::class, 'exportDailySalesCsv'])->name('reports.daily.export');
                Route::get('/inventory/stock', [StockController::class, 'index'])->name('stock.index');
        Route::get('/inventory/stock/opname', [StockController::class, 'create'])->name('stock.create');
        Route::post('/inventory/stock/opname', [StockController::class, 'store'])->name('stock.store');
        Route::get('/inventory/movements', [StockController::class, 'movements'])->name('stock.movements');
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

    Route::middleware('permission:transactions.view')->group(function () {
        Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
        Route::get('/transactions/{transaction}/receipt', [TransactionController::class, 'receipt'])->name('transactions.receipt');
    });

    Route::middleware('permission:reports.view')->group(function () {
        Route::get('/reports/daily-sales', [ReportController::class, 'dailySales'])->name('reports.daily');
        Route::get('/reports/daily-sales/export', [ReportController::class, 'exportDailySalesCsv'])->name('reports.daily.export');
        Route::get('/reports/comprehensive', [ReportController::class, 'comprehensive'])->name('reports.comprehensive');
        Route::get('/reports/comprehensive/export', [ReportController::class, 'exportComprehensiveCsv'])->name('reports.comprehensive.export');
    });

    Route::middleware('permission:expenses.view')->group(function () {
        Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::get('/expenses/export', [ExpenseController::class, 'export'])->name('expenses.export');
        Route::get('/expenses/create', [ExpenseController::class, 'create'])->middleware('permission:expenses.create')->name('expenses.create');
        Route::post('/expenses', [ExpenseController::class, 'store'])->middleware('permission:expenses.create')->name('expenses.store');
        Route::get('/expenses/{expense}/edit', [ExpenseController::class, 'edit'])->middleware('permission:expenses.update')->name('expenses.edit');
        Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->middleware('permission:expenses.update')->name('expenses.update');
        Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->middleware('permission:expenses.delete')->name('expenses.destroy');
    });

    // Ads Management (using expenses permissions for now)
    Route::middleware('permission:expenses.view')->group(function () {
        Route::get('/ads', [AdController::class, 'index'])->name('ads.index');
        Route::get('/ads/export', [AdController::class, 'export'])->name('ads.export');
        Route::get('/ads/create', [AdController::class, 'create'])->middleware('permission:expenses.create')->name('ads.create');
        Route::post('/ads', [AdController::class, 'store'])->middleware('permission:expenses.create')->name('ads.store');
        Route::get('/ads/{ad}/edit', [AdController::class, 'edit'])->middleware('permission:expenses.update')->name('ads.edit');
        Route::put('/ads/{ad}', [AdController::class, 'update'])->middleware('permission:expenses.update')->name('ads.update');
        Route::delete('/ads/{ad}', [AdController::class, 'destroy'])->middleware('permission:expenses.delete')->name('ads.destroy');
    });

    // Facilities Management (using products permissions for now - as it's income)
    Route::middleware('permission:products.view')->group(function () {
        Route::get('/facilities', [FacilityController::class, 'index'])->name('facilities.index');
        Route::get('/facilities/export', [FacilityController::class, 'export'])->name('facilities.export');
        Route::get('/facilities/create', [FacilityController::class, 'create'])->middleware('permission:products.create')->name('facilities.create');
        Route::post('/facilities', [FacilityController::class, 'store'])->middleware('permission:products.create')->name('facilities.store');
        Route::get('/facilities/{facility}/edit', [FacilityController::class, 'edit'])->middleware('permission:products.update')->name('facilities.edit');
        Route::put('/facilities/{facility}', [FacilityController::class, 'update'])->middleware('permission:products.update')->name('facilities.update');
        Route::delete('/facilities/{facility}', [FacilityController::class, 'destroy'])->middleware('permission:products.delete')->name('facilities.destroy');
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
