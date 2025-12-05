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
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;  
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AdController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\MembershipPackageController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminController;    
use App\Http\Controllers\Kelas\KelasController;
use App\Http\Controllers\Kelas\KelasRegistrationController;
use App\Http\Controllers\Kelas\KelasPaymentController;
use App\Http\Controllers\Kelas\KelasExpenseController;
use App\Http\Controllers\Kelas\KelasReportController;
use App\Http\Controllers\Kelas\KelasMemberController;
use App\Http\Controllers\Kelas\KelasCheckinController;




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
        
        // Stock opname moved to products
        Route::post('/products/stock-opname', [ProductController::class, 'stockOpname'])->name('products.stock-opname');
        Route::get('/products/{product}/movements', [ProductController::class, 'getStockMovements'])->name('products.movements');
        Route::get('/products/{product}/units', [ProductController::class, 'getAvailableUnits'])->name('products.units');
        
        // Inventory movements (moved from StockController)
        Route::get('/inventory/movements', [ProductController::class, 'movements'])->name('stock.movements');
        
        // Admin Panel - User Management
        Route::get('/admin/users', [AdminController::class, 'index'])->name('admin.users.index');
        Route::get('/admin/users/create', [AdminController::class, 'create'])->name('admin.users.create');
        Route::post('/admin/users', [AdminController::class, 'store'])->name('admin.users.store');
        Route::get('/admin/users/{user}/edit', [AdminController::class, 'edit'])->name('admin.users.edit');
        Route::put('/admin/users/{user}', [AdminController::class, 'update'])->name('admin.users.update');
        Route::delete('/admin/users/{user}', [AdminController::class, 'destroy'])->name('admin.users.destroy');
        Route::get('/admin/users/{user}/transactions', [AdminController::class, 'userTransactions'])->name('admin.users.transactions');
        Route::get('/admin/users/{user}/permissions', [AdminController::class, 'showPermissions'])->name('admin.users.permissions');
        Route::post('/admin/users/{user}/permissions', [AdminController::class, 'updatePermissions'])->name('admin.users.update-permissions');
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

    // Purchase Management
    Route::middleware('permission:purchases.view')->group(function () {
        Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
    });
    
    Route::middleware('permission:purchases.create')->group(function () {
        Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
        Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store');
    });
    
    Route::middleware('permission:purchases.view')->group(function () {
        Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');
    });
    
    Route::middleware('permission:purchases.create')->group(function () {
        Route::post('/purchases/{purchase}/payments', [PurchaseController::class, 'storePayment'])->name('purchases.payments.store');
    });
    
    Route::middleware('permission:purchases.delete')->group(function () {
        Route::delete('/purchases/{purchase}', [PurchaseController::class, 'destroy'])->name('purchases.destroy');
    });
    // Member Management
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
        Route::get('/members/{member}/renew', [MemberController::class, 'renew'])->name('members.renew');
        Route::post('/members/{member}/renew', [MemberController::class, 'processRenewal'])->name('members.renew.process');
    });

    Route::middleware('permission:members.delete')->group(function () {
        Route::delete('/members/{member}', [MemberController::class, 'destroy'])->name('members.destroy');
    });

    // Membership Packages
    Route::middleware('permission:packages.view')->group(function () {
        Route::get('/packages', [MembershipPackageController::class, 'index'])->name('packages.index');
    });

    Route::middleware('permission:packages.create')->group(function () {
        Route::get('/packages/create', [MembershipPackageController::class, 'create'])->name('packages.create');
        Route::post('/packages', [MembershipPackageController::class, 'store'])->name('packages.store');
    });

    Route::middleware('permission:packages.update')->group(function () {
        Route::get('/packages/{package}/edit', [MembershipPackageController::class, 'edit'])->name('packages.edit');
        Route::put('/packages/{package}', [MembershipPackageController::class, 'update'])->name('packages.update');
    });

    Route::middleware('permission:packages.delete')->group(function () {
        Route::delete('/packages/{package}', [MembershipPackageController::class, 'destroy'])->name('packages.destroy');
    });

    // Attendance Management
    Route::middleware('permission:attendance.checkin')->group(function () {
        Route::get('/attendance/checkin', [AttendanceController::class, 'checkin'])->name('attendance.checkin');
        Route::post('/attendance/checkin', [AttendanceController::class, 'store'])->name('attendance.store');
        Route::post('/attendance/{attendance}/checkout', [AttendanceController::class, 'checkout'])->name('attendance.checkout');
    });

    Route::middleware('permission:attendance.view')->group(function () {
        Route::get('/attendance/history', [AttendanceController::class, 'index'])->name('attendance.history');
    });

    Route::middleware('permission:attendance.reports')->group(function () {
        Route::get('/reports/attendance', [ReportController::class, 'attendance'])->name('reports.attendance');
    });

    // API Routes for attendance (member search)
    Route::get('/api/members/search', [AttendanceController::class, 'searchMembers'])->name('api.members.search');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Kelas Module
    Route::prefix('kelas')->group(function () {
        // Specific routes FIRST (before wildcard routes)
        Route::get('/', [KelasController::class, 'index'])->middleware('permission:kelas.view')->name('kelas.index');
        Route::get('/create', [KelasController::class, 'create'])->middleware('permission:kelas.manage')->name('kelas.create');
        Route::post('/', [KelasController::class, 'store'])->middleware('permission:kelas.manage')->name('kelas.store');
        
        // Registrations
        Route::get('/registrations', [KelasRegistrationController::class, 'index'])->middleware('permission:kelas.registration.manage')->name('kelas.registrations.index');
        Route::post('/registrations', [KelasRegistrationController::class, 'store'])->middleware('permission:kelas.registration.manage')->name('kelas.registrations.store');
        Route::delete('/registrations/{registration}', [KelasRegistrationController::class, 'destroy'])->middleware('permission:kelas.registration.manage')->name('kelas.registrations.destroy');

        // Payments
        Route::get('/payments', [KelasPaymentController::class, 'index'])->middleware('permission:kelas.payment.manage')->name('kelas.payments.index');
        Route::post('/payments', [KelasPaymentController::class, 'store'])->middleware('permission:kelas.payment.manage')->name('kelas.payments.store');

        // Expenses
        Route::get('/expenses', [KelasExpenseController::class, 'index'])->middleware('permission:kelas.expense.manage')->name('kelas.expenses.index');
        Route::post('/expenses', [KelasExpenseController::class, 'store'])->middleware('permission:kelas.expense.manage')->name('kelas.expenses.store');

        // Reports
        Route::get('/reports', [KelasReportController::class, 'index'])->middleware('permission:kelas.report.view')->name('kelas.reports.index');

        // Kelas Members
        Route::get('/members', [KelasMemberController::class, 'index'])->middleware('permission:kelas.registration.manage')->name('kelas.members.index');
        Route::get('/members/create', [KelasMemberController::class, 'create'])->middleware('permission:kelas.registration.manage')->name('kelas.members.create');
        Route::post('/members', [KelasMemberController::class, 'store'])->middleware('permission:kelas.registration.manage')->name('kelas.members.store');
        Route::get('/members/{member}', [KelasMemberController::class, 'show'])->middleware('permission:kelas.registration.manage')->name('kelas.members.show');
        Route::get('/members/{member}/edit', [KelasMemberController::class, 'edit'])->middleware('permission:kelas.registration.manage')->name('kelas.members.edit');
        Route::put('/members/{member}', [KelasMemberController::class, 'update'])->middleware('permission:kelas.registration.manage')->name('kelas.members.update');
        Route::delete('/members/{member}', [KelasMemberController::class, 'destroy'])->middleware('permission:kelas.registration.manage')->name('kelas.members.destroy');

        // Checkin/Attendance
        Route::get('/checkin', [KelasCheckinController::class, 'index'])->name('kelas.checkin.index');
        Route::post('/checkin', [KelasCheckinController::class, 'store'])->name('kelas.checkin.store');
        Route::put('/checkin/{attendance}/checkout', [KelasCheckinController::class, 'checkout'])->name('kelas.checkin.checkout');
        Route::get('/checkin/history', [KelasCheckinController::class, 'history'])->name('kelas.checkin.history');
        Route::get('/checkin/search-members', [KelasCheckinController::class, 'searchMembers'])->name('kelas.checkin.search-members');
        
        // Wildcard routes LAST (after all specific routes)
        Route::get('/{kelas}', [KelasController::class, 'show'])->middleware('permission:kelas.view')->name('kelas.show');
        Route::get('/{kelas}/edit', [KelasController::class, 'edit'])->middleware('permission:kelas.manage')->name('kelas.edit');
        Route::put('/{kelas}', [KelasController::class, 'update'])->middleware('permission:kelas.manage')->name('kelas.update');
        Route::delete('/{kelas}', [KelasController::class, 'destroy'])->middleware('permission:kelas.manage')->name('kelas.destroy');
    });
});

require __DIR__.'/auth.php';
