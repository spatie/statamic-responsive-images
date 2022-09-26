<?php

namespace Spatie\ResponsiveImages\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Spatie\ResponsiveImages\ResponsiveReferenceUpdater;
use Statamic\Events\AssetDeleted;
use Statamic\Events\AssetSaved;
use Statamic\Listeners\Concerns\GetsItemsContainingData;

class UpdateResponsiveReferences implements ShouldQueue
{
    use GetsItemsContainingData;

    /**
     * @param $events
     * @return void
     */
    public function subscribe($events)
    {
        if (config('statamic.system.update_references') === false) {
            return;
        }

        $events->listen(AssetSaved::class, [self::class, 'handleSaved']);
        $events->listen(AssetDeleted::class, [self::class, 'handleDeleted']);
    }

    /**
     * Handle asset saved event.
     *
     * @param AssetSaved $event
     */
    public function handleSaved(AssetSaved $event)
    {
        $asset = $event->asset;

        $container = $asset->container()->handle();
        $originalPath = $asset->getOriginal('path');
        $newPath = $asset->path();

        $this->replaceReferences($container, $originalPath, $newPath);
    }

    /**
     * Handle asset deleted event.
     *
     * @param AssetDeleted $event
     * @return void
     */
    public function handleDeleted(AssetDeleted $event)
    {
        $asset = $event->asset;

        $container = $asset->container()->handle();
        $originalPath = $asset->getOriginal('path');
        $newPath = null;

        $this->replaceReferences($container, $originalPath, $newPath);
    }

    /**
     * @param $container
     * @param $originalPath
     * @param $newPath
     * @return void
     */
    protected function replaceReferences($container, $originalPath, $newPath)
    {
        if (! $originalPath || $originalPath === $newPath) {
            return;
        }

        $newValue = $newPath ? "{$container}::{$newPath}" : null;

        $this->getItemsContainingData()->each(function ($item) use ($container, $originalPath, $newValue) {
            ResponsiveReferenceUpdater::item($item)
                ->filterByContainer($container)
                ->updateReferences($container . '::' . $originalPath, $newValue);
        });
    }
}
