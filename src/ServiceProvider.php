<?php

namespace Spatie\ResponsiveImages;

use Illuminate\Support\Facades\Blade;
use Spatie\ResponsiveImages\Commands\GenerateResponsiveVersionsCommand;
use Spatie\ResponsiveImages\Commands\RegenerateResponsiveVersionsCommand;
use Spatie\ResponsiveImages\Fieldtypes\ResponsiveFieldtype;
use Spatie\ResponsiveImages\Listeners\GenerateResponsiveVersions;
use Spatie\ResponsiveImages\Tags\ResponsiveTag;
use Statamic\Events\AssetUploaded;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $publishAfterInstall = false;

    protected $tags = [
        ResponsiveTag::class,
    ];

    protected $fieldtypes = [
        ResponsiveFieldtype::class,
    ];

    protected $stylesheets = [
        __DIR__.'/../dist/css/responsive.css',
    ];

    protected $scripts = [
        __DIR__.'/../dist/js/responsive.js',
    ];

    protected $listen = [
        AssetUploaded::class => [
            GenerateResponsiveVersions::class,
        ],
    ];

    protected $commands = [
        GenerateResponsiveVersionsCommand::class,
        RegenerateResponsiveVersionsCommand::class,
    ];

    public function boot()
    {
        parent::boot();

        $this->bootAddonViews()
            ->bootAddonConfig()
            ->bootDirectives();
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
        $this->mergeConfigFrom(__DIR__.'/../config/responsive-images.php', 'statamic.responsive-images');

        $this->publishes([
            __DIR__.'/../config/responsive-images.php' => config_path('statamic/responsive-images.php'),
        ], 'responsive-images-config');

        return $this;
    }

    protected function bootDirectives()
    {
        Blade::directive('responsive', function ($arguments) {
            return "<?php echo \Spatie\ResponsiveImages\Tags\ResponsiveTag::render({$arguments}) ?>";
        });

        return $this;
    }
}
