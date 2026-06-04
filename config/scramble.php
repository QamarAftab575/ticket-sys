<?php

return [
    /*
     * API path, where the documentation will be available.
     */
    'path' => 'api/docs',

    /*
     * API domain. By default, app domain is used. This is for the OpenAPI servers.
     */
    'api_domain' => env('APP_URL'),

    /*
     * Customize the UI settings.
     */
    'ui' => 'scalar', // Use Scalar UI instead of default

    /*
     * The list of route prefixes that should be documented.
     */
    'routes' => [
        'prefix' => 'api/v1',
        'middleware' => ['api'],
    ],

    /*
     * API information.
     */
    'info' => [
        'title' => 'Asana-like Project Management API',
        'description' => 'Complete REST API for project management system with workspaces, projects, tasks, and more.',
        'version' => '1.0.0',
        'contact' => [
            'name' => 'API Support',
            'email' => 'support@yourapp.com',
        ],
    ],

    /*
     * Servers configuration.
     */
    'servers' => [
        [
            'url' => env('APP_URL') . '/api/v1',
            'description' => 'Main API Server',
        ],
    ],

    /*
     * Security schemes configuration.
     */
    'security' => [
        'BearerAuth' => [
            'type' => 'http',
            'scheme' => 'bearer',
            'bearerFormat' => 'API Token',
            'description' => 'Enter your API token (obtained from /auth/login or /auth/register)',
        ],
        'ApiKeyAuth' => [
            'type' => 'apiKey',
            'in' => 'header',
            'name' => 'X-API-Token',
            'description' => 'Alternative: Use X-API-Token header',
        ],
    ],

    /*
     * Tags for grouping endpoints.
     */
    'tags' => [
        [
            'name' => 'Authentication',
            'description' => 'User authentication and token management',
        ],
        [
            'name' => 'Users',
            'description' => 'User profile and search operations',
        ],
        [
            'name' => 'Workspaces',
            'description' => 'Workspace (Organization) management',
        ],
        [
            'name' => 'Projects',
            'description' => 'Project creation and management',
        ],
        [
            'name' => 'Tasks',
            'description' => 'Task operations, dependencies, and subtasks',
        ],
        [
            'name' => 'Custom Fields',
            'description' => 'Custom field management for projects',
        ],
        [
            'name' => 'Comments',
            'description' => 'Task comments',
        ],
        [
            'name' => 'Attachments',
            'description' => 'File uploads and attachments',
        ],
        [
            'name' => 'SSO',
            'description' => 'Single Sign-On with JWT tokens',
        ],
    ],

    /*
     * Customize the OpenAPI specification.
     */
    'extensions' => [
        'x-logo' => [
            'url' => env('APP_URL') . '/logo.png',
            'altText' => 'API Logo',
        ],
    ],

    /*
     * Exclude specific routes from documentation.
     */
    'ignore' => [
        // Add routes to ignore here
    ],
];
