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

        $this->bootAddonViews()
            ->bootAddonConfig();
    }

    protected function bootAddonViews()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'responsive-images');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/responsive-images'),
        ], 'responsive-images-views');

        return $this;
    }

    protected function bootAddonConfig()
    {
        $this->publishes([
            __DIR__.'/../config/responsive-images.php' => config_path('statamic/responsive-images.php'),
        ], 'responsive-images-config');

        return $this;
    }

    public function register() {
        parent::register();

        $this->registerAddonConfig();
    }

    protected function registerAddonConfig() {
        $this->mergeConfigFrom(
            __DIR__.'/../config/responsive-images.php', 'responsive-images'
        );

        return $this;
    }
}
