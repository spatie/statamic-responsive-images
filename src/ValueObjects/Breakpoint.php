<?php

namespace Spatie\ResponsiveImages\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;

class Breakpoint implements Arrayable
{
    /** @var string */
    public $label;

    /** @var ?int */
    public $value;

    /** @var float */
    public $ratio;

    /** @var string */
    public $unit;

    public function __construct(string $label, int $value, float $ratio)
    {
        $this->label = $label;
        $this->value = $value;
        $this->ratio = $ratio;
        $this->unit = config('statamic.responsive-images.breakpoint_unit', 'px');
    }

    public function getMediaString(): string
    {
        if (! $this->value) {
            return '';
        }

        return "(min-width: {$this->value}{$this->unit})";
    }

    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'value' => $this->value,
            'media' => $this->getMediaString(),
            'ratio' => $this->ratio,
            'unit' => $this->unit,
        ];
    }
}
