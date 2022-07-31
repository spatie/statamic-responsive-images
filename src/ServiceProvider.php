<?php

namespace Spatie\ResponsiveImages;

use Illuminate\Support\Facades\Blade;
use Spatie\ResponsiveImages\Commands\GenerateResponsiveVersionsCommand;
use Spatie\ResponsiveImages\Commands\RegenerateResponsiveVersionsCommand;
use Spatie\ResponsiveImages\Fieldtypes\ResponsiveFieldtype;
use Spatie\ResponsiveImages\GraphQL\BreakpointType;
use Spatie\ResponsiveImages\GraphQL\ResponsiveField;
use Spatie\ResponsiveImages\GraphQL\ResponsiveFieldType as GraphQLResponsiveFieldType;
use Spatie\ResponsiveImages\Jobs\GenerateImageJob;
use Spatie\ResponsiveImages\Listeners\GenerateResponsiveVersions;
use Spatie\ResponsiveImages\Listeners\UpdateResponsiveReferences;
use Spatie\ResponsiveImages\Tags\ResponsiveTag;
use Statamic\Events\AssetUploaded;
use Statamic\Events\AssetSaved;
use Statamic\Facades\GraphQL;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
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
        AssetSaved::class => [
            UpdateResponsiveReferences::class,
        ],
    ];

    protected $commands = [
        GenerateResponsiveVersionsCommand::class,
        RegenerateResponsiveVersionsCommand::class,
    ];

    public function boot()
    {
        parent::boot();

        $this
            ->bootEvents()
            ->bootCommands()
            ->bootAddonViews()
            ->bootAddonConfig()
            ->bootDirectives()
            ->bindImageJob()
            ->bootGraphQL();
    }

    protected function bootAddonViews(): self
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'responsive-images');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/responsive-images'),
        ], 'responsive-images-views');

        return $this;
    }

    protected function bootAddonConfig(): self
    {
        $this->mergeConfigFrom(__DIR__.'/../config/responsive-images.php', 'statamic.responsive-images');

        $this->publishes([
            __DIR__.'/../config/responsive-images.php' => config_path('statamic/responsive-images.php'),
        ], 'responsive-images-config');

        return $this;
    }

    protected function bootDirectives(): self
    {
        Blade::directive('responsive', function ($arguments) {
            return "<?php echo \Spatie\ResponsiveImages\Tags\ResponsiveTag::render({$arguments}) ?>";
        });

        return $this;
    }

    private function bindImageJob(): self
    {
        $this->app->bind(GenerateImageJob::class, config('statamic.responsive-images.image_job'));

        return $this;
    }

    private function bootGraphQL(): self
    {
        GraphQL::addType(BreakpointType::class);
        GraphQL::addType(GraphQLResponsiveFieldType::class);

        GraphQL::addField('AssetInterface', 'responsive', function () {
            return (new ResponsiveField())->toArray();
        });

        return $this;
    }
}
