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

class TicketViewScreen extends Screen
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
        return 'Vizualizare tichet - admin';
    }

    public function permission(): ?iterable
    {
        return [
            'platform.tickets',
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
                    ->canSee($this->ticket->status !== 'closed'),

                Input::make()
                    ->hidden(),

                Input::make()
                    ->hidden(),

                Button::make('Închide tichet')
                    ->method('closeTicket')
                    ->type(Color::DANGER)
                    ->canSee($this->ticket->status !== 'closed'),
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
                ->title('Nume')
                ->value($reply->user->name)
                ->readonly();

            $replyFields[] = TextArea::make('reply.message')
                ->title('Mesaj')
                ->rows(10)
                ->value($reply->message)
                ->readonly();

            $replyFields[] = Button::make('Șterge răspunsul')
                ->method('deleteReply')
                ->parameters(['reply_id' => $reply->id])
                ->type(Color::DANGER);
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
        ], [
            'reply.message.required' => 'Reply message cannot be empty.',
        ]);

        $creator = User::find($supportForm->user_id);
        $creator_email = $creator->email;

        $reply = new SupportReply([
            'user_id' => Auth::id(),
            'message' => $request->input('reply.message'),
        ]);

        $supportForm->replies()->save($reply);

        Toast::info('Răspuns adăugat cu succes!');

        $subject = 'Unui tichet creat de dumneavoastră i-a fost adăugat un răspuns';
        $message = 'Buna ziua, Unui tichet creat de dumneavoastră în aplicația atelierului S.C. Licență S.R.L. i-a fost adăugat un răspuns. Vă așteptăm în aplicație pentru a vizualiza răspunsul.';

        Mail::raw($message, function ($mail) use ($creator_email, $subject) {
            $mail->to($creator_email)
                ->subject($subject);
        });

        return redirect()->route('platform.ticket.view', $supportForm);
    }

    public function deleteReply(Request $request, SupportForm $supportForm)
    {
        $reply = SupportReply::findOrFail($request->input('reply_id'));
        $reply->delete();

        Toast::info('Răspunsul a fost șters cu succes!');

        return redirect()->route('platform.ticket.view', $supportForm);
    }

    /**
     * Handle closing the ticket.
     */
    public function closeTicket(SupportForm $supportForm)
    {
        $supportForm->update(['status' => 'closed']);

        Toast::info('Tichet închis cu succes!');

        return redirect()->route('platform.ticket.view', $supportForm);
    }
}
