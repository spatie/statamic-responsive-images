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
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        protected Asset $asset,
        protected Breakpoint $breakpoint,
    ) {
        $this->queue = config('statamic.responsive-images.queue', 'default');
    }

    public function handle(): string
    {
        $dimensions = app(DimensionCalculator::class)
            ->calculateForPlaceholder($this->breakpoint);

        $params = [
            'w' => $dimensions->width,
            'h' => $dimensions->height,
            'blur' => 5,
            'cache' => Config::get('statamic.assets.image_manipulation.cache', false),
        ];

        try {
            return app(ImageGenerator::class)->generateByAsset($this->asset, $params);
        } catch (NotFoundHttpException) {
            return '';
        }
    }
}
