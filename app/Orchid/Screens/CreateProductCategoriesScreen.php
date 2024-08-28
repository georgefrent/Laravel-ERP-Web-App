<?php

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;

use App\Models\Category;
use App\Models\Product;

use App\Orchid\Layouts\Examples\ExampleElements;
use Orchid\Screen\Action;
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

class CreateProductCategoriesScreen extends Screen
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
        return 'Crează categorie de produs';
    }

    public function permission(): ?iterable
    {
        return [
            'platform.create-product-categories',
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

                Input::make('category_name')
                    ->title('Numele categoriei')
                    ->placeholder('Enter the name of the category')
                    ->required(),

                TextArea::make('textarea')
                    ->title('Descriere')
                    ->placeholder('Enter the description of the category')
                    ->rows(10),

                Button::make('Trimite')
                    ->method('buttonClickProcessing')
                    ->type(Color::PRIMARY),

            ])->title('Formular categorie de produs'),
        ];
    }
    public function buttonClickProcessing(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'category_name' => 'required|string|max:255',
            'textarea' => 'nullable|string',
        ]);

        $existingCategory = Category::where('category_name', $validated['category_name'])->first();

        if ($existingCategory) {
            // Category already exists, show an alert and return
            Alert::warning('Categoria există deja.');

            return back();
        }

        // Create a new category
        $category = new Category();
        $category->category_name = $validated['category_name'];
        $category->category_description = $validated['textarea'] ?? '';
        $category->save();

        Alert::info('Categoria de produs a fost creată cu succes.');

        return redirect()->route('platform.product-categories');
    }
}
