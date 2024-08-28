<?php

declare(strict_types=1);
use App\Orchid\Screens\ClientTicketViewScreen;
use App\Orchid\Screens\TicketViewScreen;
use App\Orchid\Screens\CreateTicketsScreen;
use App\Orchid\Screens\TicketsScreen;
use App\Orchid\Screens\MyTicketsScreen;
use App\Orchid\Screens\CartScreen;
use App\Orchid\Screens\ClientViewOrderScreen;
use App\Orchid\Screens\ClientOrdersScreen;
use App\Orchid\Screens\ClientViewProductScreen;
use App\Orchid\Screens\ClientProductsScreen;
use App\Orchid\Screens\ProductCategoriesEditScreen;
use App\Orchid\Screens\ProductCategoriesScreen;
use App\Orchid\Screens\CreateProductCategoriesScreen;
use App\Orchid\Screens\ProductEditScreen;
use App\Orchid\Screens\ProductsScreen;
use App\Orchid\Screens\CreateProductsScreen;
use App\Orchid\Screens\ServiceOrderEditScreen;
use App\Orchid\Screens\ServiceOrdersScreen;
use App\Orchid\Screens\CreateServiceOrdersScreen;
use App\Orchid\Screens\DashboardScreen;
use App\Orchid\Screens\PlatformScreen;
use App\Orchid\Screens\Role\RoleEditScreen;
use App\Orchid\Screens\Role\RoleListScreen;
use App\Orchid\Screens\User\UserEditScreen;
use App\Orchid\Screens\User\UserListScreen;
use App\Orchid\Screens\User\UserProfileScreen;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;

/*
|--------------------------------------------------------------------------
| Dashboard Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the need "dashboard" middleware group. Now create something great!
|
*/

// Client View Ticket
Route::screen('ticket/client/{supportForm}', ClientTicketViewScreen::class)
    ->name('platform.client.ticket.view');

// View Ticket
Route::screen('ticket/{supportForm}', TicketViewScreen::class)
    ->name('platform.ticket.view');

// Create tickets
Route::screen('/create-tickets', CreateTicketsScreen::class)
->name('platform.create-tickets');

// Tickets Inbox for employees
Route::screen('/tickets', TicketsScreen::class)
    ->name('platform.tickets');

// Client Tickets
Route::screen('/my-tickets', MyTicketsScreen::class)
    ->name('platform.my-tickets');

// Client Cart
Route::screen('/my-cart', CartScreen::class)
    ->name('platform.my-cart');

// Client View Order
Route::screen('order/view/{id}', ClientViewOrderScreen::class)
    ->name('platform.order.view');

// Client Orders
Route::screen('/my-orders', ClientOrdersScreen::class)
    ->name('platform.my-orders');

// Client View Products
Route::screen('product/view/{id}', ClientViewProductScreen::class)
    ->name('platform.product.view');

// Client Products
Route::screen('/client-products', ClientProductsScreen::class)
    ->name('platform.client-products');

// Product Categories Edit
Route::screen('product-categories/edit/{id}', ProductCategoriesEditScreen::class)
    ->name('platform.product-categories.edit');

// Product categories
Route::screen('/product-categories', ProductCategoriesScreen::class)
    ->name('platform.product-categories');

// Create product categories
Route::screen('/create-product-categories', CreateProductCategoriesScreen::class)
    ->name('platform.create-product-categories');

// Product Edit
Route::screen('product/edit/{id}', ProductEditScreen::class)
    ->name('platform.product.edit');

// Products
Route::screen('/products', ProductsScreen::class)
    ->name('platform.products');

// Create Products
Route::screen('/create-products', CreateProductsScreen::class)
    ->name('platform.create-products');

// Service Order Edit
Route::screen('service-order/edit/{id}', ServiceOrderEditScreen::class)
    ->name('platform.service-order.edit');

// Service Orders
Route::screen('/service-orders', ServiceOrdersScreen::class)
    ->name('platform.service-orders');

// Create Service Orders
Route::screen('/create-service-orders', CreateServiceOrdersScreen::class)
    ->name('platform.create-service-orders');

// Dashboard
Route::screen('/dashboard', DashboardScreen::class)
    ->name('platform.dashboard');

// Main
Route::screen('/main', PlatformScreen::class)
    ->name('platform.main');

// Platform > Profile
Route::screen('profile', UserProfileScreen::class)
    ->name('platform.profile')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Profile'), route('platform.profile')));

// Platform > System > Users > User
Route::screen('users/{user}/edit', UserEditScreen::class)
    ->name('platform.systems.users.edit')
    ->breadcrumbs(fn (Trail $trail, $user) => $trail
        ->parent('platform.systems.users')
        ->push($user->name, route('platform.systems.users.edit', $user)));

// Platform > System > Users > Create
Route::screen('users/create', UserEditScreen::class)
    ->name('platform.systems.users.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.systems.users')
        ->push(__('Create'), route('platform.systems.users.create')));

// Platform > System > Users
Route::screen('users', UserListScreen::class)
    ->name('platform.systems.users')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Users'), route('platform.systems.users')));

// Platform > System > Roles > Role
Route::screen('roles/{role}/edit', RoleEditScreen::class)
    ->name('platform.systems.roles.edit')
    ->breadcrumbs(fn (Trail $trail, $role) => $trail
        ->parent('platform.systems.roles')
        ->push($role->name, route('platform.systems.roles.edit', $role)));

// Platform > System > Roles > Create
Route::screen('roles/create', RoleEditScreen::class)
    ->name('platform.systems.roles.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.systems.roles')
        ->push(__('Create'), route('platform.systems.roles.create')));

// Platform > System > Roles
Route::screen('roles', RoleListScreen::class)
    ->name('platform.systems.roles')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Roles'), route('platform.systems.roles')));

//Route::screen('idea', Idea::class, 'platform.screens.idea');
