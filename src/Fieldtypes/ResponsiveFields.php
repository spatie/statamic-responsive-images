<?php

namespace Spatie\ResponsiveImages\Fieldtypes;

use Statamic\Facades\AssetContainer;
use Statamic\Fieldtypes\Assets\ImageRule;

class ResponsiveFields
{
    /** @var array|null */
    protected $config;

    public function __construct(?array $config = null)
    {
        $this->config = $config;
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

        $breakpoints = array_merge(['default'], $this->config['use_breakpoints']
            ?  $breakpoints->toArray()
            : []);


        foreach ($breakpoints as $index => $breakpoint) {
            if (! isset(config('statamic.responsive-images.breakpoints')[$breakpoint]) && $breakpoint !== 'default') {
                continue;
            }

            if ($this->config['use_breakpoints']) {
                $fields[] = [
                    'handle' => "{$breakpoint}_section",
                    'field' => [
                        'display' => $index === 0
                            ? __('Default')
                            : __(':label Breakpoint (:breakpoint:unit)', [
                                'label' => strtoupper($breakpoint),
                                'breakpoint' => config('statamic.responsive-images.breakpoints')[$breakpoint],
                                'unit' => config('statamic.responsive-images.breakpoint_unit', 'px'),
                            ]),
                        'instructions' => $index === 0 ? __('Set the default settings.') : __("Previous breakpoint’s choices will be used when empty."),
                        'type' => 'section',
                        'width' => $this->config['allow_fit'] ? 25 : 33,
                    ],
                ];
            }

            $fields[] = [
                'handle' => $breakpoint === 'default' ? 'src' : "{$breakpoint}:src",
                'field' => [
                    'display' => __('Image'),
                    'instructions' => $index === 0
                        ? __('Choose an image to generate responsive versions from.')
                        : '',
                    'type' => 'assets',
                    'localizable' => $this->config['localizable'] ?? false,
                    'container' => $this->config['container'] ?? optional(AssetContainer::all()->first())->handle(),
                    'folder' => $this->config['folder'] ?? '/',
                    'allow_uploads' => $this->config['allow_uploads'],
                    'restrict' => $this->config['restrict'] ?? false,
                    'max_files' => 1,
                    'mode' => 'list',
                    'width' => $this->config['use_breakpoints']
                        ? ($this->config['allow_ratio'] ? ($this->config['allow_fit'] ? 25 : 33) : 66)
                        : ($this->config['allow_ratio'] ? ($this->config['allow_fit'] ? 50 : 66) : 100),
                    'required' => in_array('required', $this->config['validate'] ?? []) && $index === 0,
                    'validate' => array_filter([
                        new ImageRule(),
                        ((in_array('sometimes', $this->config['validate'] ?? []) && $index === 0) ? 'sometimes' : null)
                    ]),
                ],
            ];

            if ($this->config['allow_ratio']) {
                $fields[] = [
                    'handle' => $breakpoint === 'default'
                        ? 'ratio'
                        : "{$breakpoint}:ratio",
                    'field' => [
                        'display' => __('Ratio'),
                        'instructions' => $index === 0
                            ? __('Accepts a float (`1.55`) or a basic fraction (`16/9`).')
                            : '',
                        'type' => 'text',
                        'width' => $this->config['allow_fit'] ? 25 : 33,
                    ],
                ];

                if ($this->config['allow_fit']) {
                    $fields[] = [
                        'handle' => $breakpoint === 'default'
                            ? 'glide:fit'
                            : "{$breakpoint}:glide:fit",
                        'field' => [
                            'display' => __('Fit'),
                            'instructions' => $index === 0
                                ? __('Sets how the image is fitted to its target ratio.')
                                : '',
                            'type' => 'select',
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
}
