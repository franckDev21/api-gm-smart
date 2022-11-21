<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\CashierController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CompanyController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\FileController;
use App\Http\Controllers\Api\V1\InvoiceController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ProductSupplierController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ROUTE
Route::group(['prefix' => 'v1'], function () {

    // auth
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);

    Route::post('contact', [UserController::class, 'contact']);

    // company
    Route::post('my/company/{adminUser}', [CompanyController::class, 'store']);
    Route::delete('my/company/{company}', [CompanyController::class, 'destroy']);
    Route::post('my/company/logo/{company}', [CompanyController::class, 'storeLogo']);

    // base65
    Route::post('/base64', [FileController::class, 'generateBase64']);

    // Protected route
    Route::group(['middleware' => ['auth:sanctum', 'active']], function () {

        Route::post('/import/product', [UserController::class, 'importProduct'])->name('import');
        Route::get('/file-import', [UserController::class, 'importView'])->name('import-view');
        Route::get('/export-products', [UserController::class, 'exportUsers'])->name('export-users');

        // admins
        Route::get('admins', [UserController::class, 'getAdminUsers']);
        Route::get('admins/{adminUser}', [UserController::class, 'getAdminUser']);
        Route::delete('admins/{adminUser}', [UserController::class, 'deleteAdminUser']);
        Route::post('admins/{adminUser}/update', [UserController::class, 'updateAdminUser']);
        Route::post('admin-user/toggle-active/{adminUser}', [UserController::class, 'toggleActiveAdminUser']);
        Route::post('/admin/user/create',[UserController::class,'storeAdminUser']);
        // 

        // companies
        Route::get('companies',[CompanyController::class,'allCompaniesOfAdmin']);
        Route::post('admin-user/toggle-active/company/{company}/{adminUser}', [CompanyController::class, 'toggleActiveCompany']);

        // users
        Route::get('users/companies', [UserController::class, 'index']);
        Route::get('users/{user}', [UserController::class, 'show']);
        Route::post('users/{user}/update', [UserController::class, 'update']);
        Route::delete('users/companies/{user}', [UserController::class, 'deleteUserCompany']);
        Route::post('users/companies/toggle-active/{user}', [UserController::class, 'toggleActiveUserCompany']);

        // dashboard
        Route::get('/dashboard', [UserController::class, 'dashboard']);
        Route::get('/dashboard/admin', [UserController::class, 'dashboardAdmin']);
        Route::get('/dashboard/super',[UserController::class,'dashboardSuper']);
        Route::get('/dashboard/company/{company}',[UserController::class,'dashboardCompany']);

        // custumers
        Route::get('customers', [CustomerController::class, 'index']);
        Route::post('customers', [CustomerController::class, 'store']);
        Route::delete('customers/{customer}', [CustomerController::class, 'destroy']);
        Route::get('customer/{customer}', [CustomerController::class, 'show']);
        Route::post('customer/{customer}', [CustomerController::class, 'update']);

        // products
        Route::get('products', [ProductController::class, 'index']);
        Route::post('products', [ProductController::class, 'store']);
        Route::get('product/{product}', [ProductController::class, 'show']);
        Route::post('product/{product}', [ProductController::class, 'update']);
        Route::delete('product/{product}', [ProductController::class, 'destroy']);
        Route::get('products/types', [ProductController::class, 'getTypes']);
        Route::get('products/suppliers', [ProductController::class, 'getSuppliers']);
        Route::post('products/add/{product}/input/supply', [ProductController::class, 'addInput']);
        Route::post('products/add/{product}/output', [ProductController::class, 'addOutput']);

        // orders
        Route::get('orders', [OrderController::class, 'index']);
        Route::post('orders', [OrderController::class, 'store']);
        Route::get('orders/{order}', [OrderController::class, 'show']);
        Route::delete('orders/{order}', [OrderController::class, 'destroy']);
        Route::post('orders/pay/{order}', [OrderController::class, 'payer']);
        Route::get('orders/{order}/invoice', [OrderController::class, 'getInvoice']);

        // invoices
        Route::get('invoices', [InvoiceController::class, 'index']);
        Route::post('invoices/{order}', [OrderController::class, 'invoice']);
        Route::delete('invoices/{invoice}', [InvoiceController::class, 'destroy']);

        // cash
        Route::get('cashiers', [CashierController::class, 'index']);
        Route::get('cashiers/total', [CashierController::class, 'getTotal']);
        Route::post('cashiers', [CashierController::class, 'store']);
        Route::post('cashiers/output', [CashierController::class, 'output']);

        // histoty
        Route::get('history/all', [ProductController::class, 'getAllHistory']);
        Route::get('history/procurement', [ProductController::class, 'getProcurement']);

        // categories
        Route::get('categories', [CategoryController::class, 'index']);
        Route::post('categories', [CategoryController::class, 'store']);
        Route::delete('categories/{category}', [CategoryController::class, 'destroy']);

        // product suppliers
        Route::get('product-suppliers', [ProductSupplierController::class, 'index']);
        Route::post('product-suppliers', [ProductSupplierController::class, 'store']);
        Route::delete('product-suppliers/{productSupplier}', [ProductSupplierController::class, 'destroy']);

        Route::get('my/company/{company}', [CompanyController::class, 'myCompany']);
        Route::post('my/company/picture/{company}', [CompanyController::class, 'updatePictureCompany']);

        Route::post('my/company/update/{company}', [CompanyController::class, 'update']);
        Route::post('user/toggle-active/{user}', [UserController::class, 'toggleActiveUser']);

        // logout
        Route::post('auth/logout', [AuthController::class, 'logout']);

        // users
        Route::get('users', [UserController::class, 'getUsers']);
        Route::post('users/create', [UserController::class, 'store']);

        // get user info
        Route::post('auth/user/info', [AuthController::class, 'getUserInfo']);

        // update user information
        Route::post('auth/user', [UserController::class, 'updateUserInfo']);
        // update password
        Route::post('auth/user/password', [AuthController::class, 'updateUserPassword']);
        // update user picture
        Route::post('auth/user/picture', [UserController::class, 'updateUserPicture']);
    });
});
