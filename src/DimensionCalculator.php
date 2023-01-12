<?php

namespace Spatie\ResponsiveImages;

use Illuminate\Support\Collection;
use Statamic\Contracts\Assets\Asset;

interface DimensionCalculator
{
    /**
     * Used to generate dimensions for breakpoints.
     *
     * @param Asset $asset
     * @param Breakpoint $breakpoint
     * @return Collection<Dimensions>
     */
    public function calculate(Asset $asset, Breakpoint $breakpoint): Collection;

    /**
     * Specific dimension calculation for <img> tag. Important for cases where it is important to maintain
     * aspect ratio, then browser (depending on provided CSS) can use `width` and `height` to maintain aspect ratio.
     * On other hand the returned values here are not as important if you plan to control <img> styling in other ways
     * e.g. with the following CSS: width: 100%, height 100%.
     *
     * @param Asset $asset
     * @param Breakpoint $breakpoint
     * @return Dimensions
     */
    public function calculateForImgTag(Asset $asset, Breakpoint $breakpoint): Dimensions;

    /**
     * Used for generating dimensions for placeholder image which is blurred.
     * We recommend a width of low value such as 32px, as the image contents will be turned into a string
     * that gets output in the srcset.
     *
     * @param Asset $asset
     * @param Breakpoint $breakpoint
     * @return Dimensions
     */
    public function calculateForPlaceholder(Asset $asset, Breakpoint $breakpoint): Dimensions;
}
