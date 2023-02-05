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
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var \Statamic\Contracts\Assets\Asset */
    protected $asset;

    /** @var array */
    protected $params;

    public function __construct(Asset $asset, array $params = [])
    {
        $this->asset = $asset;
        $this->params = $params;

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
