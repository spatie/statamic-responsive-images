<?php

namespace Spatie\ResponsiveImages;

use Illuminate\Support\Collection;
use League\Flysystem\FileNotFoundException;
use League\Glide\Server;
use Spatie\ResponsiveImages\ValueObjects\Breakpoint;
use Statamic\Imaging\ImageGenerator;
use Statamic\Support\Str;
use Statamic\Tags\Tags;

class ResponsiveTag extends Tags
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

    /** @var \Illuminate\Support\Collection */
    private $widths;

    /** @var \Spatie\ResponsiveImages\Responsive */
    private $responsive;

    public function __construct(
        Server $server,
        ImageGenerator $imageGenerator,
        WidthCalculator $widthCalculator
    ) {
        $this->server = $server;
        $this->imageGenerator = $imageGenerator;
        $this->widthCalculator = $widthCalculator;
    }

    public static function render(...$arguments): string
    {
        $asset = $arguments[0];
        $parameters = $arguments[1] ?? [];

        /** @var \Spatie\ResponsiveImages\ResponsiveTag $responsive */
        $responsive = app(ResponsiveTag::class);
        $responsive->setContext(['url' => $asset]);
        $responsive->tag = 'responsive:url';
        $responsive->setParameters($parameters);

        return $responsive->__call('responsive:url', []);
    }

    public function __call($method, $args): string
    {
        $tag = explode(':', $this->tag, 2)[1];

        try {
            $this->responsive = new Responsive($this->context->get($tag), $this->ratioParameters());
        } catch (AssetNotFoundException $e) {
            return '';
        }

        $this->asset = $this->responsive->asset;

        if ($this->asset->extension() === "svg") {
            return view('responsive-images::responsiveImage', [
                'attributeString' => $this->getAttributeString(),
                'src' => $this->asset->url(),
                'width' => $this->asset->width(),
                'height' => $this->responsive->assetHeight(),
                'asset' => $this->asset->toAugmentedArray(),
            ])->render();
        }

        $this->includePlaceholder = $this->params['placeholder'] ?? true;
        $this->includeWebp = $this->params['webp'] ?? true;
        $this->widths = $this->widthCalculator->calculateWidthsFromAsset($this->asset);

        $sources = $this->responsive->breakPoints()
            ->map(function (Breakpoint $breakpoint) {
                return [
                    'media' => $breakpoint->getMediaString(),
                    'srcSet' => $this->buildSrcSet($breakpoint->ratio, $this->placeholder($breakpoint->ratio)),
                    'srcSetWebp' => $this->includeWebp
                        ? $this->buildSrcSet($breakpoint->ratio, $this->placeholder($breakpoint->ratio), 'webp')
                        : null,
                ];
            })->values()->toArray();

        return view('responsive-images::responsiveImage', [
            'attributeString' => $this->getAttributeString(),
            'placeholder' => $this->includePlaceholder,
            'src' => $this->asset->url(),
            'sources' => $sources,
            'width' => $this->asset->width(),
            'height' => $this->responsive->assetHeight(),
            'asset' => $this->asset->toAugmentedArray(),
        ])->render();
    }

    private function getAttributeString(): string
    {
        return collect($this->params)
            ->except($this->ratioParameters()->keys()->merge(['placeholder', 'webp']))
            ->reject(function ($value, $name) {
                return Str::contains($name, 'glide:');
            })
            ->map(function ($value, $name) {
                return $name . '="' . $value . '"';
            })->implode(' ');
    }

    private function ratioParameters(): Collection
    {
        return $this->params->filter(function ($value, $key) {
            return Str::endsWith($key, 'ratio');
        });
    }

    private function placeholder(string $ratio): string
    {
        if (! $this->includePlaceholder) {
            return '';
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

        try {
            $source = base64_encode($this->server->getCache()->read($path));
            $base64Placeholder = "data:{$this->server->getCache()->getMimetype($path)};base64,{$source}";
        } catch (FileNotFoundException $e) {
            return '';
        }

        return view('responsive-images::placeholderSvg', [
            'width' => $this->asset->width(),
            'height' => $this->asset->width() / $ratio,
            'image' => $base64Placeholder,
            'asset' => $this->asset->toAugmentedArray(),
        ])->render();
    }

    private function buildSrcSet(float $ratio = null, string $placeholder = '', string $format = null): string
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
                return "{$this->responsive->buildImage($width, $params, $ratio)} {$width}w";
            })
            ->when($placeholder !== '', function (Collection $widths) use ($placeholder) {
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
}
