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

use App\Models\Product;
use App\Models\Category;
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

class ProductCategoriesEditScreen extends Screen
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
        return 'Editează categoria de produs';
    }

    public function permission(): ?iterable
    {
        return [
            'platform.product-categories',
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
        $categories = \App\Models\Category::all();

        $orderIdFromRoute = request()->route('id');

        // Loop through each category
        foreach ($categories as $category) {
            if ($category->category_id == $orderIdFromRoute) {
                // Access each column and assign to separate variables
                $category_id = $category->category_id;
                $category_name = $category->category_name;
                $category_description = $category->category_description;
            }
        }
        return [
            Layout::rows([

                Input::make('category_name')
                    ->title('Numele categoriei:')
                    ->placeholder('Introdu numele categoriei...')
                    ->value($category_name)
                    ->required(),

                TextArea::make('textarea')
                    ->title('Descrierea categoriei')
                    ->value($category_description)
                    ->placeholder('Introdu descrierea categoriei...')
                    ->rows(10),

                Group::make([
                    Button::make('Trimite')
                        ->method('buttonClickProcessing')
                        ->type(Color::PRIMARY),

                    Button::make('Șterge')
                        ->method('delete')
                        ->type(Color::DANGER)
                        ->confirm('Ești sigur că vrei să ștergi această categorie?'),
                ]),

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

        // Retrieve the route parameter
        $orderIdFromRoute = $request->route('id');

        // Find the existing service order
        $category = Category::findOrFail($orderIdFromRoute);

        // Update the product category
        $category->category_name = $validated['category_name'];
        $category->category_description = $validated['textarea'] ?? '';
        $category->save();

        // Display a success message
        Alert::info('Categoria de produs a fost actualizată cu succes.');

        return redirect()->route('platform.product-categories');
    }

    public function delete(Request $request)
    {
        // Retrieve the route parameter
        $orderIdFromRoute = $request->route('id');

        // Find the existing service order
        $category = Category::findOrFail($orderIdFromRoute);

        // Delete the product category
        $category->delete();

        // Display a success message
        Alert::info('Categoria de produs a fost ștearsă cu succes.');

        return redirect()->route('platform.product-categories');
    }
}
