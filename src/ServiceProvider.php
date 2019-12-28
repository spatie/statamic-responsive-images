<?php

namespace Rias\ResponsiveImages;

use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $tags = [
        Responsive::class,
    ];

    protected $publishables = [
        __DIR__.'/../resources/views/responsiveImageWithPlaceholder.antlers.html',
    ];

    public function boot()
    {
        parent::boot();

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'responsive-images');
    }
}
