<?php

namespace Spatie\ResponsiveImages;

use Illuminate\Support\Collection;
use Statamic\Contracts\Assets\Asset;

class WidthCalculator
{
    public function calculateWidthsFromAsset(Asset $asset): Collection
    {
        $width = $asset->width();
        $height = $asset->height();
        $fileSize = $asset->size();

        return $this->calculateWidths($fileSize, $width, $height)->sort();
    }

    public function calculateWidths(int $fileSize, int $width, int $height): Collection
    {
        $targetWidths = collect();

        $targetWidths->push($width);

        $ratio = $height / $width;
        $area = $height * $width;

        $predictedFileSize = $fileSize;
        $pixelPrice = $predictedFileSize / $area;

        while (true) {
            $predictedFileSize *= 0.7;

            $newWidth = (int) floor(sqrt(($predictedFileSize / $pixelPrice) / $ratio));

            if ($this->finishedCalculating($predictedFileSize, $newWidth)) {
                if (config('statamic.responsive-images.max_width')) {
                    $targetWidths = $targetWidths->filter(function ($width) {
                        return $width <= config('statamic.responsive-images.max_width');
                    });
                }

                return $targetWidths;
            }

            $targetWidths->push($newWidth);
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
