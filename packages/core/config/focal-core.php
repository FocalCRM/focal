<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Focal Core Configuration
    |--------------------------------------------------------------------------
    |
    | Database-agnostic configuration for core CRM models, table prefixes,
    | and custom properties.
    |
    */

    'tables' => [
        'contacts' => 'focal_contacts',
        'companies' => 'focal_companies',
        'properties' => 'focal_properties',
        'property_groups' => 'focal_property_groups',
        'associations' => 'focal_associations',
        'association_types' => 'focal_association_types',
        'activities' => 'focal_activities',
        'property_history' => 'focal_property_history',
        'lists' => 'focal_lists',
        'list_memberships' => 'focal_list_memberships',
        'lifecycle_stage_transitions' => 'focal_lifecycle_stage_transitions',
        'custom_object_definitions' => 'focal_custom_object_definitions',
        'custom_object_records' => 'focal_custom_object_records',
    ],

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | The Eloquent model used for owners, assignees and authors across all
    | Focal packages. When null, the default auth provider's model is used
    | (auth.providers.users.model).
    |
    */
    'user_model' => env('FOCAL_USER_MODEL'),

    /*
    |--------------------------------------------------------------------------
    | Domain Auto-Association
    |--------------------------------------------------------------------------
    |
    | Automatically associate created contacts with matching companies by corporate
    | email domain. Excludes consumer freemail domains.
    |
    */
    'auto_associate_companies' => false,

    /*
    |--------------------------------------------------------------------------
    | Custom Freemail Domains
    |--------------------------------------------------------------------------
    |
    | Additional consumer domains to treat as freemail (never auto-create or associate).
    |
    */
    'freemail_domains' => [],

    /*
    |--------------------------------------------------------------------------
    | Lifecycle State Machine
    |--------------------------------------------------------------------------
    |
    | Controls whether strict forward progression through the lifecycle funnel
    | is enforced by default.
    |
    */
    'lifecycle' => [
        'strict_transitions' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limits
    |--------------------------------------------------------------------------
    |
    | Requests per minute, per IP address, for the public routes Focal packages
    | register. "public" covers browser-facing submissions (forms, chat, portal
    | replies); "api" covers token-authenticated webhooks and sending APIs.
    |
    */
    'rate_limits' => [
        'public' => (int) env('FOCAL_PUBLIC_RATE_LIMIT', 30),
        'api' => (int) env('FOCAL_API_RATE_LIMIT', 600),
    ],

    /*
    |--------------------------------------------------------------------------
    | Pluggable Enrichment Engine
    |--------------------------------------------------------------------------
    |
    | Configuration for automated domain and corporate data enrichment.
    | Available drivers: "heuristic" (zero-external-API cost default)
    |
    */
    'enrichment' => [
        'driver' => env('FOCAL_ENRICHMENT_DRIVER', 'heuristic'),
        'auto_enrich' => false,
    ],
];
