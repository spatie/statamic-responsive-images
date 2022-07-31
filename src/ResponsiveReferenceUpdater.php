<?php

namespace Spatie\ResponsiveImages;

use Statamic\Data\DataReferenceUpdater;
use Statamic\Facades\AssetContainer;
use Statamic\Support\Arr;

class ResponsiveReferenceUpdater extends DataReferenceUpdater
{
    /**
     * @var string
     */
    protected $container;

    /**
     * Filter by container.
     *
     * @param  string  $container
     * @return $this
     */
    public function filterByContainer(string $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Recursively update fields.
     *
     * @param  \Illuminate\Support\Collection  $fields
     * @param  null|string  $dottedPrefix
     */
    protected function recursivelyUpdateFields($fields, $dottedPrefix = null)
    {
        $this->updateResponsiveFieldValues($fields, $dottedPrefix);
    }

    /**
     * Update assets field values.
     *
     * @param  \Illuminate\Support\Collection  $fields
     * @param  null|string  $dottedPrefix
     * @return $this
     */
    protected function updateResponsiveFieldValues($fields, $dottedPrefix)
    {
        $fields
            ->filter(function ($field) {
                return $field->type() === 'responsive'
                    && $this->getConfiguredAssetsFieldContainer($field) === $this->container;
            })
            ->each(function ($field) use ($dottedPrefix) {
                $this->updateResponsiveValue($field, $dottedPrefix);
            });

        return $this;
    }

    /**
     * Update responsive value on item. This method is
     * a clone of AssetReferenceUpdater@updateArrayValue()
     * with a modification to fix dot notication zeroes.
     * 
     *
     * @param  \Statamic\Fields\Field  $field
     * @param  null|string  $dottedPrefix
     */
    protected function updateResponsiveValue($field, $dottedPrefix, $multiple = false)
    {
        $data = $this->item->data()->all();

        $dottedKey = $dottedPrefix.$field->handle();

        $fieldData = collect(Arr::dot(Arr::get($data, $dottedKey, [])));

        // Array dot notation zeroes need to be removed
        $fieldData = $fieldData->mapWithKeys(function ($value, $key) {
            return [str_replace('.0', '', $key) => $value];
        });

        if (! $fieldData->contains($this->originalValue())) {
            return;
        }

        $fieldData->transform(function ($value) {
            return $value === $this->originalValue() ? $this->newValue() : $value;
        });


        Arr::set($data, $dottedKey, $fieldData->all());

        $this->item->data($data);

        $this->updated = true;
    }

    /**
     * Get configured assets field container, or implied asset container if only one exists.
     *
     * @param  \Statamic\Fields\Field  $field
     * @return string
     */
    protected function getConfiguredAssetsFieldContainer($field)
    {
        if ($container = $field->get('container')) {
            return $container;
        }

        $containers = AssetContainer::all();

        return $containers->count() === 1
            ? $containers->first()->handle()
            : null;
    }
}
