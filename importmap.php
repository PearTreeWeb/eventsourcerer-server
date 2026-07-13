<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    '@hotwired/turbo' => [
        'version' => '8.0.23',
    ],
    '@symfony/stimulus-bridge' => [
        'version' => '4.0.1',
    ],
    'tippy.js' => [
        'version' => '6.3.7',
    ],
    '@popperjs/core' => [
        'version' => '2.11.8',
    ],
    'tippy.js/dist/tippy.css' => [
        'version' => '6.3.7',
        'type' => 'css',
    ],
    'chart.js' => [
        'version' => '4.5.1',
    ],
    '@kurkle/color' => [
        'version' => '0.4.0',
    ],
    'simple-datatables' => [
        'version' => '10.2.0',
    ],
    'simple-datatables/dist/style.min.css' => [
        'version' => '10.2.0',
        'type' => 'css',
    ],
    '@symfony/ux-live-component' => [
        'path' => './vendor/symfony/ux-live-component/assets/dist/live_controller.js',
    ],
    'toastify-js' => [
        'version' => '1.12.0',
    ],
    'toastify-js/src/toastify.css' => [
        'version' => '1.12.0',
        'type' => 'css',
    ],
];
