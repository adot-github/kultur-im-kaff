<?php
/*
Admin-Konfiguration für die Tabelle wp_kk_event_locations (Spielorte).
Wird von wp_kk_events.fky_location referenziert.
*/

$editor->add_table_config( [
    'table' => 'kk_event_locations',

    'menu' => [
        'menu_parent' => $root_config_id,
        'page_title'  => '– Spielorte',
        'menu_title'  => '– Spielorte',
        'icon'        => 'dashicons-location-alt',
        'position'    => 26,
    ],

    'list' => [
        'fields'          => [ 'str_location', 'str_city' ],
        'orderby_default' => 'str_location',
        'order_default'   => 'asc',
        'labels'          => [
            'title'      => 'Spielorte bearbeiten',
            'button_add' => 'Neuen Spielort hinzufügen',
        ],
    ],

    'form' => [
        'labels' => [
            'title_add'   => 'Neuen Spielort hinzufügen',
            'title_edit'  => 'Spielort bearbeiten',
            'button_add'  => 'Spielort speichern',
            'button_edit' => 'Spielort speichern',
        ],
        'fields' => [
            'str_location' => 'col-md-8',
            'str_google'   => 'col-md-4',
            'str_address'  => 'col-md-4',
            'str_zip'      => 'col-md-2',
            'str_city'     => 'col-md-6',
            'str_travel'   => 'col-md-12',
            'mem_description' => 'col-md-12',
            '',
            'str_image_1' => 'col-md-4',
            'str_image_2' => 'col-md-4',
            'str_image_3' => 'col-md-4',
        ],
    ],

    'fields' => [
        'id' => [
            'label' => 'ID',
        ],

        'str_location' => [
            'label'      => 'Name',
            'sortable'   => true,
            'searchable' => true,
            'formatter'  => [
                'list' => 'actions',
            ],
        ],

        'mem_description' => [
            'label' => 'Beschreibung',
            'acf'   => [
                'type' => 'acdb_ckeditor',
                'mode' => 'standalone',
            ],
        ],

        'str_address' => [
            'label' => 'Adresse (Strasse/Nr.)',
        ],

        'str_zip' => [
            'label' => 'PLZ',
        ],

        'str_city' => [
            'label'      => 'Ort',
            'sortable'   => true,
            'searchable' => true,
        ],

        'str_travel' => [
            'label'        => 'Anreise',
            'instructions' => 'Kurzer Hinweistext, z. B. Bus-/Parkplatz-Info.',
        ],

        'str_google' => [
            'label'        => 'Google-Maps-Link',
            'instructions' => 'Bitte die vollständige URL aus der Browser-Adresszeile einfügen (mit Koordinaten, z. B. "@47.41...,8.04..."), nicht den kurzen "Teilen"-Link – nur so kann der Kartenausschnitt auf der Spielorte-Seite angezeigt werden.',
            'acf'          => [
                'type' => 'url',
            ],
        ],

        'str_image_1' => [
            'label' => 'Bild 1',
            'acf'   => [
                'type'         => 'acdb_file_selector',
                'file_type'    => 'image',
                'subfolder'    => '/spielorte/',
                'image_width'  => 200,
                'image_height' => 200,
            ],
        ],

        'str_image_2' => [
            'label' => 'Bild 2',
            'acf'   => [
                'type'         => 'acdb_file_selector',
                'file_type'    => 'image',
                'subfolder'    => '/spielorte/',
                'image_width'  => 200,
                'image_height' => 200,
            ],
        ],

        'str_image_3' => [
            'label' => 'Bild 3',
            'acf'   => [
                'type'         => 'acdb_file_selector',
                'file_type'    => 'image',
                'subfolder'    => '/spielorte/',
                'image_width'  => 200,
                'image_height' => 200,
            ],
        ],
    ],
] );
