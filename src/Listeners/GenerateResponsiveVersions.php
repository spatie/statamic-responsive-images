<?php

namespace Spatie\ResponsiveImages\Listeners;

use Spatie\ResponsiveImages\Breakpoint;
use Spatie\ResponsiveImages\Responsive;
use Spatie\ResponsiveImages\Source;
use Statamic\Events\AssetUploaded;
use Statamic\Tags\Parameters;

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

        $responsive = new Responsive($event->asset, new Parameters());
        $responsive->breakPoints()->each(function (Breakpoint $breakpoint) {
            $breakpoint->sources()->each(function (Source $source) {
                $source->dispatchImageJobs();
            });
        });
    }
}
