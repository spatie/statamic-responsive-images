<?php

namespace Spatie\ResponsiveImages\Fieldtypes;

use Statamic\Facades\AssetContainer;
use Statamic\Fieldtypes\Assets\ImageRule;

class ResponsiveFields
{
    public function __construct(protected ?array $config = null)
    {
    }

    public static function new(?array $config): ResponsiveFields
    {
        return new static($config);
    }

    public function getConfig(): array
    {
        $fields = [];

        $breakpoints = collect($this->config['breakpoints'] ?? [])->sortBy(function ($breakpoint) {
            return config("statamic.responsive-images.breakpoints.{$breakpoint}");
        });

        $breakpoints = array_merge(['default'], $this->config['use_breakpoints'] ? $breakpoints->toArray() : []);

        foreach ($breakpoints as $index => $breakpoint) {
            if (! isset(config('statamic.responsive-images.breakpoints')[$breakpoint]) && $breakpoint !== 'default') {
                continue;
            }

            $isDefault = $breakpoint === 'default';
            $prefix = $isDefault ? '' : "{$breakpoint}:";

            $fields[] = [
                'handle' => "{$prefix}src",
                'field' => [
                    'display' => $this->imageDisplay($index, $breakpoint),
                    'instructions' => $index === 0 ? __('Choose an image to generate responsive versions from.') : '',
                    'type' => 'assets',
                    'localizable' => $this->config['localizable'] ?? false,
                    'container' => $this->config['container'] ?? optional(AssetContainer::all()->first())->handle(),
                    'folder' => $this->config['folder'] ?? '/',
                    'allow_uploads' => $this->config['allow_uploads'],
                    'restrict' => $this->config['restrict'] ?? false,
                    'dynamic' => $this->config['dynamic'] ?? null,
                    'max_files' => 1,
                    'show_set_alt' => false,
                    'mode' => 'list',
                    'width' => $this->config['allow_ratio'] ? ($this->config['allow_fit'] ? 50 : 66) : 100,
                    'required' => in_array('required', $this->config['validate'] ?? []) && $index === 0,
                    'validate' => array_filter([
                        new ImageRule(),
                        ((in_array('sometimes', $this->config['validate'] ?? []) && $index === 0) ? 'sometimes' : null),
                    ]),
                ],
            ];

            if ($this->config['allow_ratio']) {
                $fields[] = [
                    'handle' => "{$prefix}ratio",
                    'field' => [
                        'display' => __('Ratio'),
                        'instructions' => $index === 0 ? __('Accepts float (`1.55`) or fraction (`16/9`).') : '',
                        'type' => 'text',
                        'width' => $this->config['allow_fit'] ? 25 : 33,
                    ],
                ];

                if ($this->config['allow_fit']) {
                    $fields[] = [
                        'handle' => "{$prefix}glide:fit",
                        'field' => [
                            'display' => __('Fit'),
                            'instructions' => $index === 0 ? __('Sets how the image is fitted to its target ratio.') : '',
                            'type' => 'select',
                            'clearable' => true,
                            'default' => null,
                            'options' => [
                                'crop_focal' => __('Focal crop'),
                                'contain' => __('Contain'),
                                'max' => __('Max'),
                                'fill' => __('Fill'),
                                'stretch' => __('Stretch'),
                                'crop' => __('Crop'),
                            ],
                            'width' => 25,
                        ],
                    ];
                }
            }
        }

        return $fields;
    }

    protected function imageDisplay(int $index, string $breakpoint): string
    {
        if (! $this->config['use_breakpoints']) {
            return __('Image');
        }

        if ($index === 0) {
            return __('Default Image');
        }

        return __(':label Breakpoint (:breakpoint:unit)', [
            'label' => strtoupper($breakpoint),
            'breakpoint' => config('statamic.responsive-images.breakpoints')[$breakpoint],
            'unit' => config('statamic.responsive-images.breakpoint_unit', 'px'),
        ]);
    }
}
