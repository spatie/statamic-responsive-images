<?php

namespace Spatie\ResponsiveImages\Commands;

use Illuminate\Console\Command;
use Spatie\ResponsiveImages\Breakpoint;
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

    public function handle(AssetRepository $assets)
    {
        if (! config('statamic.assets.image_manipulation.cache')) {
            $this->error('Caching is not enabled for image manipulations, generating them will have no benefit.');

            return;
        }

        $assets = $assets->all()->filter(function (Asset $asset) {
            return $asset->isImage() && $asset->extension() !== 'svg';
        });

        $this->info("Generating responsive image versions for {$assets->count()} assets.");

        $this->getOutput()->progressStart($assets->count());

        /** @var \Statamic\Assets\AssetCollection $assets */
        $assets->each(function (Asset $asset) {
            $responsive = new Responsive($asset, new Parameters());

            $responsive->breakPoints()->each(function (Breakpoint $breakpoint) {
                $breakpoint->sources()->each(function (Source $source) {
                    $source->dispatchImageJobs();
                });
            });

            $this->getOutput()->progressAdvance();
        });

        $this->getOutput()->progressFinish();
        $this->info("All jobs dispatched.");
    }
}
