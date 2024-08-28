<?php

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;

use App\Models\Category;
use App\Models\Product;
use Orchid\Screen\Fields\Matrix;

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


class CreateProductsScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        // $product = Product::find(1); // Adjust to your data fetching logic
        // $specifications = $product ? json_decode($product->specifications, true) : [];

        $product_categories = Category::all()->pluck('category_name', 'category_name');

        return [
            // 'specifications' => $specifications ?? [],
            'product_categories' => $product_categories,
        ];

    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Crează produs';
    }

    public function permission(): ?iterable
    {
        return [
            'platform.create-products',
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

                Input::make('product_name')
                    ->title('Numele produsului:')
                    ->placeholder('Enter the name of the product')
                    ->required(),

                Select::make('category_name')
                    ->options($this->query()['product_categories'])
                    ->title('Selectează categoria produsului')
                    ->placeholder('Select the category of the product')
                    ->empty('')
                    ->required(),

                Input::make('brand')
                    ->title('Brand')
                    ->placeholder('Enter the brand of the product')
                    ->required(),

                Input::make('model')
                    ->title('Model')
                    ->placeholder('Enter the model of the product'),

                Input::make('price')
                    ->type('number')
                    ->title('Preț')
                    ->placeholder('Enter the price of the product')
                    ->step(0.01)
                    ->required(),

                Input::make('quantity_in_stock')
                    ->type('number')
                    ->title('Cantitate în stoc')
                    ->placeholder('Enter the product quantity that is in stock'),

                TextArea::make('textarea')
                    ->title('Descriere')
                    ->placeholder('Enter the description of the product')
                    ->rows(10),

                Matrix::make('specifications')
                    ->title('Specificații')
                    ->columns([
                        'Title' => 'title',
                        'Text' => 'text',
                    ])
                    ->fields([
                        'title' => Input::make('specifications[rows][title]')
                            ->placeholder('Introdu titlu'),
                        'text' => Input::make('specifications[rows][text]')
                            ->placeholder('Introdu text'),
                    ])
                    ->help('Adăugați perechi titlu-text pentru specificații.'),

                Button::make('Trimite')
                    ->method('buttonClickProcessing')
                    ->type(Color::PRIMARY),

            ])->title('Formular produs'),
        ];
    }

    public function buttonClickProcessing(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'category_name' => 'required|exists:product_categories,category_name',
            'brand' => 'required|string|max:255',
            'model' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'quantity_in_stock' => 'nullable|integer|min:0',
            'textarea' => 'nullable|string',
            'specifications' => 'nullable|array',
            'specifications.*.title' => 'required_with:specifications|string|max:255',
            'specifications.*.text' => 'required_with:specifications|string|max:255',
        ]);

        // Retrieve the category
        $product_categories = Category::where('category_name', $validated['category_name'])->firstOrFail();

        $existingProduct = Product::where('product_name', $validated['product_name'])->first();

        if ($existingProduct) {
            // Product already exists, show an alert and return
            Alert::warning('Produsul există deja.');

            return back();
        }

        // Convert specifications to JSON
        $jsonSpecifications = [];
        if (isset($validated['specifications'])) {
            foreach ($validated['specifications'] as $specification) {
                if (!empty($specification['title']) && isset($specification['text'])) {
                    $jsonSpecifications[$specification['title']] = $specification['text'];
                }
            }
        }

        // Create a new product
        $product = new Product();
        $product->product_name = $validated['product_name'];
        $product->category_name = $validated['category_name'];
        $product->brand = $validated['brand'];
        $product->model = $validated['model'] ?? '';
        $product->price = $validated['price'];
        $product->quantity_in_stock = $validated['quantity_in_stock'] ?? 0;
        $product->description = $validated['textarea'] ?? '';
        $product->specifications = json_encode($jsonSpecifications);
        $product->entered_at = now();
        $product->save();

        // Display a success message
        Alert::info('Produsul a fost creat cu succes.');

        return redirect()->route('platform.products');
    }
}
