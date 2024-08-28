<?php

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;

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

class ServiceOrdersScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {

        // --------------------------------- Table

        // Query the database to get the orders from the service_orders table
        $orders = DB::table('service_orders')
            ->select(
                'id',
                'device_name',
                'customer_name',
                'status',
                'progress',
                'price',
                'description',
                'payment_status',
                'entered_at',
                'started_at',
                'finished_at'
            )
            ->get();

        // Format the orders into the desired structure
        $orderData = [];
        foreach ($orders as $order) {
            $orderData[] = new Repository([
                'id' => $order->id,
                'device_name' => $order->device_name,
                'customer_name' => $order->customer_name,
                'status' => $order->status,
                'progress' => $order->progress,
                'price' => $order->price,
                'description' => $order->description,
                'payment_status' => $order->payment_status,
                'entered_at' => $order->entered_at,
                'started_at' => $order->started_at,
                'finished_at' => $order->finished_at,
            ]);
        }

        return [
            'table' => $orderData,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Comenzi de service';
    }

    public function permission(): ?iterable
    {
        return [
            'platform.service-orders',
        ];
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::table('table', [
                TD::make('edit', '')
                    ->width('50')
                    ->render(fn (Repository $model) =>
                        '<a href="' . route('platform.service-order.edit', $model->get('id')) . '" class="btn btn-warning center-text">Edit</a>'
                    ),
                TD::make('id', 'ID')
                    ->width('50')
                    ->render(fn (Repository $model) => $model->get('id'))
                    ->sort(),

                TD::make('device_name', 'Nume dispozitiv')
                    ->width('150')
                    ->render(fn (Repository $model) => Str::limit($model->get('device_name'), 50))
                    ->sort(),

                TD::make('customer_name', 'Nume client')
                    ->width('150')
                    ->render(fn (Repository $model) => Str::limit($model->get('customer_name'), 50))
                    ->sort(),

                TD::make('status', 'Status')
                    ->width('100')
                    ->render(fn (Repository $model) => ucfirst($model->get('status')))
                    ->sort(),

                TD::make('status_color', '')
                    ->width('50')
                    ->render(function (Repository $model) {
                        $status = $model->get('status');
                        $color = match ($status) {
                            'not started' => 'red',
                            'in progress' => 'orange',
                            'finished' => 'green',
                            default => 'grey',
                        };

                        return "<div style='width: 100%; height: 20px; background-color: {$color};'></div>";
                    }),

                TD::make('progress', 'Progres (%)')
                    ->width('100')
                    ->align(TD::ALIGN_RIGHT)
                    ->render(fn (Repository $model) => $model->get('progress') . '%')
                    ->sort(),

                TD::make('price', 'Preț (RON)')
                    ->width('150')
                    ->render(fn (Repository $model) => number_format($model->get('price'), 2) . ' RON')
                    ->align(TD::ALIGN_RIGHT)
                    ->sort(),

                TD::make('description', 'Descriere')
                    ->width('200')
                    ->render(fn (Repository $model) => Str::limit($model->get('description'), 100)),

                TD::make('payment_status', 'Statusul plății')
                    ->width('100')
                    ->render(fn (Repository $model) => ucfirst($model->get('payment_status')))
                    ->sort(),

                TD::make('entered_at', 'Intrată la')
                    ->width('200')
                    ->render(fn (Repository $model) => $model->get('entered_at'))
                    ->sort(),

                TD::make('started_at', 'Începută la')
                    ->width('200')
                    ->render(fn (Repository $model) => $model->get('started_at') ? $model->get('started_at') : 'Not Started')
                    ->sort(),

                TD::make('finished_at', 'Finalizată la')
                    ->width('200')
                    ->render(fn (Repository $model) => $model->get('finished_at') ? $model->get('finished_at') : 'Not Finished')
                    ->sort(),

                ]),
        ];
    }
}
