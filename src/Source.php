<?php

namespace Spatie\ResponsiveImages;

use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Spatie\ResponsiveImages\Jobs\GenerateImageJob;

class Source implements Arrayable
{
    protected readonly string $mediaWidthUnit;

    public function __construct(
        public Breakpoint $breakpoint,
        protected string $format = 'original',
    ) {
        $this->mediaWidthUnit = config('statamic.responsive-images.breakpoint_unit', 'px');
    }

    public function __set(string $name, mixed $value): void
    {
        throw new Exception("Cannot modify property {$name}");
    }

    public function getMimeType(): ?string
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

    public function getSrcSet(?string $format = null, ?bool $includePlaceholder = null): ?string
    {
        $includePlaceholder ??= $this->includePlaceholder();

        $dimensionsCollection = $this->getDimensions();

        if ($dimensionsCollection->isEmpty()) {
            return null;
        }

        return $dimensionsCollection
            ->map(fn (Dimensions $dimensions) => "{$this->buildImageJob($dimensions->width, $dimensions->height, $this->format)->handle()} {$dimensions->width}w")
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
        $params['height'] = $height;

        return app(GenerateImageJob::class, ['asset' => $this->breakpoint->asset, 'params' => $params]);
    }

    public function getMediaString(): ?string
    {
        if (! $this->breakpoint->minWidth) {
            return null;
        }

        return "(min-width: {$this->breakpoint->minWidth}{$this->mediaWidthUnit})";
    }

    /** @return Collection<int, Dimensions> */
    private function getDimensions(): Collection
    {
        return app(DimensionCalculator::class)->calculateForBreakpoint($this);
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function dispatchImageJobs(): void
    {
        $format = $this->format === 'original' ? null : $this->format;

        $this->getDimensions()->each(function (Dimensions $dimensions) use ($format) {
            dispatch($this->buildImageJob($dimensions->width, $dimensions->height, $format));
        });

        if ($this->includePlaceholder()) {
            dispatch($this->breakpoint->buildPlaceholderJob());
        }
    }

    private function includePlaceholder(): bool
    {
        return $this->breakpoint->parameters['placeholder']
            ?? config('statamic.responsive-images.placeholder', false);
    }

    public function toGql(array $args): array
    {
        return $this->toArray();
    }

    public function toArray(): array
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
