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

        if (! config('statamic.responsive-images.generate_on_upload', true)) {
            return;
        }

        (new WidthCalculator())
            ->calculateWidthsFromAsset($event->asset)
            ->map(function (int $width) use ($event) {
                dispatch(app(GenerateImageJob::class, ['asset' => $event->asset, 'params' => ['width' => $width]]));
                dispatch(app(GenerateImageJob::class, ['asset' => $event->asset, 'params' => ['width' => $width, 'fm' => 'webp']]));
            });
    }
}
