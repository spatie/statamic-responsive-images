<?php

namespace Spatie\ResponsiveImages\Commands;

use Exception;
use Illuminate\Console\Command;
use Spatie\ResponsiveImages\Jobs\GenerateImageJob;
use Spatie\ResponsiveImages\WidthCalculator;
use Statamic\Console\RunsInPlease;
use Statamic\Contracts\Assets\Asset;
use Statamic\Contracts\Assets\AssetRepository;

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

        $assets->each(function (Asset $asset) {
            (new WidthCalculator())
                ->calculateWidthsFromAsset($asset)
                ->map(function (int $width) use ($asset) {
                    try {
                        dispatch(new GenerateImageJob($asset, ['width' => $width]));
                    } catch (Exception $e) {
                        $this->error("Exception while generating responsive asset {$asset->filename()}: {$e->getMessage()}");
                        logger($e);
                    }

                    try {
                        dispatch(new GenerateImageJob($asset, ['width' => $width, 'fm' => 'webp']));
                    } catch (Exception $e) {
                        $this->error("Exception while generating WEBP for asset {$asset->filename()}: {$e->getMessage()}");
                        logger($e);
                    }
                });

            $this->getOutput()->progressAdvance();
        });

        $this->getOutput()->progressFinish();
    }
}
