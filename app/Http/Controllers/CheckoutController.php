<?php

namespace App\Http\Controllers;

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
use Illuminate\Support\Facades\Mail;

class CheckoutController extends Controller
{
    public function checkout(Request $request)
    {
        $user = Auth::user();
        $cart = Cart::with('items')->where('user_id', $user->id)->first();

        $user_name = $user->name;
        $user_email = $user->email;

        // Set Stripe API key from .env file
        Stripe::setApiKey(env('STRIPE_SECRET'));

        // Example product details
        $productName = 'Comanda - ' . $user_name;
        $productDescription = $user_email;
        $productAmount = $cart->total_price;

        try {
            // Transform price in integer
            $amountInInteger = intval($productAmount * 100);

            // Create a new product in Stripe
            $product = StripeProduct::create([
                'name' => $productName,
                'description' => $productDescription,
            ]);

            // Create a price for the new product
            $price = Price::create([
                'product' => $product->id,
                'unit_amount' => $amountInInteger,
                'currency' => 'ron',
            ]);

            $subject = 'Plată realizată cu succes';
            $message = 'Plata în valoare de ' . $productAmount . ' RON a fost finalizată cu succes.';

            Mail::raw($message, function ($mail) use ($user_email, $subject) {
                $mail->to($user_email)
                    ->subject($subject);
            });

            return $user->checkout(
                $price->id,
                [
                    'success_url' => route('success'),
                    'cancel_url' => route('cancel'),
                ]
            );
        } catch (\Exception $e) {
            // Handle errors appropriately
            return redirect()->back()->withErrors(['error' => 'Eroare la crearea produsului Stripe: ' . $e->getMessage()]);
        }
    }
}
