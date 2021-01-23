<?php

namespace Spatie\ResponsiveImages;

use Illuminate\Support\Collection;
use League\Glide\Server;
use Spatie\ResponsiveImages\Jobs\GenerateImageJob;
use Statamic\Facades\URL;
use Statamic\Fields\Value;
use Statamic\Imaging\ImageGenerator;
use Statamic\Support\Str;
use Statamic\Tags\Tags;

class Responsive extends Tags
{
    /** @var \League\Glide\Server */
    private $server;

    /** @var \Statamic\Imaging\ImageGenerator */
    private $imageGenerator;

    /** @var \Spatie\ResponsiveImages\WidthCalculator */
    private $widthCalculator;

    /** @var \Statamic\Assets\Asset */
    private $asset;

    /** @var bool */
    private $includePlaceholder;

    /** @var bool */
    private $includeWebp;

    public function __construct(Server $server, ImageGenerator $imageGenerator, WidthCalculator $widthCalculator)
    {
        $this->server = $server;
        $this->imageGenerator = $imageGenerator;
        $this->widthCalculator = $widthCalculator;
    }

    public static function render(...$arguments)
    {
        $asset = $arguments[0];
        $parameters = $arguments[1] ?? [];

        /** @var \Spatie\ResponsiveImages\Responsive $responsive */
        $responsive = app(Responsive::class);
        $responsive->setContext(['url' => $asset]);
        $responsive->tag = 'responsive:url';
        $responsive->setParameters($parameters);

        return $responsive->__call('responsive:url', []);
    }

    public function __call($method, $args)
    {
        $tag = explode(':', $this->tag, 2)[1];
        $this->asset = $this->context->get($tag);
        $this->includePlaceholder = $this->params['placeholder'] ?? true;
        $this->includeWebp = $this->params['webp'] ?? true;

        if (is_string($this->asset)) {
            $this->asset = \Statamic\Facades\Asset::findByUrl($this->asset);
        }

        if ($this->asset instanceof Value) {
            $this->asset = $this->asset->value();

            if ($this->asset instanceof Collection) {
                $this->asset = $this->asset->first();
            }
        }

        if (! $this->asset) {
            return '';
        }

        if ($this->asset->extension() === "svg") {
            return view('responsive-images::responsiveImage', [
                'attributeString' => $this->getAttributeString(),
                'src' => $this->asset->url(),
                'width' => $this->asset->width(),
                'height' => $this->assetHeight(),
                'asset' => $this->asset->toAugmentedArray(),
            ])->render();
        }

        $this->widths = $this->widthCalculator->calculateWidthsFromAsset($this->asset)->sort();

        return view('responsive-images::responsiveImage', [
            'attributeString' => $this->getAttributeString(),
            'placeholder' => $this->includePlaceholder,
            'src' => $this->asset->url(),
            'sources' => $this->buildSources(),
            'width' => $this->asset->width(),
            'height' => $this->assetHeight(),
            'asset' => $this->asset->toAugmentedArray(),
        ])->render();
    }

    private function getAttributeString(): string
    {
        return collect($this->params)
            ->except($this->ratioParams()->keys()->merge(['placeholder', 'webp']))
            ->reject(function ($value, $name) {
                return Str::contains($name, 'glide:');
            })
            ->map(function ($value, $name) {
                return $name . '="' . $value . '"';
            })->implode(' ');
    }

    private function ratioParams(): Collection
    {
        return $this->params->filter(function ($value, $key) {
            return Str::endsWith($key, 'ratio');
        });
    }

    private function ratios(): Collection
    {
        $ratios = $this->ratioParams()->mapWithKeys(function ($ratio, $param) {
            $breakpoint = Str::contains($param, ':') ? Str::before($param, ':') : 'default';

            if (Str::contains($ratio, '/')) {
                [$width, $height] = explode('/', $ratio);
                $ratio = (float) $width / (float) $height;
            }

            return [$breakpoint => (float) $ratio];
        });

        if (! $ratios->has('default')) {
            $ratios->put('default', $this->assetRatio());
        }

        return $ratios;
    }

    private function assetRatio(): float
    {
        return $this->asset->width() / $this->asset->height();
    }

    private function assetHeight(): ?float
    {
        if (! $this->asset->width()) {
            return null;
        }

        return $this->asset->width() / $this->ratios()->get('default');
    }

    private function media(string $breakpoint): ?string
    {
        $screenSize = config("statamic.responsive-images.breakpoints.$breakpoint");
        $breakpointUnit = config("statamic.responsive-images.breakpoint_unit");

        return $screenSize ? "(min-width: {$screenSize}{$breakpointUnit})" : null;
    }

    private function sources(): Collection
    {
        return $this->ratios()->map(function ($ratio, $breakpoint) {
            return [
                'breakpoint' => config("statamic.responsive-images.breakpoints.$breakpoint"),
                'media' => $this->media($breakpoint),
                'ratio' => $ratio,
            ];
        })->sortByDesc('breakpoint');
    }

    private function buildSources(): array
    {
        return $this->sources()->map(function ($item) {
            return [
                'media' => $item['media'],
                'srcSet' => $this->buildSrcSet($item['ratio'], $this->placeholder($item['ratio'])),
                'srcSetWebp' => $this->includeWebp ? $this->buildSrcSet($item['ratio'], $this->placeholder($item['ratio']), 'webp') : null,
            ];
        })->values()->toArray();
    }

    private function placeholder(string $ratio): ?string
    {
        if (! $this->includePlaceholder) {
            return null;
        }

        $base64Svg = base64_encode($this->placeholderSvg($ratio));

        return "data:image/svg+xml;base64,{$base64Svg} 32w";
    }

    private function placeholderSvg(float $ratio): string
    {
        $path = $this->imageGenerator->generateByAsset($this->asset, [
            'w' => 32,
            'h' => 32 / $ratio,
            'blur' => 5,
        ]);

        $source = base64_encode($this->server->getCache()->read($path));
        $base64Placeholder = "data:{$this->server->getCache()->getMimetype($path)};base64,{$source}";

        return view('responsive-images::placeholderSvg', [
            'width' => $this->asset->width(),
            'height' => $this->asset->width() / $ratio,
            'image' => $base64Placeholder,
            'asset' => $this->asset->toAugmentedArray(),
        ])->render();
    }

    private function buildSrcSet(float $ratio = null, string $placeholder = null, string $format = null): string
    {
        $params = $this->getGlideParams();

        if ($format) {
            $params['fm'] = $format;
        }

        /* We don't want any heights specified other than our own */
        unset($params['height']);
        unset($params['h']);

        return $this->widths
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
            ->map(function (int $width) use ($ratio, $params) {
                return "{$this->buildImage($width, $ratio, $params)} {$width}w";
            })
            ->when($placeholder, function (Collection $widths) use ($placeholder) {
                return $widths->prepend($placeholder);
            })
            ->implode(', ');
    }

    private function getGlideParams(): array
    {
        return collect($this->params)
            ->filter(function ($value, $name) {
                return Str::contains($name, 'glide:');
            })
            ->mapWithKeys(function ($value, $name) {
                return [str_replace('glide:', '', $name) => $value];
            })
            ->toArray();
    }

    private function buildImage(int $width, float $ratio = null, array $params)
    {
        $params['width'] = $width;

        if ($ratio) {
            $params['height'] = $width / $ratio;
        }

        return URL::makeRelative((new GenerateImageJob($this->asset, $params))->handle());
    }
}
