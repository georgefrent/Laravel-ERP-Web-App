<?php

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;

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

use Orchid\Support\Facades\Alert;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Support\Color;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Label;

class ClientProductsScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Request $request): iterable
    {
        // --------------------------------- Table

        // Query the database to get the products from the products table
        $products = DB::table('products')
            ->select(
                'products.product_id',
                'products.product_name',
                'products.category_name',
                'products.brand',
                'products.model',
                'products.price',
                'products.quantity_in_stock',
                'products.description',
                'products.specifications',
                'products.entered_at',
            )
            ->get();


        // Get distinct product names and categories for the select options
        $productNames = $products->pluck('product_name', 'product_id')->all();
        $categories = $products->pluck('category_name', 'category_name')->all();

        // Filter products based on the request
        $filteredProducts = $products;

        if ($request->has('product_select')) {
            $filteredProducts = $filteredProducts->where('product_id', $request->get('product_select'));
        }

        if ($request->has('category_filter')) {
            $filteredProducts = $filteredProducts->where('category_name', $request->get('category_filter'));
        }

        // Format the products into the desired structure

        $productData = $filteredProducts->map(function ($product) {
            return new Repository([
                'product_id' => $product->product_id,
                'product_name' => $product->product_name,
                'category_name' => $product->category_name,
                'brand' => $product->brand,
                'model' => $product->model,
                'price' => $product->price,
                'quantity_in_stock' => $product->quantity_in_stock,
                'description' => $product->description,
                'specifications' => $product->specifications,
                'entered_at' => $product->entered_at,
            ]);
        })->all();

        return [
            'table'   => $productData,
            'product_names' => $productNames,
            'categories' => $categories,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Produse';
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
        $productNames = DB::table('products')->pluck('product_name', 'product_id')->all();
        $categories = DB::table('products')->pluck('category_name', 'category_name')->all();

        return [
            Layout::rows([
                Group::make([
                    Select::make('product_select')
                        ->options($productNames)
                        ->title('Căutare produs')
                        ->placeholder('Caută')
                        ->empty(''),

                    Button::make('Căutare')
                        ->method('filterByProduct')
                        ->type(Color::PRIMARY),
                ]),

                Group::make([
                    Select::make('category_filter')
                        ->options($categories)
                        ->title('Filtru de categorie')
                        ->placeholder('Filtrează')
                        ->empty(''),

                    Button::make('Filtrare')
                        ->method('filterByCategory')
                        ->type(Color::PRIMARY),
                ]),
            ]),

            Layout::table('table', [
                TD::make('view', '')
                    ->width('50')
                    ->render(fn (Repository $model) =>
                        '<a href="' . route('platform.product.view', $model->get('product_id')) . '" class="btn btn-default center-text">View</a>'
                    ),

                TD::make('product_name', 'Nume produs')
                    ->width('500')
                    ->render(fn (Repository $model) => Str::limit($model->get('product_name'), 50))
                    ->sort(),

                TD::make('category_name', 'Categorie')
                    ->width('150')
                    ->render(fn (Repository $model) => $model->get('category_name'))
                    ->sort(),

                TD::make('price', 'Preț (RON)')
                    ->width('150')
                    ->render(fn (Repository $model) => number_format($model->get('price'), 2) . ' RON')
                    ->align(TD::ALIGN_RIGHT)
                    ->sort(),

                TD::make('quantity_in_stock', 'Cantitate în stoc')
                    ->width('50')
                    ->render(fn (Repository $model) => $model->get('quantity_in_stock'))
                    ->sort(),
                ]),
        ];
    }

     /**
     * Method to handle filtering by product.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function filterByProduct(Request $request)
    {
        return redirect()->route('platform.client-products', [
            'product_select' => $request->input('product_select')
        ]);
    }

    /**
     * Method to handle filtering by category.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function filterByCategory(Request $request)
    {
        return redirect()->route('platform.client-products', [
            'category_filter' => $request->input('category_filter')
        ]);
    }
}
