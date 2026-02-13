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

    public function subscribe($events): void
    {
        if (config('statamic.system.update_references') === false) {
            return;
        }

        $events->listen(AssetSaved::class, [self::class, 'handleSaved']);
        $events->listen(AssetDeleted::class, [self::class, 'handleDeleted']);
    }

    public function handleSaved(AssetSaved $event): void
    {
        $asset = $event->asset;

        $this->replaceReferences(
            $asset->container()->handle(),
            $asset->getOriginal('path'),
            $asset->path(),
        );
    }

    public function handleDeleted(AssetDeleted $event): void
    {
        $asset = $event->asset;

        $this->replaceReferences(
            $asset->container()->handle(),
            $asset->getOriginal('path'),
            null,
        );
    }

    protected function replaceReferences(string $container, ?string $originalPath, ?string $newPath): void
    {
        if (! $originalPath || $originalPath === $newPath) {
            return;
        }

        $newValue = $newPath ? "{$container}::{$newPath}" : null;

        $this->getItemsContainingData()->each(function ($item) use ($container, $originalPath, $newValue) {
            ResponsiveReferenceUpdater::item($item)
                ->filterByContainer($container)
                ->updateReferences("{$container}::{$originalPath}", $newValue);
        });
    }
}
