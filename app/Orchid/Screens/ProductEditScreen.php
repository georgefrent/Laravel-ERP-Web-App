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
use Orchid\Screen\Fields\Matrix;

use Orchid\Screen\Fields\Picture;
use Orchid\Screen\Fields\Upload;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductEditScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {

        // $IdFromRoute = request()->route('id');

        $product_categories = Category::all()->pluck('category_name', 'category_name');

        // $product_image = ProductImage::findOrFail($IdFromRoute);

        return [
            'product_categories' => $product_categories,
            // 'product_image' => $product_image,
            // 'image_path' => $product_image ? $product_image->image_path : null,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Editare produs';
    }

    public function permission(): ?iterable
    {
        return [
            'platform.products',
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
        $IdFromRoute = request()->route('id');

        $products = \App\Models\Product::all();

        // $product_images = \App\Models\ProductImage::all();

        // Loop through each product
        foreach ($products as $product) {
            if ($product->product_id == $IdFromRoute) {
                // Access each column and assign to separate variables
                $product_id = $product->product_id;
                $product_name = $product->product_name;
                $category_name = $product->category_name;
                $brand = $product->brand;
                $model = $product->model;
                $price = $product->price;
                $quantity_in_stock = $product->quantity_in_stock;
                $description = $product->description;
                $specifications = json_decode($product->specifications, true) ?? [];
                $entered_at = $product->entered_at;
            }
        }

        // $specificationsJson = json_encode($specifications, JSON_PRETTY_PRINT);

        // Transform the specifications array into a string with each key-value pair on a new line
        $specificationsString = '';
        if ($specifications) {
            foreach ($specifications as $key => $value) {
                $specificationsString .= $key . ': ' . $value . "\n";
            }
        }

        // $product_image = ProductImage::where('product_id', $product->product_id)->first();

        return [
            Layout::rows([

                Input::make('product_name')
                    ->title('Nume produs:')
                    ->value($product_name)
                    ->placeholder('Introdu numele produsului')
                    ->required(),

                Select::make('category_name')
                    ->options($this->query()['product_categories'])
                    ->title('Selectează categoria')
                    ->placeholder('Selectează categoria produsului')
                    ->empty('')
                    ->help($category_name)
                    ->required(),

                Input::make('brand')
                    ->title('Brand')
                    ->value($brand)
                    ->placeholder('Introdu marca produsului')
                    ->required(),

                Input::make('model')
                    ->title('Model')
                    ->value($model)
                    ->placeholder('Introdu modelul produsului'),

                Input::make('price')
                    ->type('number')
                    ->value($price)
                    ->title('Preț')
                    ->placeholder('Introdu prețul produsului')
                    ->step(0.01)
                    ->required(),

                Input::make('quantity_in_stock')
                    ->type('number')
                    ->value($quantity_in_stock)
                    ->title('Cantitate în stoc')
                    ->placeholder('Introdu cantitatea produsului care să fie în stoc'),

                TextArea::make('textarea')
                    ->title('Descriere')
                    ->value($description)
                    ->placeholder('Introdu descrierea produsului')
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
                    ->value($specifications)
                    ->help('Adăugați perechi titlu-text pentru specificații.'),

                TextArea::make('specifications_string')
                    ->title('Specificații care există deja')
                    ->value($specificationsString)
                    ->placeholder('')
                    ->rows(10),



                Group::make([
                    Button::make('Trimite')
                        ->method('buttonClickProcessing')
                        ->type(Color::PRIMARY),

                    Button::make('Șterge')
                        ->method('delete')
                        ->type(Color::DANGER)
                        ->confirm('Ești sigur că vrei să ștergi acest produs?'),
                ]),

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

        // Retrieve the route parameter
        $IdFromRoute = $request->route('id');

        // Find the existing product
        $product = Product::findOrFail($IdFromRoute);

        // Retrieve the category
        $product_categories = Category::where('category_name', $validated['category_name'])->firstOrFail();

        // Convert specifications to JSON
        $jsonSpecifications = [];
        if (isset($validated['specifications'])) {
            foreach ($validated['specifications'] as $specification) {
                if (!empty($specification['title']) && isset($specification['text'])) {
                    $jsonSpecifications[$specification['title']] = $specification['text'];
                }
            }
        }

        // Update a product
        $product->product_name = $validated['product_name'];
        $product->category_name = $validated['category_name'];
        $product->brand = $validated['brand'];
        $product->model = $validated['model'] ?? '';
        $product->price = $validated['price'];
        $product->quantity_in_stock = $validated['quantity_in_stock'] ?? 0;
        $product->description = $validated['textarea'] ?? '';
        $product->specifications = json_encode($jsonSpecifications);
        $product->entered_at = $product->entered_at;
        $product->save();

        // Display a success message
        Alert::info('Produsul a fost actualizat cu succes.');

        return redirect()->route('platform.products');
    }

    public function delete(Request $request)
    {
        // Retrieve the route parameter
        $IdFromRoute = $request->route('id');

        // Find the existing service order
        $product = Product::findOrFail($IdFromRoute);

        // Delete the product
        $product->delete();

        // Display a success message
        Alert::info('Produsul a fost șters cu succes.');

        return redirect()->route('platform.products');
    }
}

