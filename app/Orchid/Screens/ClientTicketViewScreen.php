<?php

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;

use App\Models\SupportForm;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\TD;
use Orchid\Screen\Fields\Group;
use Illuminate\Http\Request;
use Orchid\Support\Facades\Toast;

use App\Models\User;
use App\Models\SupportReply;
use Orchid\Screen\Fields\Quill;
use Orchid\Screen\Layouts\Rows;
use Illuminate\Support\Facades\Auth;
use Orchid\Support\Color;
use Illuminate\Support\Facades\Mail;


class ClientTicketViewScreen extends Screen
{
    public $ticket;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(SupportForm $supportForm): array
    {
        $this->ticket = $supportForm;
        $creator = User::find($supportForm->user_id);

        return [
            'ticket' => $supportForm,
            'creator_name' => $creator->name ?? 'Unknown',
            'replies' => $supportForm->replies()->with('user')->get(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Vizualizare tichet';
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
        $ticketStatus = $this->ticket->status;

        return [
            Layout::rows([
                Input::make('ticket.creator')
                    ->title('Creator')
                    ->value($this->query($this->ticket)['creator_name'])
                    ->readonly(),

                Input::make('ticket.subject')
                    ->title('Subiect')
                    ->readonly(),

                TextArea::make('ticket.message')
                    ->title('Mesaj')
                    ->value($this->ticket->message)
                    ->add('custom-disabled')
                    ->rows(10)
                    ->disabled(),
            ]),

            $this->generateRepliesLayout($this->query($this->ticket)['replies']),

            Layout::rows([
                TextArea::make('reply.message')
                    ->title('Răspunsul tău')
                    ->rows(10)
                    ->disabled($ticketStatus === 'closed'),

                Button::make('Trimite răspuns')
                    ->type(Color::PRIMARY)
                    ->method('sendReply')
            ]),
        ];
    }

     /**
     * Generate the layout for displaying replies.
     */
    private function generateRepliesLayout($replies): Rows
    {
        $replyFields = [];

        foreach ($replies as $reply) {
            $replyFields[] = Input::make('reply.user')
                ->title('Utilizator')
                ->value($reply->user->name)
                ->readonly();

            $replyFields[] = TextArea::make('reply.message')
                ->title('Mesaj')
                ->rows(10)
                ->value($reply->message)
                ->readonly();
        }

        return Layout::rows($replyFields)->canSee(count($replies) > 0);
    }

    /**
     * Handle reply submission.
     */
    public function sendReply(Request $request, SupportForm $supportForm)
    {
        $request->validate([
            'reply.message' => 'required',
        ]);

        $reply = new SupportReply([
            'user_id' => Auth::id(),
            'message' => $request->input('reply.message'),
        ]);

        $supportForm->replies()->save($reply);

        Toast::info('Răspuns adăugat cu succes!');

        return redirect()->route('platform.client.ticket.view', $supportForm);
    }
}
