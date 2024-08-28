<?php

declare(strict_types=1);

namespace App\Orchid\Layouts;

use Orchid\Screen\Layouts\Chart;

class ChartsLayout extends Chart
{
    /**
     * Available options:
     * 'bar', 'line',
     * 'pie', 'percentage'.
     *
     * @var string
     */
    protected $type = 'bar';

     /**
     * Height of the chart.
     *
     * @var int
     */
    protected $height = 300;
    /**
     * Determines whether to display the export button.
     *
     * @var bool
     */
    protected $export = true;
}
