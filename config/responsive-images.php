<?php

return [

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
    | Set a global max width for generated images
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
