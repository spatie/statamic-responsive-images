<?php

namespace Spatie\ResponsiveImages\Fieldtypes;

use Statamic\Facades\Blueprint;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\Fields as BlueprintFields;
use Spatie\ResponsiveImages\Fieldtypes\ResponsiveFields as ResponsiveFields;
use Statamic\Support\Arr;

class ResponsiveFieldtype extends Fieldtype
{
    protected static $handle = 'responsive';

    protected $categories = ['media', 'relationship'];
    protected $defaultValue = [];
    protected $defaultable = false;
    protected $icon = 'assets';

    protected function configFieldItems(): array
    {
        return [
            'use_breakpoints' => [
                'display' => __('Use breakpoints'),
                'instructions' => __('Allow breakpoints to be added'),
                'type' => 'toggle',
                'default' => true,
                'width' => 50,
            ],
            'allow_ratio' => [
                'display' => __('Allow ratio'),
                'instructions' => __('Allow ratio to be defined'),
                'type' => 'toggle',
                'default' => true,
                'width' => 50,
            ],
            'allow_fit' => [
                'display' => __('Allow fit'),
                'instructions' => __('Allow fit to be defined'),
                'type' => 'toggle',
                'default' => true,
                'width' => 50,
                'if' => [
                    'allow_ratio' => 'true',
                ],
            ],
            'breakpoints' => [
                'display' => __('Breakpoints'),
                'instructions' => __('Which breakpoints can be chosen.'),
                'type' => 'select',
                'multiple' => true,
                'default' => array_keys(config('statamic.responsive-images.breakpoints')),
                'options' => array_keys(config('statamic.responsive-images.breakpoints')),
                'width' => 100,
                'if' => [
                    'use_breakpoints' => 'true',
                ],
            ],
            'container' => [
                'display' => __('Container'),
                'instructions' => __('statamic::fieldtypes.assets.config.container'),
                'type' => 'asset_container',
                'max_items' => 1,
                'mode' => 'select',
                'width' => 50,
            ],
            'folder' => [
                'display' => __('Folder'),
                'instructions' => __('statamic::fieldtypes.assets.config.folder'),
                'type' => 'asset_folder',
                'max_items' => 1,
                'width' => 50,
            ],
            'restrict' => [
                'display' => __('Restrict'),
                'instructions' => __('statamic::fieldtypes.assets.config.restrict'),
                'type' => 'toggle',
                'width' => 50,
            ],
            'allow_uploads' => [
                'display' => __('Allow Uploads'),
                'instructions' => __('statamic::fieldtypes.assets.config.allow_uploads'),
                'type' => 'toggle',
                'default' => true,
                'width' => 50,
            ],
        ];
    }

    public function preload()
    {
        return [
            'fields' => $this->fieldConfig(),
            'meta' => $this->fields()->addValues($this->field()->value() ?? [])->meta(),
        ];
    }

    protected function fields()
    {
        return new BlueprintFields($this->fieldConfig());
    }

    protected function fieldConfig()
    {
        return ResponsiveFields::new($this->config())->getConfig();
    }

    public function preProcess($data)
    {
        return $this->fields()->addValues($data ?? [])->preProcess()->values()->all();
    }

    public function process($data)
    {
        return Arr::removeNullValues(
            $this->fields()->addValues($data)->process()->values()->all()
        );
    }

    public function augment($data)
    {
        if (! is_iterable($data)) {
            return $data;
        }

        return Blueprint::make()
            ->setContents(['fields' => $this->fieldConfig()])
            ->fields()
            ->addValues($data)
            ->augment()
            ->values()
            ->only(array_keys($data))
            ->all();
    }

}
