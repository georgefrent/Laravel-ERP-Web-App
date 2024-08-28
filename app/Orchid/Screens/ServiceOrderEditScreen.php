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
use Orchid\Screen\Fields\DateTimer;

class ServiceOrderEditScreen extends Screen
{

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {

        $requiredPermissions = '{"platform.systems.attachment":"1","platform.systems.roles":"0","platform.systems.users":"0","platform.index":"1"}';
        $users = User::where('permissions', $requiredPermissions)->pluck('email', 'id');


        return [
            'users' => $users,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Editează comanda de service';
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
        return [
            Layout::rows([

                Input::make('name')
                    ->title('Nume dispozitiv:')
                    ->placeholder('Introdu numele dispozitivului')
                    ->value($deviceName)
                    ->required(),

                Select::make('selected_user')
                    ->options($this->query()['users'])
                    ->title('Selectează clientul')
                    ->placeholder('Selectează clientul comenzii în funcție de e-mail')
                    ->empty('')
                    ->help($customerName)
                    ->required(),

                Select::make('select')
                    ->title('Status')
                    ->placeholder('Alege statusul comenzii')
                    ->options([
                        'not_started' => 'Not Started',
                        'in_progress' => 'In Progress',
                        'finished' => 'Finished'
                    ])
                    ->help($status)
                    ->empty('')
                    ->required(),

                Input::make('progress')
                    ->type('number')
                    ->title('Progres')
                    ->value($progress)
                    ->placeholder('Introdu procentajul progresului'),

                Input::make('price')
                    ->type('number')
                    ->title('Preț')
                    ->value($price)
                    ->placeholder('Introdu prețul comenzii')
                    ->step(0.01),

                TextArea::make('textarea')
                    ->title('Descriere')
                    ->value($description)
                    ->placeholder('Introdu descrierea comenzii')
                    ->rows(10)
                    ->required(),

                Select::make('payment_status')
                    ->title('Statusul plății')
                    ->placeholder('Alege statusul plății')
                    ->options([
                        'unpaid' => 'Unpaid',
                        'completed' => 'Completed'
                    ])
                    ->help($payment_status)
                    ->empty('')
                    ->required(),

                DateTimer::make('startedAt_datetime')
                    ->title('Data & ora la care comanda a început')
                    ->allowInput()
                    ->format24hr()
                    ->enableTime()
                    ->value($startedAt)
                    ->placeholder('Selectează ora & data de start')
                    ->serverFormat('Y-m-d H:i:s'),

                DateTimer::make('finishedAt_datetime')
                    ->title('Data & ora la care comanda a fost finalizată')
                    ->allowInput()
                    ->format24hr()
                    ->enableTime()
                    ->value($finishedAt)
                    ->placeholder('Selectează ora & data de finalizare')
                    ->serverFormat('Y-m-d H:i:s'),
                Group::make([
                    Button::make('Trimite')
                        ->method('buttonClickProcessing')
                        ->type(Color::PRIMARY),

                    Button::make('Șterge')
                        ->method('delete')
                        ->type(Color::DANGER)
                        ->confirm('Ești sigur că vrei să ștergi această comandă ?'),
                ]),

            ])->title('Formular comandă'),
        ];
    }

    public function buttonClickProcessing(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'selected_user' => 'required|exists:users,id',
            'select' => 'required|in:not_started,in_progress,finished',
            'progress' => 'nullable|integer|min:0|max:100',
            'price' => 'nullable|numeric|min:0',
            'textarea' => 'nullable|string',
            'payment_status' => 'required|in:unpaid,completed',
            'startedAt_datetime' => 'nullable|date_format:Y-m-d H:i:s',
            'finishedAt_datetime' => 'nullable|date_format:Y-m-d H:i:s',
        ]);

        // Retrieve the route parameter
        $orderIdFromRoute = $request->route('id');

        // Find the existing service order
        $serviceOrder = ServiceOrder::findOrFail($orderIdFromRoute);

        // Retrieve the user
        $user = User::find($validated['selected_user']);

        // Update the service order
        $serviceOrder->device_name = $validated['name'];
        $serviceOrder->customer_name = $user->email;
        $serviceOrder->status = str_replace('_', ' ', $validated['select']);
        $serviceOrder->progress = $validated['progress'] ?? 0;
        $serviceOrder->price = $validated['price'] ?? 0;
        $serviceOrder->description = $validated['textarea'] ?? '';
        $serviceOrder->payment_status = str_replace('_', ' ', $validated['payment_status']);
        $serviceOrder->entered_at = $serviceOrder->entered_at;
        $serviceOrder->started_at = $validated['startedAt_datetime'] ?? $serviceOrder->started_at;
        $serviceOrder->finished_at = $validated['finishedAt_datetime'] ?? $serviceOrder->finished_at;
        $serviceOrder->save();

        // Display a success message
        Alert::info('Comanda de service a fost actualizată cu succes.');

        return redirect()->route('platform.service-orders');
    }

    public function delete(Request $request)
    {
        // Retrieve the route parameter
        $orderIdFromRoute = $request->route('id');

        // Find the existing service order
        $serviceOrder = ServiceOrder::findOrFail($orderIdFromRoute);

        // Delete the service order
        $serviceOrder->delete();

        // Display a success message
        Alert::info('Comanda de service a fost ștearsă cu succes.');

        return redirect()->route('platform.service-orders');
    }
}
