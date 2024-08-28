<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Illuminate\Http\RedirectResponse;

use App\Orchid\Layouts\Examples\ExampleElements;
use Orchid\Screen\Action;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Label;
use Orchid\Screen\Fields\Map;
use Orchid\Screen\Fields\Quill;
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
use Orchid\Support\Facades\Toast;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use Orchid\Screen\Sight;

class PlatformScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'location' => [
                'lat' => 45.747319375507985,
                'lng' => 21.226783292952625,
            ],
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'S.C. Licență S.R.L.';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'S.C. Licență S.R.L. a deschis ușa clienților în anul 2024 in Timișoara. Ne ocupăm de reparații electronice de orice fel, în special reparații de televizoare, calculatoare, telefoane, la prețuri mici și într-un timp scurt.';
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
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        $user = Auth::user();

        $username = $user->name;

        if ($user && is_null($user->email_verified_at)) {
            Alert::info('Înainte de a continua, vă rugăm să vă verificați adresa de e-mail făcând clic pe linkul pe care l-ați primit în mail.');
        }

        Toast::info('Bine ați venit, ' . $username);

        return [

            Layout::legend('contact', [
                Sight::make('supportEmail', 'Adresă de email')->render(fn () => 'support@atelier.ro'),
                Sight::make('supportPhone', 'Număr de telefon')->render(fn () => '0356 000 000'),
                Sight::make('')->render(fn () => Button::make('Deschide un tichet')
                    ->method('openCreateTicket')
                    ->type(Color::INFO)),

            ])->title('Contact'),

            Layout::rows([

                Map::make('location')
                    ->title('Timișoara, România')
                    ->help('Introdu coordonatele sau folosește-te de search'),

            ])->title('Unde ne găsiți'),

        ];
    }

    public function openCreateTicket(Request $request)
    {
        return redirect()->route('platform.create-tickets');
    }
}
