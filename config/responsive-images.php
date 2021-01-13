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
];
