<?php

namespace Spatie\ResponsiveImages\Listeners;

use Spatie\ResponsiveImages\Jobs\GenerateImageJob;
use Spatie\ResponsiveImages\WidthCalculator;
use Statamic\Events\AssetUploaded;

class GenerateResponsiveVersions
{
    public function handle(AssetUploaded $event): void
    {
        if (! $event->asset->isImage()) {
            return;
        }

        if ($event->asset->extension() === 'svg') {
            return;
        }

        if (! config('statamic.assets.image_manipulation.cache')) {
            return;
        }

        (new WidthCalculator())
            ->calculateWidthsFromAsset($event->asset)
            ->map(function (int $width) use ($event) {
                dispatch(new GenerateImageJob($event->asset, ['width' => $width]));
                dispatch(new GenerateImageJob($event->asset, ['width' => $width, 'fm' => 'webp']));
            });
    }
}
