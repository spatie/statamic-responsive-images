<?php

namespace Spatie\ResponsiveImages;

use Illuminate\Support\Collection;
use Statamic\Contracts\Assets\Asset;

/**
 * The original, file-size, aspect-ratio based dimension calculator.
 */
class ResponsiveDimensionCalculator implements DimensionCalculator
{
    public function calculateForBreakpoint(Source $source): Collection
    {
        $asset = $source->breakpoint->asset;
        $width = $asset->width();
        $height = $asset->height();
        $fileSize = $asset->size();

        $ratio = $this->breakpointRatio($asset, $source->breakpoint);
        $glideParams = $source->breakpoint->getImageManipulationParams();

        return $this
            ->calculateDimensions($fileSize, $width, $height, $ratio)
            ->sort()
            // Filter out widths by max width
            ->when((isset($glideParams['width']) || config('statamic.responsive-images.max_width') !== null), function ($dimensions) use ($glideParams, $ratio) {
                $maxWidth = $glideParams['width'] ?? config('statamic.responsive-images.max_width');

                $filtered = $dimensions->filter(function (Dimensions $dimensions) use ($maxWidth) {
                    return $dimensions->getWidth() <= $maxWidth;
                });

                // We want at least one width to be returned
                if (! $filtered->count()) {
                    $filtered = collect([
                        new Dimensions($maxWidth, round($maxWidth / $ratio)),
                    ]);
                }

                return $filtered;
            });
    }

    public function calculateForImgTag(Breakpoint $breakpoint): Dimensions
    {
        $maxWidth = ($breakpoint->parameters['glide:width'] ?? config('statamic.responsive-images.max_width') ?? null);

        $ratio = $this->breakpointRatio($breakpoint->asset, $breakpoint);
        $originalWidth = $breakpoint->asset->width();
        $originalHeight = $breakpoint->asset->height();

        $width = $maxWidth ?? $originalWidth;
        $height = round($width / $ratio);

        // If the calculated dimensions exceed the original image dimensions,
        // constrain them to fit within the original bounds
        if ($height > $originalHeight) {
            $height = $originalHeight;
            $width = round($height * $ratio);
            
            // Ensure width doesn't exceed original width either
            if ($width > $originalWidth) {
                $width = $originalWidth;
                $height = round($width / $ratio);
            }
        } elseif ($width > $originalWidth) {
            $width = $originalWidth;
            $height = round($width / $ratio);
        }

        return new Dimensions($width, $height);
    }

    public function calculateForPlaceholder(Breakpoint $breakpoint): Dimensions
    {
        return new Dimensions(32, 32 / $this->breakpointRatio($breakpoint->asset, $breakpoint));
    }

    public function breakpointRatio(Asset $asset, Breakpoint $breakpoint): float
    {
        return $breakpoint->parameters['ratio'] ?? ($asset->width() / $asset->height());
    }

    protected function calculateDimensions(int $assetFilesize, int $assetWidth, int $assetHeight, $ratio): Collection
    {
        $dimensions = collect();

        // Calculate initial dimensions ensuring they don't exceed original image dimensions
        $calculatedWidth = $assetWidth;
        $calculatedHeight = round($calculatedWidth / $ratio);

        // If calculated dimensions exceed original, constrain them appropriately
        if ($calculatedHeight > $assetHeight) {
            $calculatedHeight = $assetHeight;
            $calculatedWidth = round($calculatedHeight * $ratio);
            
            // Ensure width doesn't exceed original width either
            if ($calculatedWidth > $assetWidth) {
                $calculatedWidth = $assetWidth;
                $calculatedHeight = round($calculatedWidth / $ratio);
            }
        }

        $dimensions->push(new Dimensions($calculatedWidth, $calculatedHeight));

        // For filesize calculations
        $ratioForFilesize = $assetHeight / $assetWidth;
        $area = $assetHeight * $assetWidth;

        $predictedFileSize = $assetFilesize;
        $pixelPrice = $predictedFileSize / $area;

        while (true) {
            $predictedFileSize *= config('statamic.responsive-images.dimension_calculator_threshold', 0.7);

            $newWidth = (int) floor(sqrt(($predictedFileSize / $pixelPrice) / $ratioForFilesize));

            if ($this->finishedCalculating($predictedFileSize, $newWidth)) {
                return $dimensions;
            }

            $dimensions->push(new Dimensions($newWidth, round($newWidth / $ratio)));
        }
    }

    protected function finishedCalculating(float $predictedFileSize, int $newWidth): bool
    {
        if ($newWidth < 20) {
            return true;
        }

        if ($predictedFileSize < (1024 * 10)) {
            return true;
        }

        return false;
    }
}
