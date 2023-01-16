<?php

namespace Spatie\ResponsiveImages;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Spatie\ResponsiveImages\Jobs\GenerateImageJob;

/**
 * Representing <source> tag in HTML
 */
class Source implements Arrayable
{
    public Breakpoint $breakpoint;
    protected string $format;
    protected string $mediaWidthUnit;

    public function __construct(Breakpoint $breakpoint, ?string $format = 'original')
    {
        $this->breakpoint = $breakpoint;
        $this->format = $format;
        $this->mediaWidthUnit = config('statamic.responsive-images.breakpoint_unit', 'px');
    }

    public function getMimeType(): string|null
    {
        $mimeTypesBySetFormat = [
            'webp' => 'image/webp',
            'avif' => 'image/avif',
        ];

        if (isset($mimeTypesBySetFormat[$this->format])) {
            return $mimeTypesBySetFormat[$this->format];
        }

        return $this->breakpoint->asset->mimeType();
    }

    /**
     * @param string|null $format
     * @param bool|null $includePlaceholder
     * @return string
     */
    public function getSrcSet(string $format = null, ?bool $includePlaceholder = null): string
    {
        // In order of importance: override (e.g. from GraphQL), breakpoint param, config
        $includePlaceholder = $includePlaceholder
            ?? $this->breakpoint->breakpointParams['placeholder']
            ?? config('statamic.responsive-images.placeholder', false);

        return $this->getDimensions()
            ->map(function (Dimensions $dimensions) use ($format) {
                return "{$this->buildImageJob($dimensions->width, $dimensions->height, $this->format)->handle()} {$dimensions->width}w";
            })
            ->when($includePlaceholder, function (Collection $dimensions) {
                $placeholderSrc = $this->breakpoint->placeholderSrc();

                if (empty($placeholderSrc)) {
                    return $dimensions;
                }

                return $dimensions->prepend($placeholderSrc);
            })
            ->implode(', ');
    }

    public function buildImageJob(int $width, ?int $height = null, ?string $format = null): GenerateImageJob
    {
        $params = $this->breakpoint->getImageManipulationParams($format);

        $params['width'] = $width;

        if ($height) {
            $params['height'] = $height;
        }

        return app(GenerateImageJob::class, ['asset' => $this->breakpoint->asset, 'params' => $params]);
    }

    public function getMediaString(): null|string
    {
        if (! $this->breakpoint->minWidth) {
            return null;
        }

        return "(min-width: {$this->breakpoint->minWidth}{$this->mediaWidthUnit})";
    }

    /**
     * @return Collection<Dimensions>
     */
    private function getDimensions(): Collection
    {
        return app(DimensionCalculator::class)->calculate($this);
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function dispatchImageJobs(): void
    {
        $format = $this->format === 'original' ? null : $this->format;

        $this->getDimensions()->map(function (Dimensions $dimensions) use ($format) {
            dispatch($this->buildImageJob($dimensions->width, $dimensions->height, $format));
        });
    }

    public function toGql(array $args)
    {
        return $this->toArray();
    }

    public function toArray()
    {
        return [
            'format' => $this->format,
            'mimeType' => $this->getMimeType(),
            'media' => $this->getMediaString(),
            'mediaWidthUnit' => $this->mediaWidthUnit,
            'minWidth' => $this->breakpoint->minWidth,
            'srcSet' => $this->getSrcSet(),
        ];
    }
}
