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

class ProductCategoriesScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        // --------------------------------- Table

        // Query the database to get the products from the products table
        $categories = DB::table('product_categories')
            ->select(
                'category_id',
                'category_name',
                'category_description',
            )
            ->get();

        // Format the products into the desired structure
        $categoryData = [];
        foreach ($categories as $category) {
            $categoryData[] = new Repository([
                'category_id' => $category->category_id,
                'category_name' => $category->category_name,
                'category_description' => $category->category_description,
            ]);
        }

        return [
            'table'   => $categoryData,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Categorii de produs';
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
        return [
            Layout::table('table', [
                TD::make('edit', '')
                    ->width('100')
                    ->render(fn (Repository $model) =>
                        '<a href="' . route('platform.product-categories.edit', $model->get('category_id')) . '" class="btn btn-warning center-text">Edit</a>'
                    ),

                TD::make('category_id', 'ID categorie')
                    ->width('50')
                    ->render(fn (Repository $model) => $model->get('category_id'))
                    ->sort(),

                TD::make('category_name', 'Nume categorie')
                    ->width('150')
                    ->render(fn (Repository $model) => Str::limit($model->get('category_name'), 50))
                    ->sort(),

                TD::make('category_description', 'Descriere')
                    ->width('200')
                    ->render(fn (Repository $model) => Str::limit($model->get('category_description'), 100)),
                ]),
        ];
    }
}
