<?php

namespace Spatie\ResponsiveImages;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use League\Flysystem\FilesystemException;
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

    /**
     * @var int The minimum width of when the breakpoint starts
     */
    public $breakpointMinValue;

    /** @var array */
    public $breakpointParams;

    /** @var string */
    public $unit;

    public function __construct(Asset $asset, string $label, int $breakpointMinValue, array $breakpointParams)
    {
        $this->asset = $asset;
        $this->label = $label;
        $this->breakpointMinValue = $breakpointMinValue;
        $this->breakpointParams = $breakpointParams;
        $this->unit = config('statamic.responsive-images.breakpoint_unit', 'px');
    }

    public function getMediaString(): string
    {
        if (! $this->breakpointMinValue) {
            return '';
        }

        return "(min-width: {$this->breakpointMinValue}{$this->unit})";
    }

    public function getSrcSet(bool $includePlaceholder = true, string $format = null): string
    {
        return $this->getDimensions()
            ->map(function (Dimensions $dimensions) use ($format) {
                return "{$this->buildImageJob($dimensions->width, $dimensions->height, $format)->handle()} {$dimensions->width}w";
            })
            ->when($includePlaceholder, function (Collection $dimensions) {
                $placeholderSrc = $this->placeholderSrc();

                if (empty($placeholderSrc)) {
                    return $dimensions;
                }

                return $dimensions->prepend($placeholderSrc);
            })
            ->implode(', ');
    }

    /**
     * Get only Glide params.
     *
     * @param string|null $format
     * @return array
     */
    public function getImageManipulationParams(string $format = null): array
    {
        $params = $this->getGlideParams();

        if ($format) {
            $params['fm'] = $format;
        }

        $quality = $this->getFormatQuality($format);

        if ($quality) {
            $params['q'] = $quality;
        }

        $crop = $this->getCropFocus($params);

        if ($crop) {
            $params['fit'] = $crop;
        }

        // There are two ways to pass in width, so we just use one: "width"
        if (isset($params['w'])) {
            $params['width'] = $params['width'] ?? $params['w'];
            unset($params['w']);
        }

        // Same for height
        if (isset($params['h'])) {
            $params['height'] = $params['height'] ?? $params['height'];
            unset($params['h']);
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
        $glideParamsQualityValue = $this->breakpointParams['glide:quality'] ?? $this->breakpointParams['glide:q'] ?? null;

        if ($glideParamsQualityValue) {
            return intval($glideParamsQualityValue);
        }

        if ($format === null) {
            $format = $this->asset->extension();
        }

        if (isset($this->breakpointParams['quality:' . $format])) {
            return intval($this->breakpointParams['quality:' . $format]);
        }

        $configQualityValue = config('statamic.responsive-images.quality.' . $format);

        if ($configQualityValue !== null) {
            return intval($configQualityValue);
        }

        return null;
    }

    /**
     * @return Collection<Dimensions>
     */
    private function getDimensions(): Collection
    {
        return app(DimensionCalculator::class)->calculate($this->asset, $this);
    }

    public function buildImageJob(int $width, ?int $height = null, ?string $format = null): GenerateImageJob
    {
        $params = $this->getImageManipulationParams($format);

        $params['width'] = $width;

        if ($height) {
            $params['height'] = $height;
        }

        return app(GenerateImageJob::class, ['asset' => $this->asset, 'params' => $params]);
    }

    public function dispatchImageJobs(): void
    {
        $this->getDimensions()
            ->map(function (Dimensions $dimensions) {
                dispatch($this->buildImageJob($dimensions->width, $dimensions->height));
                if (config('statamic.responsive-images.webp', true)) {
                    dispatch($this->buildImageJob($dimensions->width, $dimensions->height, 'webp'));
                }

                if (config('statamic.responsive-images.avif', false)) {
                    dispatch($this->buildImageJob($dimensions->width, $dimensions->height, 'avif'));
                }
            });
    }

    public function toArray(): array
    {
        return [
            'asset' => $this->asset,
            'label' => $this->label,
            'value' => $this->breakpointMinValue,
            'media' => $this->getMediaString(),
            'parameters' => $this->breakpointParams,
            'unit' => $this->unit,
        ];
    }

    private function getGlideParams(): array
    {
        return collect($this->breakpointParams)
            ->filter(function ($value, $name) {
                return Str::contains($name, 'glide:');
            })
            ->mapWithKeys(function ($value, $name) {
                return [str_replace('glide:', '', $name) => $value];
            })
            ->toArray();
    }

    public function toGql(array $args): array
    {
        $data = [
            'asset' => $this->asset,
            'label' => $this->label,
            'value' => $this->breakpointMinValue,
            'unit' => $this->unit,
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

        // Check if DimensionCalculator is instance of ResponsiveDimensionCalculator
        // as ratio is only property applicable only for this DimensionCalculator
        if (app(DimensionCalculator::class) instanceof ResponsiveDimensionCalculator) {
            $data['ratio'] = app(DimensionCalculator::class)->breakpointRatio($this->asset, $this);
        }

        return $data;
    }

    public function placeholder(): string
    {
        return Blink::once("placeholder-{$this->asset->id()}", function () {
            $imageGenerator = app(ImageGenerator::class);

            $dimensions = app(DimensionCalculator::class)
                ->calculateForPlaceholder($this->asset, $this)
                ->toArray();

            $manipulationPath = $imageGenerator->generateByAsset($this->asset, array_merge($dimensions, [
                'blur' => 5,
                // Arbitrary parameter to change md5 hash for Glide manipulation cache key
                // to force Glide to generate new manipulated image if cache setting changes.
                // TODO: Remove this line once the issue has been resolved in statamic/cms package
                'cache' => Config::get('statamic.assets.image_manipulation.cache', false),
            ]));

            $base64Image = $this->readImageToBase64($manipulationPath);

            if (! $base64Image) {
                return '';
            }

            $placeholderSvg = view('responsive-images::placeholderSvg', array_merge($dimensions, [
                'image' => $base64Image,
                'asset' => $this->asset->toAugmentedArray(),
            ]))->render();

            return 'data:image/svg+xml;base64,' . base64_encode($placeholderSvg);
        });
    }

    private function placeholderSrc(): string
    {
        $placeholder = $this->placeholder();

        if (empty($placeholder)) {
            return '';
        }

        return $placeholder . ' 32w';
    }

    private function readImageToBase64($assetPath): string|null
    {
        /**
         * Glide tag has undocumented method for generating data URL that we borrow from
         * @see \Statamic\Tags\Glide::generateGlideDataUrl
         */
        $cache = GlideManager::cacheDisk();

        try {
            $assetContent = $cache->read($assetPath);
            $assetMimeType = $cache->mimeType($assetPath);
        } catch (FilesystemException $e) {
            if (config('app.debug')) {
                throw $e;
            }

            logger()->error($e->getMessage());

            return null;
        }

        return 'data:' . $assetMimeType . ';base64,' . base64_encode($assetContent);
    }
}
