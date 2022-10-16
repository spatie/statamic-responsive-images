<?php

namespace Spatie\ResponsiveImages;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Spatie\ResponsiveImages\Jobs\GenerateImageJob;
use Statamic\Contracts\Assets\Asset;
use Statamic\Facades\Blink;
use Statamic\Facades\Glide as GlideManager;
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
        return $this->getWidths()
            ->map(function (int $width) use ($format) {
                return "{$this->buildImageJob($width, $format, $this->ratio)->handle()} {$width}w";
            })
            ->when($includePlaceholder, function (Collection $widths) {
                return $widths->prepend($this->placeholderSrc());
            })
            ->implode(', ');
    }

    private function getParams(string $format = null): array
    {
        $params = $this->getGlideParams();

        if ($format) {
            $params['fm'] = $format;
        }

        $quality = $this->getFormatQuality($format);

        if ($quality) {
            $params['q'] = $quality;
        }

        /* We don't want any heights specified other than our own */
        unset($params['height']);
        unset($params['h']);

        $crop = $this->getCropFocus($params);

        if ($crop) {
            $params['fit'] = $crop;
        }

        return $params;
    }

    private function getCropFocus($params): string|null
    {
        if (
            Config::get('statamic.assets.auto_crop') === false
            || (array_key_exists('fit', $params) && $params['fit'] !== 'crop_focal')
        ) {
            return null;
        }

        return "crop-" . $this->asset->get('focus', '50-50');
    }

    /**
     * Get format quality by the following order: glide parameter, quality parameter and then config values.
     *
     * @param string|null $format
     * @return int|null
     */
    private function getFormatQuality(string $format = null): int|null
    {
        // Backwards compatible if someone used glide:quality to adjust quality
        $glideParamsQualityValue = $this->parameters['glide:quality'] ?? $this->parameters['glide:q'] ?? null;

        if ($glideParamsQualityValue) {
            return intval($glideParamsQualityValue);
        }

        if ($format === null) {
            $format = $this->asset->extension();
        }

        if (isset($this->parameters['quality:' . $format])) {
            return intval($this->parameters['quality:' . $format]);
        }

        $configQualityValue = config('statamic.responsive-images.quality.' . $format);

        if ($configQualityValue !== null) {
            return intval($configQualityValue);
        }

        return null;
    }

    private function getWidths(): Collection
    {
        $params = $this->getParams();

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
            });
    }

    public function buildImageJob(int $width, ?string $format = null, ?float $ratio = null): GenerateImageJob
    {
        $params = $this->getParams($format);

        $params['width'] = $width;

        if (! is_null($ratio)) {
            $params['height'] = $width / $ratio;
        }

        return app(GenerateImageJob::class, ['asset' => $this->asset, 'params' => $params]);
    }

    public function dispatchImageJobs(): void
    {
        $this->getWidths()
            ->map(function (int $width) {
                dispatch($this->buildImageJob($width, null, $this->ratio));
                if (config('statamic.responsive-images.webp', true)) {
                    dispatch($this->buildImageJob($width, 'webp', $this->ratio));
                }

                if (config('statamic.responsive-images.avif', false)) {
                    dispatch($this->buildImageJob($width, 'avif', $this->ratio));
                }
            });
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

    public function placeholder(): string
    {
        $base64Svg = base64_encode($this->placeholderSvg());

        return "data:image/svg+xml;base64,{$base64Svg}";
    }

    public function toGql(array $args): array
    {
        $data = [
            'asset' => $this->asset,
            'label' => $this->label,
            'value' => $this->value,
            'unit' => $this->unit,
            'ratio' => $this->ratio,
            'mediaString' => $this->getMediaString(),
            'placeholder' => $args['placeholder'] ? $this->placeholder() : null,
            'srcSet' => $this->getSrcSet($args['placeholder']),
        ];

        if ($args['webp']) {
            $data['srcSetWebp'] = $this->getSrcSet($args['placeholder'], 'webp');
        }

        if ($args['avif']) {
            $data['srcSetAvif'] = $this->getSrcSet($args['placeholder'], 'avif');
        }

        return $data;
    }

    private function placeholderSrc(): string
    {
        return $this->placeholder() . ' 32w';
    }

    private function placeholderSvg(): string
    {
        return Blink::once("placeholder-{$this->asset->id()}-{$this->ratio}", function () {
            $imageGenerator = app(ImageGenerator::class);

            $manipulationPath = $imageGenerator->generateByAsset($this->asset, [
                'w' => 32,
                'h' => round(32 / $this->ratio),
                'blur' => 5,
                // Arbitrary parameter to change md5 hash for Glide manipulation cache key
                // to force Glide to generate new manipulated image if cache setting changes.
                // TODO: Remove this line once the issue has been resolved in statamic/cms package
                'cache' => Config::get('statamic.assets.image_manipulation.cache', false)
            ]);

            /**
             * Glide tag has undocumented method for generating data URL that we borrow from
             * @see \Statamic\Tags\Glide::generateGlideDataUrl
             */
            $cache = GlideManager::cacheDisk();
            $assetContentEncoded = base64_encode($cache->read($manipulationPath));
            $base64Placeholder = 'data:'.$cache->mimeType($manipulationPath).';base64,'.$assetContentEncoded;

            return view('responsive-images::placeholderSvg', [
                'width' => 32,
                'height' => round(32 / $this->ratio),
                'image' => $base64Placeholder,
                'asset' => $this->asset->toAugmentedArray(),
            ])->render();
        });
    }
}
