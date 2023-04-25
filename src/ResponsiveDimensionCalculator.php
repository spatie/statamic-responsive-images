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

        $width = $maxWidth ?? $breakpoint->asset->width();

        return new Dimensions($width, round($width / $ratio));
    }

    public function calculateForPlaceholder(Breakpoint $breakpoint): Dimensions
    {
        return new Dimensions(32, 32 / $this->breakpointRatio($breakpoint->asset, $breakpoint));
    }

    public function breakpointRatio(Asset $asset, Breakpoint $breakpoint): float
    {
        return $breakpoint->parameters['ratio'] ?? ($asset->width() / $asset->height());
    }

    private function calculateDimensions(int $assetFilesize, int $assetWidth, int $assetHeight, $ratio): Collection
    {
        $dimensions = collect();

        $dimensions->push(new Dimensions($assetWidth, round($assetWidth / $ratio)));

        // For filesize calculations
        $ratioForFilesize = $assetHeight / $assetWidth;
        $area = $assetHeight * $assetWidth;

        $predictedFileSize = $assetFilesize;
        $pixelPrice = $predictedFileSize / $area;

        while (true) {
            $predictedFileSize *= config('statamic.responsive-images.predicted_file_size_multiplier', 0.7);

            $newWidth = (int) floor(sqrt(($predictedFileSize / $pixelPrice) / $ratioForFilesize));

            if ($this->finishedCalculating($predictedFileSize, $newWidth)) {
                return $dimensions;
            }

            $dimensions->push(new Dimensions($newWidth, round($newWidth / $ratio)));
        }
    }

    private function finishedCalculating(float $predictedFileSize, int $newWidth): bool
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
