<?php

namespace Spatie\ResponsiveImages\Fieldtypes;

use Illuminate\Support\Arr as IlluminateArr;
use Spatie\ResponsiveImages\AssetNotFoundException;
use Spatie\ResponsiveImages\Breakpoint;
use Spatie\ResponsiveImages\Exceptions\InvalidAssetException;
use Spatie\ResponsiveImages\Fieldtypes\ResponsiveFields as ResponsiveFields;
use Spatie\ResponsiveImages\GraphQL\ResponsiveFieldType as GraphQLResponsiveFieldtype;
use Spatie\ResponsiveImages\Responsive;
use Statamic\Facades\GraphQL;
use Statamic\Fields\Field;
use Statamic\Fields\Fields as BlueprintFields;
use Statamic\Fields\Fieldtype;
use Statamic\Support\Arr;
use Statamic\Tags\Context;
use Statamic\Tags\Parameters;
use Throwable;

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

    public function extraRules(): array
    {
        $rules = collect($this->fieldConfig())->mapWithKeys(function ($field) {
            if ($field['field']['required'] ?? false) {
                $rules = ['required'];
            } else {
                $rules = ['nullable'];
            }

            $prefixedHandle = $this->field()->handle() . '.' . $field['handle'];

            return [
                $prefixedHandle => array_merge($rules, $field['field']['validate'] ?? []),
            ];
        });

        return $rules->toArray();
    }

    public function preProcess($data)
    {
        return $this->getFieldsWithValues($data)->preProcess()->values()->all();
    }

    public function preProcessIndex($data)
    {
        $data = $this->augment($data);

        if (! isset($data['src'])) {
            return ['total' => 0, 'assets' => []];
        }

        try {
            $responsive = new Responsive($data['src'], Parameters::make($data, Context::make()));
        } catch (AssetNotFoundException | InvalidAssetException) {
            return ['total' => 0, 'assets' => []];
        }

        $breakpoints = $responsive->breakPoints();
        $total = $breakpoints->count();

        $assets = $breakpoints
            ->take(6)
            ->map(function (Breakpoint $breakpoint) {
                $arr = [
                    'id' => $breakpoint->asset->id(),
                    'is_image' => $isImage = $breakpoint->asset->isImage(),
                    'is_svg' => $breakpoint->asset->isSvg(),
                    'extension' => $breakpoint->asset->extension(),
                    'url' => $breakpoint->asset->url(),
                    'breakpoint' => $breakpoint->label,
                ];

                if ($isImage) {
                    $arr['thumbnail'] = cp_route('assets.thumbnails.show', [
                        'encoded_asset' => base64_encode($breakpoint->asset->id()),
                        'size' => 'small',
                    ]);
                }

                return $arr;
            });

        return compact('total', 'assets');
    }

    public function process($data)
    {
        if (! is_iterable($data)) {
            return [];
        }

        return Arr::removeNullValues(
            $this->getFieldsWithValues($data)->process()->values()->all()
        );
    }

    public function augment($data)
    {
        if (! is_iterable($data)) {
            return $data;
        }

        $fields = $this->getFieldsWithValues($data);

        try {
            $processedFields = $fields->process();
        } catch (Throwable) {
            $processedFields = $fields;
        }

        return $processedFields
            ->augment()
            ->values()
            ->only(array_keys($data))
            ->all();
    }

    public function toGqlType()
    {
        return GraphQL::type(GraphQLResponsiveFieldtype::NAME);
    }

    protected function getFieldsWithValues(array $values): BlueprintFields
    {
        $fields = $this->fields()->all()->map(function (Field $field) use ($values) {
            return IlluminateArr::has($values, $field->handle())
                ? $field->newInstance()->setValue(IlluminateArr::get($values, $field->handle()))
                : $field->newInstance();
        });

        return $this->fields()->setFields($fields);
    }
}
