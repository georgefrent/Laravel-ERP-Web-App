<?php

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;

use App\Models\SupportForm;
use App\Models\User;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Components\Cells\Currency;
use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Repository;
use Orchid\Support\Facades\Toast;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class MyTicketsScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $user = Auth::user();

        $tickets = DB::table('support_forms')
            ->where('user_id', $user->id)
            ->select(
                'support_forms.id',
                'support_forms.user_id',
                'support_forms.subject',
                'support_forms.message',
                'support_forms.status',
                'support_forms.created_at',

            )
            ->get();


        $ticketData = [];
        foreach ($tickets as $ticket) {
            $user = User::find($ticket->user_id);
            $ticketData[] = new Repository([
                'id' => $ticket->id,
                'user_id' => $ticket->user_id,
                'user_name' => $user ? $user->name : 'Unknown',
                'user_email' => $user ? $user->email : 'Unknown',
                'subject' => $ticket->subject,
                'message' => $ticket->message,
                'status' => $ticket->status,
                'created_at' => $ticket->created_at,
            ]);
        }

        return [
            'table' => $ticketData,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Tichetele mele';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make('Crează tichet')
            ->icon('plus')
            ->route('platform.create-tickets'),
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
            Layout::table('table', [
                TD::make('view', '')
                    ->width('50')
                    ->render(fn (Repository $model) =>
                        '<a href="' . route('platform.client.ticket.view', $model->get('id')) . '" class="btn btn-default center-text">View</a>'
                    ),

                TD::make('id', 'ID tichet')
                    ->width('50')
                    ->render(fn (Repository $model) => $model->get('id'))
                    ->sort(),

                TD::make('name', 'Nume client')
                    ->width('50')
                    ->render(fn (Repository $model) => $model->get('user_name'))
                    ->sort(),

                TD::make('email', 'E-mail client')
                    ->width('100')
                    ->render(fn (Repository $model) => $model->get('user_email'))
                    ->sort(),

                TD::make('subject', 'Subiect')
                    ->width('100')
                    ->render(fn (Repository $model) => $model->get('subject'))
                    ->sort(),

                TD::make('status', 'Status')
                    ->width('50')
                    ->render(fn (Repository $model) => $model->get('status'))
                    ->sort(),

                TD::make('status_color', '')
                    ->width('20 ')
                    ->render(function (Repository $model) {
                        $status = $model->get('status');
                        $color = match ($status) {
                            'open' => 'green',
                            'closed' => 'red',
                            default => 'grey',
                        };

                        return "<div style='width: 100%; height: 20px; background-color: {$color};'></div>";
                    }),

                TD::make('created_at', 'Creat la')
                    ->width('60')
                    ->render(fn (Repository $model) => $model->get('created_at'))
                    ->sort(),
            ]),
        ];
    }
}
