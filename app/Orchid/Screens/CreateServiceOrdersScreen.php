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

class CreateServiceOrdersScreen extends Screen
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

        // $users = User::all()->pluck('name', 'id');

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
        return 'Crează comandă de service';
    }

    public function permission(): ?iterable
    {
        return [
            'platform.create-service-orders',
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
            Layout::rows([

                Input::make('name')
                    ->title('Nume dispozitiv:')
                    ->placeholder('Enter device name')
                    ->required(),

                Select::make('selected_user')
                    ->options($this->query()['users'])
                    ->title('Selectează client')
                    ->placeholder('Select the customer of the order by email')
                    ->empty('')
                    ->required(),

                Select::make('select')
                    ->title('Status')
                    ->placeholder('Choose the status of the order')
                    ->options([
                        'not_started' => 'Not Started',
                        'in_progress' => 'In Progress',
                        'finished' => 'Finished'
                    ])
                    ->empty('')
                    ->required(),

                Input::make('progress')
                    ->type('number')
                    ->title('Progres')
                    ->placeholder('Enter progress percentage'),

                Input::make('price')
                    ->type('number')
                    ->title('Preț')
                    ->placeholder('Enter the price of the order')
                    ->step(0.01),

                TextArea::make('textarea')
                    ->title('Descriere')
                    ->placeholder('Enter the description of the order')
                    ->rows(10)
                    ->required(),

                Select::make('payment_status')
                    ->title('Statusul plății')
                    ->placeholder('Choose the status of the payment')
                    ->options([
                        'unpaid' => 'Neplătit',
                        'completed' => 'Finalizat'
                    ])
                    ->empty('')
                    ->required(),

                Button::make('Trimite')
                    ->method('buttonClickProcessing')
                    ->type(Color::PRIMARY),

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
        ]);

        // Retrieve the user
        $user = User::find($validated['selected_user']);

        // Create a new service order
        $serviceOrder = new ServiceOrder();
        $serviceOrder->device_name = $validated['name'];
        $serviceOrder->customer_name = $user->email;
        $serviceOrder->status = str_replace('_', ' ', $validated['select']);
        $serviceOrder->progress = $validated['progress'] ?? 0;
        $serviceOrder->price = $validated['price'] ?? 0;
        $serviceOrder->description = $validated['textarea'] ?? '';
        $serviceOrder->payment_status = str_replace('_', ' ', $validated['payment_status']);
        $serviceOrder->entered_at = now();
        $serviceOrder->save();

        // Display a success message
        Alert::info('Comanda de service a fost creată cu succes.');

        return redirect()->route('platform.service-orders');
    }
}
