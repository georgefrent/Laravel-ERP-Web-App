<?php

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Rows;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Orchid\Support\Color;
use App\Models\SupportForm;
use Orchid\Support\Facades\Alert;

class CreateTicketsScreen extends Screen
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
        return 'Crează tichet';
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
                Input::make('subject')
                    ->title('Subiect')
                    ->required()
                    ->placeholder('Introdu subiectul tichetului...'),

                TextArea::make('message')
                    ->title('Mesaj')
                    ->required()
                    ->rows(10)
                    ->placeholder('Scrie aici mesajul...'),

                Button::make('Send')
                    ->method('submitTicket')
                    ->type(Color::PRIMARY),
            ])->title('Completează aici formularul tichetului, iar noi îți vom răspunde în cel mai scurt timp'),

        ];
    }

    /**
     * Handle the form submission.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submitTicket(Request $request)
    {
        $user = Auth::user();

        if ($user && is_null($user->email_verified_at)) {
            Alert::info('Înainte de a continua, vă rugăm să vă verificați adresa de e-mail făcând clic pe linkul pe care l-ați primit în mail.');
            return redirect()->route('platform.main');
        }

        $validatedData = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $ticket = new SupportForm();
        $ticket->user_id = $user->id;
        $ticket->subject = $validatedData['subject'];
        $ticket->message = $validatedData['message'];
        $ticket->status = 'open';
        $ticket->created_at = now();
        $ticket->updated_at = now();
        $ticket->save();

        Toast::info('Tichetul a fost creat cu succes.');

        return redirect()->route('platform.my-tickets');
    }
}
