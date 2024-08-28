<?php

declare(strict_types=1);

namespace App\Orchid;

use Orchid\Platform\Dashboard;
use Orchid\Platform\ItemPermission;
use Orchid\Platform\OrchidServiceProvider;
use Orchid\Screen\Actions\Menu;
use Orchid\Support\Color;

class PlatformProvider extends OrchidServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @param Dashboard $dashboard
     *
     * @return void
     */
    public function boot(Dashboard $dashboard): void
    {
        parent::boot($dashboard);

        // ...
    }

    /**
     * Register the application menu.
     *
     * @return Menu[]
     */
    public function menu(): array
    {
        return [
            Menu::make('Pagina principală')
                ->icon('bs.house')
                ->title('Main Navigation')
                ->route('platform.main'),

            Menu::make('Dashboard')
                ->icon('bs.bar-chart')
                ->permission('platform.dashboard')
                ->route('platform.dashboard'),

            Menu::make('Coșul meu')
                ->icon('bs.cart')
                ->route('platform.my-cart')
                ->divider(),

            Menu::make('Comenzile mele')
                ->icon('bs.list-ol')
                ->route('platform.my-orders')
                ->divider()
                ->title('Service Orders'),

            Menu::make('Comenzi de service')
                ->icon('bs.view-list')
                ->permission('platform.service-orders')
                ->route('platform.service-orders'),

            Menu::make('Crează comenzi')
                ->icon('bs.card-text')
                ->permission('platform.create-service-orders')
                ->route('platform.create-service-orders')
                ->divider(),

            Menu::make('Produse')
                ->icon('bs.folder2-open')
                ->route('platform.client-products')
                ->divider()
                ->title('Products'),

            Menu::make('Admin Produse')
                ->icon('bs.view-list')
                ->permission('platform.products')
                ->route('platform.products'),

            Menu::make('Crează produse')
                ->icon('bs.card-text')
                ->permission('platform.create-products')
                ->route('platform.create-products')
                ->divider(),

            Menu::make('Categorii de produse')
                ->icon('bs.view-list')
                ->permission('platform.product-categories')
                ->route('platform.product-categories')
                ->title('Product Categories'),

            Menu::make('Crează categorii de produse')
                ->icon('bs.card-text')
                ->permission('platform.create-product-categories')
                ->route('platform.create-product-categories')
                ->divider(),

            Menu::make('Tichetele mele')
                ->icon('bs.headset')
                ->route('platform.my-tickets')
                ->title('Support'),

            Menu::make('Tichete')
                ->icon('bs.inbox')
                ->permission('platform.tickets')
                ->route('platform.tickets'),

            Menu::make('Crează un tichet')
                ->icon('bs.card-text')
                ->route('platform.create-tickets')
                ->divider(),

            Menu::make(__('Utilizatori'))
                ->icon('bs.people')
                ->route('platform.systems.users')
                ->permission('platform.systems.users')
                ->title(__('Access Controls')),

            Menu::make(__('Roluri'))
                ->icon('bs.shield')
                ->route('platform.systems.roles')
                ->permission('platform.systems.roles')
                ->divider(),

        ];
    }

    /**
     * Register permissions for the application.
     *
     * @return ItemPermission[]
     */
    public function permissions(): array
    {
        return [
            ItemPermission::group(__('System'))
                ->addPermission('platform.systems.roles', __('Roluri'))
                ->addPermission('platform.systems.users', __('Utilizatori')),

            ItemPermission::group(__('Angajați'))
                ->addPermission('platform.dashboard', __('Dashboard'))
                ->addPermission('platform.service-orders', __('Comenzi de service'))
                ->addPermission('platform.create-service-orders', __('Crează comenzi'))
                ->addPermission('platform.products', __('Admin produse'))
                ->addPermission('platform.create-products', __('Crează produse'))
                ->addPermission('platform.product-categories', __('Categorii de produse'))
                ->addPermission('platform.create-product-categories', __('Crează categorii de produse'))
                ->addPermission('platform.tickets', __('Tichete')),
        ];
    }
}
