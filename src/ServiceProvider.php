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

    protected $publishables = [
        __DIR__.'/../resources/views' => '../../../resources/views/vendor/statamic-responsive-images',
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
