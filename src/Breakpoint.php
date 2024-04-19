<?php

namespace Spatie\ResponsiveImages;

use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use League\Flysystem\FilesystemException;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Contracts\Assets\Asset;
use Statamic\Facades\Blink;
use Statamic\Facades\Glide as GlideManager;
use Statamic\Imaging\ImageGenerator;
use Statamic\Support\Str;

/**
 * @property-read \Statamic\Assets\Asset $asset
 * @property-read string $label
 * @property-read int $minWidth
 * @property-read array $parameters
 * @property-read string $widthUnit
 */
class Breakpoint implements Arrayable
{
    /** @var Asset */
    public Asset $asset;

    /** @var string */
    public string $label;

    /**
     * @var int The minimum width of when the breakpoint starts
     */
    public int $minWidth;

    /** @var array */
    public array $parameters;

    /** @var string */
    public string $widthUnit;

    /** @var Collection<Source> */
    private Collection $sources;

    public function __construct(Asset $asset, string $label, int $breakpointMinWidth, array $breakpointParams)
    {
        $this->asset = $asset;
        $this->label = $label;
        $this->minWidth = $breakpointMinWidth;
        $this->parameters = $breakpointParams;
        $this->widthUnit = config('statamic.responsive-images.breakpoint_unit', 'px');
    }

    public function __set($name, $value): void
    {
        throw new Exception(sprintf('Cannot modify property %s', $name));
    }

    /**
     * @return Collection<Source>
     */
    public function sources(): Collection
    {
        if (isset($this->sources)) {
            return $this->sources;
        }

        $formats = collect(['avif', 'webp', 'original']);

        $breakpointParams = $this->parameters;

        return $this->sources = $formats->filter(function ($format) use ($breakpointParams) {
            if ($format === 'original') {
                return true;
            }

            if (isset($breakpointParams[$format])) {
                return $breakpointParams[$format];
            }

            if (config('statamic.responsive-images.' . $format, false)) {
                return true;
            }

            return false;
        })->map(function ($format) {
            return new Source($this, $format);
        });
    }

    /**
     * Get only Glide params.
     *
     * @param string|null $format
     * @return array
     */
    public function getImageManipulationParams(string $format = null): array
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

    private function getWidth(): int|null
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
        if ($format === 'original') {
            $format = null;
        }

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
            'minWidth' => $this->minWidth,
            'widthUnit' => $this->widthUnit,
            'sources' => $this->sources()->map(function (Source $source) use ($args) {
                return $source->toGql($args);
            })->all(),
            // TODO: There is no neat way to separate placeholder string from srcset string,
            // TODO: cause placeholder argument affects both.
            'placeholder' => (isset($args['placeholder']) && $args['placeholder'] === true) ? $this->placeholder() : null,
        ];

        // Check if DimensionCalculator is instance of ResponsiveDimensionCalculator
        // as ratio is only property applicable only for this DimensionCalculator
        if (app(DimensionCalculator::class) instanceof ResponsiveDimensionCalculator) {
            $data['ratio'] = app(DimensionCalculator::class)->breakpointRatio($this->asset, $this);
        }

        return $data;
    }

    private function placeholder(): string
    {
        $dimensions = app(DimensionCalculator::class)
            ->calculateForPlaceholder($this);

        $blinkKey = "placeholder-{$this->asset->id()}-{$dimensions->width}-{$dimensions->height}";

        return Blink::once($blinkKey, function () use ($dimensions) {
            $imageGenerator = app(ImageGenerator::class);

            $params = [
                'w' => $dimensions->getWidth(),
                'h' => $dimensions->getHeight(),
                'blur' => 5,
                // Arbitrary parameter to change md5 hash for Glide manipulation cache key
                // to force Glide to generate new manipulated image if cache setting changes.
                // TODO: Remove this line once the issue has been resolved in statamic/cms package
                'cache' => Config::get('statamic.assets.image_manipulation.cache', false),
            ];

            try {
                $manipulationPath = $imageGenerator->generateByAsset($this->asset, $params);
            } catch (NotFoundHttpException $e) {
                return '';
            }

            $base64Image = $this->readImageToBase64($manipulationPath);

            if (! $base64Image) {
                return '';
            }

            $placeholderSvg = view('responsive-images::placeholderSvg', [
                'width' => $dimensions->getWidth(),
                'height' => $dimensions->getHeight(),
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

        // TODO: 32w value is hardcoded, but it is possible with custom DimensionCalculator to have different width,
        // TODO: replace with dynamic value.
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
            $isSsgRunning = App::runningInConsole() &&
                Str::startsWith(Arr::get(request()->server(), 'argv.1'), ['statamic:ssg:generate', 'ssg:generate']);

            if (config('app.debug') || $isSsgRunning) {
                throw $e;
            }

            logger()->error($e->getMessage());

            return null;
        }

        return 'data:' . $assetMimeType . ';base64,' . base64_encode($assetContent);
    }
}
