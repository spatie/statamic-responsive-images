<?php

namespace Spatie\ResponsiveImages;

use Illuminate\Support\Collection;

interface DimensionCalculator
{
    /**
     * Used to generate dimensions for breakpoints/sources srcset attribute. Starting with the smallest dimensions
     * and ending with largest.
     *
     * @param Source $source
     * @return Collection<Dimensions>
     */
    public function calculateForBreakpoint(Source $source): Collection;

    /**
     * Specific dimension calculation for <img> or <source> tag. Important for cases where it is important to maintain
     * aspect ratio, then browser (depending on provided CSS) can use `width` and `height` to maintain aspect ratio.
     * On other hand the returned values here are not as important if you plan to control <img> styling in other ways
     * e.g. with the following CSS: width: 100%, height 100%.
     *
     * @param Breakpoint $breakpoint
     * @return Dimensions
     */
    public function calculateForImgTag(Breakpoint $breakpoint): Dimensions;

    /**
     * Used for generating dimensions for placeholder image which is blurred.
     * We recommend a width of low value of 32px, as the image contents will be turned into a string
     * that gets output in the srcset.
     *
     * @param Breakpoint $breakpoint
     * @return Dimensions
     */
    public function calculateForPlaceholder(Breakpoint $breakpoint): Dimensions;
}
