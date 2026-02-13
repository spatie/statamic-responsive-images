<?php

namespace Spatie\ResponsiveImages;

use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use League\Flysystem\FilesystemException;
use Spatie\ResponsiveImages\Jobs\GeneratePlaceholderJob;
use Statamic\Contracts\Assets\Asset;
use Statamic\Facades\Blink;
use Statamic\Facades\Glide as GlideManager;
use Statamic\Support\Str;

class Breakpoint implements Arrayable
{
    public readonly string $widthUnit;

    /** @var Collection<int, Source> */
    private Collection $sources;

    public function __construct(
        public Asset $asset,
        public string $label,
        public int $minWidth,
        public array $parameters,
    ) {
        $this->widthUnit = config('statamic.responsive-images.breakpoint_unit', 'px');
    }

    public function __set(string $name, mixed $value): void
    {
        throw new Exception("Cannot modify property {$name}");
    }

    /** @return Collection<int, Source> */
    public function sources(): Collection
    {
        if (isset($this->sources)) {
            return $this->sources;
        }

        return $this->sources = collect(['avif', 'webp', 'original'])
            ->filter(function (string $format) {
                if ($format === 'original') {
                    return true;
                }

                return $this->parameters[$format]
                    ?? config("statamic.responsive-images.{$format}", false);
            })
            ->map(fn (string $format) => new Source($this, $format));
    }

    public function getImageManipulationParams(?string $format = null): array
    {
        $glideParams = $this->getGlideParams();

        $params = [];

        if ($format && $format !== 'original') {
            $params['fm'] = $format;
        }

        $quality = $this->getFormatQuality($format);

        if ($quality) {
            $params['q'] = $quality;
        }

        $crop = $this->getCropFocus($glideParams);

        if ($crop) {
            $params['fit'] = $crop;
        }

        $width = $this->getWidth();

        if ($width) {
            $params['width'] = $width;
            unset($glideParams['width'], $glideParams['w']);
        }

        if (isset($glideParams['height']) || isset($glideParams['h'])) {
            $params['height'] = $glideParams['height'] ?? $glideParams['h'];
            unset($glideParams['height'], $glideParams['h']);
        }

        $params = array_merge($glideParams, $params);

        return $params;
    }

    private function getWidth(): ?int
    {
        $width = null;

        if (isset($this->parameters['glide:width']) || isset($this->parameters['glide:w'])) {
            $width = $this->parameters['glide:width'] ?? $this->parameters['glide:w'];
        }

        if ($width === null && (isset($this->parameters['width']) || isset($this->parameters['w']))) {
            $width = $this->parameters['width'] ?? $this->parameters['w'];
        }

        return $width;
    }

    private function getCropFocus(array $params): ?string
    {
        if (
            Config::get('statamic.assets.auto_crop') === false
            || (array_key_exists('fit', $params) && $params['fit'] !== 'crop_focal')
        ) {
            return null;
        }

        return "crop-{$this->asset->get('focus', '50-50')}";
    }

    private function getFormatQuality(?string $format = null): ?int
    {
        if ($format === 'original') {
            $format = null;
        }

        $glideParamsQualityValue = $this->parameters['glide:quality'] ?? $this->parameters['glide:q'] ?? null;

        if ($glideParamsQualityValue) {
            return (int) $glideParamsQualityValue;
        }

        $format ??= $this->asset->extension();

        if (isset($this->parameters["quality:{$format}"])) {
            return (int) $this->parameters["quality:{$format}"];
        }

        $configQualityValue = config("statamic.responsive-images.quality.{$format}");

        if ($configQualityValue !== null) {
            return (int) $configQualityValue;
        }

        return null;
    }

    public function toArray(): array
    {
        return [
            'asset' => $this->asset,
            'label' => $this->label,
            'minWidth' => $this->minWidth,
            'widthUnit' => $this->widthUnit,
            'parameters' => $this->parameters,
        ];
    }

    private function getGlideParams(): array
    {
        return collect($this->parameters)
            ->filter(fn ($value, string $name) => Str::contains($name, 'glide:'))
            ->mapWithKeys(fn ($value, string $name) => [str_replace('glide:', '', $name) => $value])
            ->toArray();
    }

    public function toGql(array $args): array
    {
        $data = [
            'asset' => $this->asset,
            'label' => $this->label,
            'minWidth' => $this->minWidth,
            'widthUnit' => $this->widthUnit,
            'sources' => $this->sources()->map(fn (Source $source) => $source->toGql($args))->all(),
            'placeholder' => ($args['placeholder'] ?? false) === true ? $this->placeholder() : null,
        ];

        $calculator = app(DimensionCalculator::class);

        if ($calculator instanceof ResponsiveDimensionCalculator) {
            $data['ratio'] = $calculator->breakpointRatio($this->asset, $this);
        }

        return $data;
    }

    public function buildPlaceholderJob(): GeneratePlaceholderJob
    {
        return app(GeneratePlaceholderJob::class, ['asset' => $this->asset, 'breakpoint' => $this]);
    }

    private function placeholder(): string
    {
        $dimensions = app(DimensionCalculator::class)
            ->calculateForPlaceholder($this);

        $blinkKey = "placeholder-{$this->asset->id()}-{$dimensions->width}-{$dimensions->height}";

        return Blink::once($blinkKey, function () use ($dimensions) {
            $manipulationPath = $this->buildPlaceholderJob()->handle();

            $base64Image = $this->readImageToBase64($manipulationPath);

            if (! $base64Image) {
                return '';
            }

            $placeholderSvg = view('responsive-images::placeholderSvg', [
                'width' => $dimensions->width,
                'height' => $dimensions->height,
                'image' => $base64Image,
                'asset' => $this->asset->toAugmentedArray(),
            ])->render();

            return 'data:image/svg+xml;base64,' . base64_encode($placeholderSvg);
        });
    }

    public function placeholderSrc(): string
    {
        $placeholder = $this->placeholder();

        if (empty($placeholder)) {
            return '';
        }

        return "{$placeholder} 32w";
    }

    private function readImageToBase64(string $assetPath): ?string
    {
        $cache = GlideManager::cacheDisk();

        try {
            $assetContent = $cache->read($assetPath);
            $assetMimeType = $cache->mimeType($assetPath);
        } catch (FilesystemException $e) {
            $isSsgRunning = App::runningInConsole() &&
                Str::startsWith(Arr::get(request()->server(), 'argv.1'), ['statamic:ssg:generate', 'ssg:generate']);

            if (config('app.debug') || $isSsgRunning) {
                throw $e;
            }

            logger()->error($e->getMessage());

            return null;
        }

        return "data:{$assetMimeType};base64," . base64_encode($assetContent);
    }
}
