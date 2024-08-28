<?php

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;

use App\Orchid\Layouts\Examples\ExampleElements;
use Orchid\Screen\Action;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Label;
use Orchid\Screen\Fields\Password;
use Orchid\Screen\Fields\Radio;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Support\Color;
use Orchid\Support\Facades\Alert;

use App\Models\ServiceOrder;
use Orchid\Screen\Layouts\Rows;

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

use Orchid\Screen\Sight;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;

class ClientViewOrderScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Vizualizare comandă';
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
        $serviceOrders = \App\Models\ServiceOrder::all();

        $orderIdFromRoute = request()->route('id');

        // Loop through each service order
        foreach ($serviceOrders as $serviceOrder) {
            if ($serviceOrder->id == $orderIdFromRoute) {
                // Access each column and assign to separate variables
                $orderId = $serviceOrder->id;
                $deviceName = $serviceOrder->device_name;
                $customerName = $serviceOrder->customer_name;
                $status = $serviceOrder->status;
                $progress = $serviceOrder->progress;
                $price = $serviceOrder->price;
                $description = $serviceOrder->description;
                $payment_status = $serviceOrder->payment_status;
                $enteredAt = $serviceOrder->entered_at;
                $startedAt = $serviceOrder->started_at;
                $finishedAt = $serviceOrder->finished_at;
                $createdAt = $serviceOrder->created_at;
                $updatedAt = $serviceOrder->updated_at;
            }
        }

        $serviceOrderName = 'Service ' . $deviceName;

        $statusOptions = [
            'not started' => 'Not Started',
            'in progress' => 'In Progress',
            'finished' => 'Finished',
        ];

        $payment_statusOptions = [
            'unpaid' => 'Unpaid',
            'completed' => 'Completed',
        ];

        return [
            Layout::legend('service_order', [
                Sight::make('orderId', 'ID comandă')->render(fn () => $orderId),
                Sight::make('deviceName', 'Nume dispozitiv')->render(fn () => $deviceName),
                Sight::make('status', 'Status')->render(fn () => $statusOptions[$status]),
                Sight::make('progress', 'Progres')->render(fn () => $progress),
                Sight::make('price', 'Preț')->render(fn () => $price . '   RON'),
                Sight::make('description', 'Descriere')->render(fn () => nl2br($description)),
                Sight::make('payment_status', 'Statusul plății')->render(fn () => $payment_statusOptions[$payment_status]),
                Sight::make('enteredAt', 'Intrat la')->render(fn () => $enteredAt),
                Sight::make('startedAt', 'Început la')->render(fn () => $startedAt),
                Sight::make('finishedAt', 'Finalizat la')->render(fn () => $finishedAt),
                Sight::make('action', ' ')->render(fn () => Button::make('Adaugă în coș')
                    ->type(Color::PRIMARY)
                    ->method('addToCart')),
            ])->title($serviceOrderName),
        ];
    }

    public function addToCart(Request $request)
    {

        // Retrieve the route parameter
        $orderIdFromRoute = $request->route('id');

        // Find the existing product
        $serviceOrder = ServiceOrder::findOrFail($orderIdFromRoute);

        $user = Auth::user();

        // Find or create the cart for the user
        $cart = Cart::firstOrCreate(
            ['user_id' => $user->id],
            ['user_name' => $user->name, 'user_email' => $user->email]
        );

        $existingCartItem = CartItem::where('cart_id', $cart->id)
        ->where('order_id', $orderIdFromRoute)
        ->first();

        if ($existingCartItem) {
            // Service order already exists in the cart
            Toast::warning('Această comandă se află deja în coș.');
            return;
        }

        $cart->items()->create([
            'order_id' => $serviceOrder->id,
            'device_name' => $serviceOrder->device_name,
            'price' => $serviceOrder->price,
            'type' => 'serviceOrder',
            'quantity' => 1,
        ]);

        // Update total price
        $cart->total_price = $cart->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });
        $cart->save();

        Toast::info('Comanda a fost adăugată în coș!');
    }
}
