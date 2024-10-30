<?php

namespace Spatie\ResponsiveImages\Jobs;

use Illuminate\Bus\Queueable;
use Statamic\Contracts\Assets\Asset;
use Statamic\Imaging\ImageGenerator;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Spatie\ResponsiveImages\Breakpoint;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Statamic\Exceptions\NotFoundHttpException;
use Spatie\ResponsiveImages\DimensionCalculator;

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
