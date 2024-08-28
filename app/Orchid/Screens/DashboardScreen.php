<?php

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;

use App\Orchid\Layouts\Examples\ChartBarExample;
use App\Orchid\Layouts\Examples\ChartLineExample;
use App\Orchid\Layouts\ChartsLayout;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Components\Cells\Currency;
use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Repository;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;

class DashboardScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        // -------------------------------- Chart

         // Get the current date and the date 7 days ago
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays(6);

        // Query the database to get the unique logins per day for the last 7 days
        $logins = DB::table('user_logins')
            ->select(DB::raw('DATE(login_time) as login_date'), DB::raw('COUNT(DISTINCT user_id) as daily_logins'))
            ->whereBetween('login_time', [$startDate, $endDate])
            ->groupBy('login_date')
            ->orderBy('login_date', 'ASC')
            ->get();

        // Prepare the labels and values
        $labels = [];
        $values = [];

        // Initialize the array with all dates in the range with 0 logins
        for ($date = clone $startDate; $date <= $endDate; $date->addDay()) {
            $labels[$date->format('Y-m-d')] = 0;
        }

        // Populate the logins for each day
        foreach ($logins as $login) {
            $labels[$login->login_date] = $login->daily_logins;
        }

        // Separate keys and values
        $labelsArray = array_keys($labels);
        $valuesArray = array_values($labels);

        // 1. Number of Accounts Created by Clients
        $clientPermissions = '{"platform.systems.attachment":"1","platform.systems.roles":"0","platform.systems.users":"0","platform.index":"1"}';
        $numAccounts = User::where('permissions', $clientPermissions)->count();

        // 2. Number of Open Service Orders
        $openServiceOrders = DB::table('service_orders')
            ->whereIn('status', ['not started', 'in progress'])
            ->count();

        // 3. Number of Open Support Tickets
        $openSupportTickets = DB::table('support_forms')
            ->where('status', 'open')
            ->count();

        return [
            'charts'  => [
                [
                    'name'   => 'Autentificări zilnice',
                    'values' => array_values($labels),
                    'labels' => array_keys($labels),
                ],
            ],

            'metrics' => [
                'accounts'  => ['value' => $numAccounts],
                'service_orders' => ['value' => $openServiceOrders],
                'support_tickets' => ['value' => $openSupportTickets],
            ],
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Dashboard';
    }

    public function permission(): ?iterable
    {
        return [
            'platform.dashboard',
        ];
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::metrics([
                'Conturi create'    => 'metrics.accounts',
                'Comenzi deschise' => 'metrics.service_orders',
                'Tichete deschise' => 'metrics.support_tickets',
            ]),

            ChartsLayout::make('charts', 'Statistici ale autentificărilor din ultima săptămână')
                ->description(''),


        ];
    }
}
