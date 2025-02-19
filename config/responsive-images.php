<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Generate On Upload
    |--------------------------------------------------------------------------
    |
    | Whether image conversions should be generated on upload
    |
    */

    'generate_on_upload' => true,

    /*
    |--------------------------------------------------------------------------
    | Generate Image Job
    |--------------------------------------------------------------------------
    |
    | The job used to generate images, by default this uses
    | \Spatie\ResponsiveImages\Jobs\GlideImageJob
    |
    */

    'image_job' => \Spatie\ResponsiveImages\Jobs\GenerateGlideImageJob::class,

    /*
    |--------------------------------------------------------------------------
    | Force absolute URL
    |--------------------------------------------------------------------------
    |
    | Useful if you are using GraphQL API and consuming it from another
    | app on a different domain. Normally Glide will return relative URLs, but
    | you can force it to return absolute URLs.
    |
    */
    'force_absolute_urls' => false,

    /*
    |--------------------------------------------------------------------------
    | Queue
    |--------------------------------------------------------------------------
    |
    | If the generate images job is being queued, specify the name of the
    | target queue. This falls back to the 'default' queue
    |
    */

    'queue' => 'default',

    /*
    |--------------------------------------------------------------------------
    | Max Width
    |--------------------------------------------------------------------------
    |
    | Define a global max-width for generated images.
    | You can override this on the tag.
    |
    */

    'max_width' => null,

    /*
    |--------------------------------------------------------------------------
    | Dimension Calculator Threshold
    |--------------------------------------------------------------------------
    |
    | Define the file size threshold at which the default
    | dimension calculator decides to generate a new
    | variant. By default, this is 30% smaller.
    | Must be a value: 0 < x < 1
    |
    */

    'dimension_calculator_threshold' => 0.7,

    /*
    |--------------------------------------------------------------------------
    | Placeholder
    |--------------------------------------------------------------------------
    |
    | Define if you want to generate low-quality placeholders of your images.
    | You can override this on the tag.
    |
    */

    'placeholder' => true,

    /*
    |--------------------------------------------------------------------------
    | Image formats
    |--------------------------------------------------------------------------
    |
    | Define if you want to generate WebP or AVIF versions of your images.
    | You can override this on the tag.
    |
    */

    'webp' => true,
    'avif' => false,

    /*
    |--------------------------------------------------------------------------
    | Quality
    |--------------------------------------------------------------------------
    |
    | Define quality value for each image encoding format.
    | Use null for default Glide quality.
    |
    */

    'quality' => [
        'jpg' => 90,
        'webp' => 90,
        'avif' => 45
    ],

    /*
    |--------------------------------------------------------------------------
    | Breakpoints
    |--------------------------------------------------------------------------
    |
    | Define the breakpoints to art direct your images
    |
    */

    'breakpoints' => [
        'sm' => 640,
        'md' => 768,
        'lg' => 1024,
        'xl' => 1280,
        '2xl' => 1536,
    ],

    /*
    |--------------------------------------------------------------------------
    | Breakpoint Unit
    |--------------------------------------------------------------------------
    |
    | The unit that will be used for the breakpoint media queries
    |
    */

    'breakpoint_unit' => 'px',

    /*
    |--------------------------------------------------------------------------
    | Excluded Containers
    |--------------------------------------------------------------------------
    |
    | Define the containers which should be excluded from generation responsive variants
    |
    */

    'excluded_containers' => [],

];
