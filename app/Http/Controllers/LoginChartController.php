<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LoginChartController extends Controller
{
    public function query(): iterable
    {
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
        for ($date = $startDate; $date <= $endDate; $date->addDay()) {
            $labels[$date->format('Y-m-d')] = 0;
        }

        // Populate the logins for each day
        foreach ($logins as $login) {
            $labels[$login->login_date] = $login->daily_logins;
        }

        // Separate keys and values
        $labels = array_keys($labels);
        $values = array_values($labels);

        // Return the formatted result
        return [
            'charts' => [
                [
                    'name'   => 'Daily logins',
                    'values' => $values,
                    'labels' => $labels,
                ],
            ],
        ];
    }
}
