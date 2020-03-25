<?php

namespace Spatie\ResponsiveImages;

use Spatie\ResponsiveImages\Commands\RegenerateResponsiveVersionsCommand;
use Spatie\ResponsiveImages\Listeners\GenerateResponsiveVersions;
use Statamic\Events\Data\AssetUploaded;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $tags = [
        Responsive::class,
    ];

    protected $publishables = [
        __DIR__.'/../resources/views/responsiveImageWithPlaceholder.antlers.html',
        __DIR__.'/../resources/views/responsiveImage.antlers.html',
    ];

    protected $listen = [
        AssetUploaded::class => [
            GenerateResponsiveVersions::class,
        ],
    ];

    protected $commands = [
        RegenerateResponsiveVersionsCommand::class,
    ];

    public function boot()
    {
        parent::boot();

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'responsive-images');
    }
}
