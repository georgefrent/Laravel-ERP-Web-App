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

use Orchid\Screen\Sight;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;

class ClientViewProductScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $product_categories = Category::all()->pluck('category_name', 'category_name');

        return [
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
        return 'Vizualizare produs';
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

        $specificationsHtml = '<table>';
        foreach ($specifications as $key => $value) {
            $specificationsHtml .= "<tr><td><strong>$key</strong></td><td>$value</td></tr>";
        }
        $specificationsHtml .= '</table>';

        return [
            Layout::legend('product', [
                // Sight::make('product_id', 'ID')->render(fn () => $product_id),
                Sight::make('product_name', 'Nume produs')->render(fn () => $product_name),
                Sight::make('category_name', 'Categorie')->render(fn () => $category_name),
                Sight::make('brand', 'Brand')->render(fn () => $brand),
                Sight::make('model', 'Model')->render(fn () => $model),
                Sight::make('price', 'Preț')->render(fn () => $price . '   LEI'),
                Sight::make('quantity_in_stock', 'Cantitate în stoc')->render(fn () => $quantity_in_stock),
                Sight::make('description', 'Descriere')->render(fn () => nl2br($description)),
                Sight::make('specifications', 'Specificații')->render(fn () => $specificationsHtml),
                Sight::make('action',' ')->render(fn () => Button::make('Adaugă în coș')
                    ->type(Color::PRIMARY)
                    ->method('addToCart')),
            ])->title($product_name),
        ];
    }

    public function addToCart(Request $request)
    {

        // Retrieve the route parameter
        $IdFromRoute = $request->route('id');

        // Find the existing product
        $product = Product::findOrFail($IdFromRoute);

        $user = Auth::user();

        // Find or create the cart for the user
        $cart = Cart::firstOrCreate(
            ['user_id' => $user->id],
            ['user_name' => $user->name, 'user_email' => $user->email]
        );

        // Check if the product is already in the cart
        $cartItem = $cart->items()->where('product_id', $product->product_id)->first();

        if ($cartItem) {
            $cartItem->quantity += 1; // Increment quantity
            $cartItem->save();
        } else {
            $cart->items()->create([
                'product_id' => $product->product_id,
                'product_name' => $product->product_name,
                'price' => $product->price,
                'type' => 'product',
                'quantity' => 1,
            ]);
        }

        // Update total price
        $cart->total_price = $cart->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });
        $cart->save();

        Toast::info('Produsul a fost adăugat în coș!');
    }
}
