<?php

namespace Spatie\ResponsiveImages;

use Illuminate\Support\Collection;
use Spatie\ResponsiveImages\Jobs\GenerateImageJob;
use Spatie\ResponsiveImages\ValueObjects\Breakpoint;
use Statamic\Assets\Asset;
use Statamic\Facades\Asset as AssetFacade;
use Statamic\Facades\URL;
use Statamic\Fields\Value;
use Statamic\Support\Str;

class Responsive
{
    /** @var Asset */
    public $asset;

    /** @var \Illuminate\Support\Collection */
    public $ratioParameters;

    public function __construct($assetParam, Collection $ratioParameters)
    {
        $this->ratioParameters = $ratioParameters;

        if ($assetParam instanceof Asset) {
            $this->asset = $assetParam;

            return;
        }

        if (is_string($assetParam)) {
            $asset = AssetFacade::findByUrl($assetParam);

            if (! $asset) {
                $asset = AssetFacade::findByPath($assetParam);
            }
        }

        if ($assetParam instanceof Value) {
            $asset = $assetParam->value();

            if ($asset instanceof Collection) {
                $asset = $asset->first();
            }
        }

        if (! isset($asset)) {
            throw new AssetNotFoundException();
        }

        $this->asset = $asset;
    }

    public function breakPoints(): Collection
    {
        $breakpoints = $this->ratioParameters
            ->map(function ($ratio, $param) {
                $breakpoint = Str::contains($param, ':') ? Str::before($param, ':') : 'default';

                if (Str::contains($ratio, '/')) {
                    [$width, $height] = explode('/', $ratio);
                    $ratio = (float) $width / (float) $height;
                }

                $value = config("statamic.responsive-images.breakpoints.$breakpoint");

                if (! $value && $breakpoint !== 'default') {
                    return null;
                }

                return new Breakpoint($breakpoint, $value ?? 0, $ratio);
            })
            ->filter()
            ->values();

        $defaultBreakpoint = $breakpoints->first(function (Breakpoint $breakpoint) {
            return $breakpoint->label === 'default';
        });

        if (! $defaultBreakpoint) {
            $breakpoints->prepend(new Breakpoint('default', 0, $this->asset->width() / $this->asset->height()));
        }

        return $breakpoints;
    }

    public function defaultBreakpoint(): Breakpoint
    {
        return $this->breakPoints()->first(function (Breakpoint $breakpoint) {
            return $breakpoint->label === 'default';
        });
    }

    public function buildImage(int $width, array $params = [], ?float $ratio = null): string
    {
        $params['width'] = $width;

        if (! is_null($ratio)) {
            $params['height'] = $width / $ratio;
        }

        return URL::makeRelative((new GenerateImageJob($this->asset, $params))->handle());
    }

    public function assetHeight(string $breakPointLabel = 'default'): ?float
    {
        if (! $this->asset->width()) {
            return null;
        }

        $breakpoint = $this->breakPoints()->first(function (Breakpoint $breakpoint) use ($breakPointLabel) {
            return $breakpoint->label === $breakPointLabel;
        });

        if (! $breakpoint) {
            return null;
        }

        return $this->asset->width() / $breakpoint->ratio;
    }
}
