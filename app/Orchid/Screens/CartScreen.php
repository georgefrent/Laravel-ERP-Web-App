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

use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

use Stripe\Product as StripeProduct;
use Stripe\Price;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\CheckoutController;

class CartScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $user = Auth::user();
        $cart = Cart::with('items')->where('user_id', $user->id)->first();

        return [
            'cart' => $cart,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Coșul meu';
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
        $user = Auth::user();
        $cart = Cart::with('items')->where('user_id', $user->id)->first();

        // Find or create the cart for the user
        $cart = Cart::firstOrCreate(
            ['user_id' => $user->id],
            ['user_name' => $user->name, 'user_email' => $user->email]
        );

        $productItems = [];
        $serviceOrderItems = [];

        if ($cart) {
            foreach ($cart->items as $item) {
                if ($item->type === 'product') {
                    $productItems[] = $item;
                } elseif ($item->type === 'serviceOrder') {
                    $serviceOrderItems[] = $item;
                }
            }
        }

        $cartProductLayouts = $this->generateCartProductLayout($productItems);
        $cartServiceOrderLayouts = $this->generateCartServiceOrderLayout($serviceOrderItems);

        return array_merge([
            Layout::legend('cart', [
                Sight::make('total_price', 'Preț total')->render(function ($cart) {
                    return $cart->total_price . ' LEI ' .
                        Button::make('Finalizează plata')
                            ->type(Color::SUCCESS)
                            ->method('proceedToCheckout');
                }),
            ])->title('Sumarul coșului'),
        ], $cartProductLayouts, $cartServiceOrderLayouts);
    }

    /**
     * Generate the layout for cart products.
     *
     * @param Cart $cart
     * @return array
     */
    private function generateCartProductLayout(array $items): array
    {
        $itemsLayout = [];

        foreach ($items as $item) {
            $itemsLayout[] = Layout::legend("item_{$item->id}", [
                Sight::make('product_name', 'Numele produsului')->render(fn () => $item->product_name),
                Sight::make('price', 'Preț')->render(fn () => $item->price . ' LEI'),
                Sight::make('quantity', 'Cantitate')->render(fn () => $item->quantity),
                Sight::make('remove', '')->render(fn () =>
                    Button::make('Elimină')
                        ->type(Color::DANGER)
                        ->method('removeFromCart')
                        ->parameters(['cart_item_id' => $item->id])
                ),
                Sight::make('add', '')->render(fn () =>
                    Button::make('Adaugă')
                        ->type(Color::SUCCESS)
                        ->method('addToCart')
                        ->parameters(['cart_item_id' => $item->id])
                ),
            ]);
        }

        return $itemsLayout;
    }

    /**
     * Generate the layout for cart service orders.
     *
     * @param Cart $cart
     * @return array
     */
    private function generateCartServiceOrderLayout(array $items): array
    {
        $itemsLayout = [];

        foreach ($items as $item) {
            $itemsLayout[] = Layout::legend("item_{$item->id}", [
                Sight::make('service_order_name', 'Numele comenzii')->render(fn () => 'SERVICE ' . $item->device_name),
                Sight::make('price', 'Preț')->render(fn () => $item->price . ' LEI'),
                Sight::make('remove', '')->render(fn () =>
                    Button::make('Elimină')
                        ->type(Color::DANGER)
                        ->method('removeFromCart')
                        ->parameters(['cart_item_id' => $item->id])
                ),
            ]);
        }

        return $itemsLayout;
    }

    /**
     * Method to remove one quantity from the cart.
     *
     * @param Request $request
     */
    public function removeFromCart(Request $request)
    {
        $cartItem = CartItem::findOrFail($request->cart_item_id);
        $cart = $cartItem->cart;

        if ($cartItem->quantity > 1) {
            $cartItem->quantity -= 1;
            $cartItem->save();
        } else {
            $cartItem->delete();
        }

        // Update total price
        $cart->total_price = $cart->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });
        $cart->save();

        Toast::info('Coșul a fost actualizat cu succes!');
    }

    /**
     * Method to add one quantity to the cart.
     *
     * @param Request $request
     */
    public function addToCart(Request $request)
    {
        $cartItem = CartItem::findOrFail($request->cart_item_id);
        $product = Product::findOrFail($cartItem->product_id);
        $cart = $cartItem->cart;

        if ($cartItem->quantity < $product->quantity_in_stock) {
            $cartItem->quantity += 1;
            $cartItem->save();

            // Update total price
            $cart->total_price = $cart->items->sum(function ($item) {
                return $item->price * $item->quantity;
            });
            $cart->save();

            Toast::info('Coșul a fost actualizat cu succes!');
        } else {
            Toast::error('Nu se poate adăuga mai mult decât stocul disponibil.');
        }
    }

    public function proceedToCheckout()
    {
        $user = Auth::user();

        if ($user && is_null($user->email_verified_at)) {
            Alert::info('Înainte de a continua, vă rugăm să vă verificați adresa de e-mail făcând clic pe linkul pe care l-ați primit în mail.');
            return redirect()->route('platform.main');
        }

        $url = url('/checkout');

        return redirect()->to($url);
    }
}
