<?php

namespace Spatie\ResponsiveImages\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Statamic\Contracts\Assets\Asset;
use Statamic\Facades\Image;
use Statamic\Imaging\GlideImageManipulator;

class GenerateImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var \Statamic\Contracts\Assets\Asset */
    protected $asset;

    /** @var int */
    private $width;

    /** @var string|null */
    private $format;

    public function __construct(Asset $asset, int $width, string $format = null)
    {
        $this->asset = $asset;
        $this->width = $width;
        $this->format = $format;
    }

    public function handle(): string
    {
        $manipulator = $this->getManipulator($this->asset);

        if ($this->format) {
            $manipulator->setParam('fm', $this->format);
        }

        return $manipulator->width($this->width)->build();
    }

    private function getManipulator(Asset $asset): GlideImageManipulator
    {
        return Image::manipulate($asset);
    }
}
