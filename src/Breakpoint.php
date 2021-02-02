<?php

namespace Spatie\ResponsiveImages;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use League\Flysystem\FileNotFoundException;
use League\Glide\Server;
use Spatie\ResponsiveImages\Jobs\GenerateImageJob;
use Statamic\Assets\Asset;
use Statamic\Facades\URL;
use Statamic\Imaging\ImageGenerator;
use Statamic\Support\Str;

class Breakpoint implements Arrayable
{
    /** @var \Statamic\Assets\Asset */
    public $asset;

    /** @var string */
    public $label;

    /** @var ?int */
    public $value;

    /** @var array */
    public $parameters;

    /** @var float */
    public $ratio;

    /** @var string */
    public $unit;

    public function __construct(Asset $asset, string $label, int $value, array $parameters)
    {
        $this->asset = $asset;
        $this->label = $label;
        $this->value = $value;
        $this->parameters = $parameters;
        $this->ratio = $this->parameters['ratio'] ?? $this->asset->width() / $this->asset->height();

        $this->unit = config('statamic.responsive-images.breakpoint_unit', 'px');
    }

    public function getMediaString(): string
    {
        if (! $this->value) {
            return '';
        }

        return "(min-width: {$this->value}{$this->unit})";
    }

    public function getSrcSet(bool $includePlaceholder = true, string $format = null): string
    {
        $params = $this->getGlideParams();

        if ($format) {
            $params['fm'] = $format;
        }

        /* We don't want any heights specified other than our own */
        unset($params['height']);
        unset($params['h']);

        return app(WidthCalculator::class)
            ->calculateWidthsFromAsset($this->asset)
            /* If a width is specified, consider it a max width */
            ->when(isset($params['width']) || isset($params['w']), function ($widths) use ($params) {
                $maxWidth = $params['width'] ?? $params['w'];

                $filtered = $widths->filter(function (int $width) use ($maxWidth) {
                    return $width <= $maxWidth;
                });

                /* We want at least one width to be returned */
                if (! $filtered->count()) {
                    $filtered = collect($maxWidth);
                }

                return $filtered;
            })
            ->map(function (int $width) use ($params) {
                return "{$this->buildImage($width, $params, $this->ratio)} {$width}w";
            })
            ->when($includePlaceholder, function (Collection $widths) {
                return $widths->prepend($this->placeholder());
            })
            ->implode(', ');
    }

    public function buildImage(int $width, array $params = [], ?float $ratio = null): string
    {
        $params['width'] = $width;

        if (! is_null($ratio)) {
            $params['height'] = $width / $ratio;
        }

        return URL::makeRelative((new GenerateImageJob($this->asset, $params))->handle());
    }

    public function toArray(): array
    {
        return [
            'asset' => $this->asset,
            'label' => $this->label,
            'value' => $this->value,
            'media' => $this->getMediaString(),
            'parameters' => $this->parameters,
            'unit' => $this->unit,
        ];
    }

    private function getGlideParams(): array
    {
        return collect($this->parameters)
            ->filter(function ($value, $name) {
                return Str::contains($name, 'glide:');
            })
            ->mapWithKeys(function ($value, $name) {
                return [str_replace('glide:', '', $name) => $value];
            })
            ->toArray();
    }

    private function placeholder(): string
    {
        $base64Svg = base64_encode($this->placeholderSvg());

        return "data:image/svg+xml;base64,{$base64Svg} 32w";
    }

    private function placeholderSvg(): string
    {
        $imageGenerator = app(ImageGenerator::class);
        $server = app(Server::class);

        $path = $imageGenerator->generateByAsset($this->asset, [
            'w' => 32,
            'h' => 32 / $this->ratio,
            'blur' => 5,
        ]);

        try {
            $source = base64_encode($server->getCache()->read($path));
            $base64Placeholder = "data:{$server->getCache()->getMimetype($path)};base64,{$source}";
        } catch (FileNotFoundException $e) {
            return '';
        }

        return view('responsive-images::placeholderSvg', [
            'width' => $this->asset->width(),
            'height' => $this->asset->width() / $this->ratio,
            'image' => $base64Placeholder,
            'asset' => $this->asset->toAugmentedArray(),
        ])->render();
    }
}
