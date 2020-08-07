<?php

namespace Spatie\ResponsiveImages;

use Spatie\ResponsiveImages\Commands\RegenerateResponsiveVersionsCommand;
use Spatie\ResponsiveImages\Listeners\GenerateResponsiveVersions;
use Statamic\Events\AssetUploaded;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $publishAfterInstall = false;

    protected $tags = [
        Responsive::class,
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

        $this->bootAddonViews();
    }

    protected function bootAddonViews()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'responsive-images');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/responsive-images'),
        ], 'responsive-images-views');

        return $this;
    }
}
