<?php

namespace Spatie\ResponsiveImages;

use Statamic\Assets\AssetReferenceUpdater;
use Statamic\Support\Arr;

class ResponsiveReferenceUpdater extends AssetReferenceUpdater
{
    protected function recursivelyUpdateFields($fields, $dottedPrefix = null): void
    {
        $this
            ->updateResponsiveFieldValues($fields, $dottedPrefix)
            ->updateNestedFieldValues($fields, $dottedPrefix);
    }

    protected function updateResponsiveFieldValues($fields, $dottedPrefix): static
    {
        $fields
            ->filter(fn ($field) => $field->type() === 'responsive'
                && $this->getConfiguredAssetsFieldContainer($field) === $this->container)
            ->each(fn ($field) => $this->updateResponsiveValue($field, $dottedPrefix));

        return $this;
    }

    protected function updateResponsiveValue($field, $dottedPrefix): void
    {
        $data = $this->item->data()->all();

        $dottedKey = $dottedPrefix.$field->handle();

        $fieldData = collect(
            Arr::get($data, $dottedKey, [])
        );

        $referencesUpdated = 0;

        $fieldData->transform(function ($value, $key) use (&$referencesUpdated) {
            if (! str_ends_with($key, 'src')) {
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

                return array_filter($transformedFieldDataArray, fn ($item) => $item !== null);
            }

            return $value;
        });

        $fieldData = $fieldData->filter(fn ($item) => $item !== null);

        if ($referencesUpdated === 0) {
            return;
        }

        Arr::set($data, $dottedKey, $fieldData->all());

        $this->item->data($data);

        $this->updated = true;
    }
}
