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
        $this
            ->updateResponsiveFieldValues($fields, $dottedPrefix)
            ->updateNestedFieldValues($fields, $dottedPrefix);
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
    protected function updateResponsiveValue($field, $dottedPrefix)
    {
        $data = $this->item->data()->all();

        $dottedKey = $dottedPrefix.$field->handle();

        $fieldData = collect(
            Arr::get($data, $dottedKey, [])
        );

        $updated = 0;

        $fieldData->transform(function ($value, $key) use (&$updated) {
            if (!str_ends_with($key, 'src')) {
                return $value;
            }

            // In content files, the src value can be either string or array.
            // We first handle the string value, and then handle the array value.
            if (is_string($value) && $value === $this->originalValue()) {
                $updated++;
                return $this->newValue();
            }

            // Handle array value.
            if (is_array($value) && in_array($this->originalValue(), $value)) {
                return array_map(function ($item) use (&$updated) {
                    if ($item === $this->originalValue()) {
                        $updated++;
                        return $this->newValue();
                    }

                    return $item;
                }, $value);
            }

            return $value;
        });

        if ($updated === 0) {
            return;
        }

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
