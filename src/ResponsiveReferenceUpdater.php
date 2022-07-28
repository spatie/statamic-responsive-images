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
                $this->updateArrayValue($field, $dottedPrefix);
            });

        return $this;
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
