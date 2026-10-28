<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enable/Disable Modules
    |--------------------------------------------------------------------------
    | Activa o desactiva la visualización en el menú de los módulos.
    |--------------------------------------------------------------------------
    */

    'modules' => [
        // CRM
        'dashboard'     => env('PYMECRM_SHOW_DASHBOARD', true),
        'agenda'        => env('PYMECRM_SHOW_AGENDA', true),
        'contacts'      => env('PYMECRM_SHOW_CONTACTS', true),
        'opportunities' => env('PYMECRM_SHOW_OPPORTUNITIES', true),
        'reports'       => env('PYMECRM_SHOW_REPORTS', true),

        // Administración
        'users'         => env('PYMECRM_SHOW_USERS', true),
        'roles'         => env('PYMECRM_SHOW_ROLES', true),
        'permissions'   => env('PYMECRM_SHOW_PERMISSIONS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | UI · Badge variants
    |--------------------------------------------------------------------------
    | Clases Tailwind para badges del sidebar y otras vistas.
    | Centraliza colores y estilos visuales.
    |--------------------------------------------------------------------------
    */

    'badge_variants' => [
        'red' => 'bg-red-50 text-red-700 ring-1 ring-red-200',
        'green' => 'bg-green-50 text-green-700 ring-1 ring-green-200',
        'gray' => 'bg-gray-50 text-gray-700 ring-1 ring-gray-200',
        'default' => 'bg-gray-50 text-gray-700 ring-1 ring-gray-200',
    ],

    /*
    |--------------------------------------------------------------------------
    | Usuarios
    |--------------------------------------------------------------------------
    */

    'default_role' => env('PYMECRM_DEFAULT_ROLE', 'Ejecutivo'),

];
