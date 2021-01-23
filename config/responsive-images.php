<?php

return [

    /**
     * If the generate images job is being queued, specify the name of the target queue
     * Falls back to the 'default' queue
     */
    'queue' => 'default',

    /*
     * Set a global max width for generated images
     */
    'max_width' => null,

    /*
     * Define the breakpoints you want to have available to art direct your images
     */
    'breakpoints' => [
        'sm' => 640,
        'md' => 768,
        'lg' => 1024,
        'xl' => 1280,
        '2xl' => 1536,
    ],

    /*
     * The unit of the breakpoints
     */
    'breakpoint_unit' => 'px',

];
