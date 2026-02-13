<?php

namespace Spatie\ResponsiveImages\Commands;

use Illuminate\Console\Command;
use Spatie\ResponsiveImages\Breakpoint;
use Spatie\ResponsiveImages\DimensionCalculator;
use Spatie\ResponsiveImages\Jobs\GenerateImageJob;
use Spatie\ResponsiveImages\Responsive;
use Spatie\ResponsiveImages\Source;
use Statamic\Console\RunsInPlease;
use Statamic\Contracts\Assets\Asset;
use Statamic\Contracts\Assets\AssetRepository;
use Statamic\Tags\Parameters;

class GenerateResponsiveVersionsCommand extends Command
{
    use RunsInPlease;

    protected $signature = 'statamic:responsive:generate';

    protected $description = 'Generate responsive images';

    public function handle(AssetRepository $assets): void
    {
        if (! config('statamic.assets.image_manipulation.cache')) {
            $this->error('Caching is not enabled for image manipulations, generating them will have no benefit.');

            return;
        }

        $excludedContainers = config('statamic.responsive-images.excluded_containers', []);

        $assets = $assets->all()->filter(fn (Asset $asset) => $asset->isImage()
            && $asset->extension() !== 'svg'
            && ! in_array($asset->container()->handle(), $excludedContainers));

        $this->info("Generating responsive image versions for {$assets->count()} assets.");

        $this->getOutput()->progressStart($assets->count());

        $assets->each(function (Asset $asset) {
            $responsive = new Responsive($asset, new Parameters());

            $dimensions = app(DimensionCalculator::class)
                ->calculateForImgTag($responsive->defaultBreakpoint());

            dispatch(app(GenerateImageJob::class, [
                'asset' => $responsive->asset,
                'params' => ['width' => $dimensions->width, 'height' => $dimensions->height],
            ]));

            $responsive->breakPoints()->each(function (Breakpoint $breakpoint) {
                $breakpoint->sources()->each(fn (Source $source) => $source->dispatchImageJobs());
            });

            $this->getOutput()->progressAdvance();
        });

        $this->getOutput()->progressFinish();
        $this->info('All jobs dispatched.');
    }
}
