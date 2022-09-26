<?php

namespace Spatie\ResponsiveImages;

use Statamic\Assets\AssetReferenceUpdater;
use Statamic\Support\Arr;

class ResponsiveReferenceUpdater extends AssetReferenceUpdater
{
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
     * Update responsive value on item
     * 
     * @see AssetReferenceUpdater::updateArrayValue()
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

        $referencesUpdated = 0;

        $fieldData->transform(function ($value, $key) use (&$referencesUpdated) {
            if (!str_ends_with($key, 'src')) {
                return $value;
            }

            // In content files, the src value can be either string or array.
            // First handle the string value, and then handle the array value.
            // Handle asset deletion, return null now for filtering later.
            if ($value === $this->originalValue() && $this->isRemovingValue()) {
                $referencesUpdated++;
                return null;
            }

            if (is_string($value) && $value === $this->originalValue()) {
                $referencesUpdated++;
                return $this->newValue();
            }

            // Handle array value.
            if (is_array($value) && in_array($this->originalValue(), $value)) {
                $transformedFieldDataArray = array_map(function ($item) use (&$referencesUpdated) {
                    // Handle asset deletion, return null now for filtering.
                    if ($item === $this->originalValue() && $this->isRemovingValue()) {
                        $referencesUpdated++;
                        return null;
                    }

                    if ($item === $this->originalValue()) {
                        $referencesUpdated++;
                        return $this->newValue();
                    }

                    return $item;
                }, $value);

                return array_filter($transformedFieldDataArray, fn($item) => $item !== null);
            }

            return $value;
        });

        $fieldData = $fieldData->filter(fn($item) => $item !== null);

        if ($referencesUpdated === 0) {
            return;
        }

        Arr::set($data, $dottedKey, $fieldData->all());

        $this->item->data($data);

        $this->updated = true;
    }
}
