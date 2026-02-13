<?php

namespace Spatie\ResponsiveImages\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Statamic\Contracts\Assets\Asset;

abstract class GenerateImageJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        protected Asset $asset,
        protected array $params = [],
    ) {
        $this->queue = config('statamic.responsive-images.queue', 'default');
    }

    public function handle(): string
    {
        return $this->imageUrl();
    }

    public function getParams(): array
    {
        return $this->params;
    }

    abstract protected function imageUrl(): string;
}
