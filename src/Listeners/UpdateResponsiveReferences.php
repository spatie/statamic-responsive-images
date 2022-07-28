<?php

namespace Spatie\ResponsiveImages\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Spatie\ResponsiveImages\ResponsiveReferenceUpdater;
use Statamic\Events\AssetSaved;
use Statamic\Listeners\Concerns\GetsItemsContainingData;
use Illuminate\Support\Facades\Log;

class UpdateResponsiveReferences implements ShouldQueue
{
    use GetsItemsContainingData;

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(AssetSaved::class, self::class.'@handle');
    }

    /**
     * Handle the events.
     *
     * @param  AssetSaved  $event
     */
    public function handle(AssetSaved $event)
    {
        $asset = $event->asset;

        $container = $asset->container()->handle();
        $originalPath = $asset->getOriginal('path');
        $newPath = $asset->path();

        if (! $originalPath || $originalPath === $newPath) {
            return;
        }

        Log::info('UpdateResponsiveReferences listener triggered.');

        $this->getItemsContainingData()->each(function ($item) use ($container, $originalPath, $newPath) {
            ResponsiveReferenceUpdater::item($item)
                ->filterByContainer($container)
                ->updateReferences($container . '::' . $originalPath, $container . '::' . $newPath);
        });
    }
}
