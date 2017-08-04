<?php

/**
 * @file
 * Hooks specific to the Dynamic Banner Module.
 */

/**
 * Provides libraries info to Dynamic Banner module (block)
 *
 * @return array
 */
function hook_dynamic_banner_add_libraries() {
    return [
        'bxslider' => [
            'name' => 'bxslider (Default)',
            'library' => 'dynamic_banner/dynamic_banner.bxslider',
            'classes' => ['bxslider']
        ]
    ];
}