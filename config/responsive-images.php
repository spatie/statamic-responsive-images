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
    | WebP
    |--------------------------------------------------------------------------
    |
    | Define if you want to generate WebP versions of your images.
    | You can override this on the tag.
    |
    */

    'webp' => true,

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

];
