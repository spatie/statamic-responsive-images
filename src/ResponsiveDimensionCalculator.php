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
        $height = (int) round($width / $ratio);

        return $this->constrainToOriginal($width, $height, $originalWidth, $originalHeight, $ratio);
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

        $initialHeight = (int) round($assetWidth / $ratio);

        $dimensions->push($this->constrainToOriginal($assetWidth, $initialHeight, $assetWidth, $assetHeight, $ratio));

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

            $newHeight = (int) round($newWidth / $ratio);

            $dimensions->push($this->constrainToOriginal($newWidth, $newHeight, $assetWidth, $assetHeight, $ratio));
        }
    }

    protected function constrainToOriginal(int $width, int $height, int $maxWidth, int $maxHeight, float $ratio): Dimensions
    {
        if ($height > $maxHeight) {
            $height = $maxHeight;
            $width = (int) round($height * $ratio);
        }

        if ($width > $maxWidth) {
            $width = $maxWidth;
            $height = (int) round($width / $ratio);
        }

        return new Dimensions($width, $height);
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
