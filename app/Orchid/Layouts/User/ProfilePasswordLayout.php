<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Password;
use Orchid\Screen\Layouts\Rows;

class ProfilePasswordLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Password::make('old_password')
                ->placeholder(__('Introduceți parola curentă'))
                ->title(__('Parola curentă'))
                ->help('Aceasta este parola actuală.'),

            Password::make('password')
                ->placeholder(__('Introduceți parola dorită'))
                ->title(__('Parola nouă')),

            Password::make('password_confirmation')
                ->placeholder(__('Introduceți din nou parola dorită'))
                ->title(__('Confirmați noua parolă'))
                ->help('O parolă bună are cel puțin 15 caractere sau cel puțin 8 caractere, inclusiv un număr și o literă mică.'),
        ];
    }
}
