<?php

namespace Spatie\ResponsiveImages\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Spatie\ResponsiveImages\ResponsiveReferenceUpdater;
use Statamic\Events\AssetSaved;
use Statamic\Listeners\Concerns\GetsItemsContainingData;

class UpdateResponsiveReferences implements ShouldQueue
{
    use GetsItemsContainingData;

    /**
     * Handle the events.
     *
     * @param AssetSaved $event
     */
    public function handle(AssetSaved $event)
    {
        $asset = $event->asset;

        $container = $asset->container()->handle();
        $originalPath = $asset->getOriginal('path');
        $newPath = $asset->path();

        if (!$originalPath || $originalPath === $newPath) {
            return;
        }

        $this->getItemsContainingData()->each(function ($item) use ($container, $originalPath, $newPath) {
            ResponsiveReferenceUpdater::item($item)
                ->filterByContainer($container)
                ->updateReferences($container . '::' . $originalPath, $container . '::' . $newPath);
        });
    }
}
