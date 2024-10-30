<?php

namespace Spatie\ResponsiveImages\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Spatie\ResponsiveImages\Breakpoint;
use Spatie\ResponsiveImages\DimensionCalculator;
use Statamic\Contracts\Assets\Asset;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Imaging\ImageGenerator;

class GeneratePlaceholderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected Asset $asset, protected Breakpoint $breakpoint)
    {
        $this->queue = config('statamic.responsive-images.queue', 'default');
    }

    public function handle(): string
    {
        $dimensions = app(DimensionCalculator::class)
            ->calculateForPlaceholder($this->breakpoint);

        $params = [
            'w' => $dimensions->getWidth(),
            'h' => $dimensions->getHeight(),
            'blur' => 5,
            /**
             * Arbitrary parameter to change md5 hash for Glide manipulation cache key
             * to force Glide to generate new manipulated image if cache setting changes.
             * TODO: Remove this line once the issue has been resolved in statamic/cms package
             */
            'cache' => Config::get('statamic.assets.image_manipulation.cache', false),
        ];

        try {
            return app(ImageGenerator::class)->generateByAsset($this->asset, $params);
        } catch (NotFoundHttpException $e) {
            return '';
        }
    }
}
