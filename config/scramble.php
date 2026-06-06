<?php

return [
    /*
     * API path, where the documentation will be available.
     */
    'path' => 'docs/api',

    /*
     * Which routes to document. String or array form.
     * This tells Scramble to scan all routes starting with 'api/v1'
     */
    'api_path' => 'api/v1',

    /*
     * Your API domain. By default, app domain is used.
     */
    'api_domain' => null,

    /*
     * The path where your OpenAPI specification will be exported.
     */
    'export_path' => 'api.json',

    /*
     * API information.
     */
    'info' => [
        'title' => 'Asira Project Management API',
        'description' => 'Complete REST API for project management system with workspaces, projects, tasks, and more.',
        'version' => '1.0.0',
    ],

    /*
     * UI renderer: 'elements' or 'scalar'
     */
    'renderer' => 'scalar',

    /*
     * Scalar API reference config options
     */
    'renderers' => [
        'scalar' => [
            'view' => 'scramble::scalar',
            'cdn' => 'https://cdn.jsdelivr.net/npm/@scalar/api-reference',
            'theme' => 'default',
            'darkMode' => false,
            'showDeveloperTools' => 'never',
            'credentials' => 'include',
            'hideModels' => true,
        ],
    ],

    /*
     * The list of servers of the API.
     */
    'servers' => null,

    /*
     * Middleware for the documentation routes.
     */
    'middleware' => [
        'web',
    ],

    /*
     * OpenAPI extensions.
     */
    'extensions' => [],
];
