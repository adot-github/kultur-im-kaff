<?php
/*
Admin-Konfiguration für die Tabelle wp_kk_news (News-Meldungen).
*/

$editor->add_table_config( [
    'table' => 'kk_news',
    'skin'  => 'iframe',

    'menu' => [
        'menu_parent' => $root_config_id,
        'page_title'  => '– News',
        'menu_title'  => '– News',
        'icon'        => 'dashicons-megaphone',
        'position'    => 28,
    ],

    'list' => [
        'fields'          => [ 'str_title', 'dtm_date_published', 'ysn_online', 'int_sort_order' ],
        'fields_iframe'   => [ 'str_title' ],
        'orderby_default' => 'int_sort_order',
        'order_default'   => 'asc',
        'drag_sort'       => 'int_sort_order',
        'labels'          => [
            'title'      => 'News bearbeiten',
            'button_add' => 'Neue News hinzufügen',
        ],
    ],

    'form' => [
        'labels' => [
            'title_add'   => 'Neue News hinzufügen',
            'title_edit'  => 'News bearbeiten',
            'button_add'  => 'News speichern',
            'button_edit' => 'News speichern',
        ],
        'fields' => [
            'str_title'          => 'col-md-12',
            'mem_lead'           => 'col-md-12',
            'dtm_date_published' => 'col-md-4',
            'ysn_online'         => 'col-md-4',
            'int_sort_order'     => 'col-md-4',
            '-',
            'str_image_1'   => 'col-md-4',
            'str_image_2'   => 'col-md-4',
            'str_image_3'   => 'col-md-4',
            'str_image_4'   => 'col-md-4',
            'str_image_5'   => 'col-md-4',
            '-',
            'str_video'     => 'col-md-6',
            'str_hyperlink' => 'col-md-6',
        ],
    ],

    'fields' => [
        'id' => [
            'label' => 'ID',
        ],

        'str_title' => [
            'label'      => 'Titel',
            'sortable'   => true,
            'searchable' => true,
            'formatter'  => [
                'list' => 'actions',
            ],
        ],

        'mem_lead' => [
            'label'        => 'Leadtext',
            'searchable'   => true,
            'instructions' => 'Kurzer Anrisstext, der in der Übersicht angezeigt wird.',
            'acf'          => [
                'type' => 'textarea',
                'rows' => 4,
            ],
        ],

        'dtm_date_published' => [
            'label'    => 'Veröffentlichungsdatum',
            'sortable' => true,
            'acf'      => [
                'type' => 'date_picker',
            ],
        ],

        'ysn_online' => [
            'label'        => 'Online',
            'sortable'     => true,
            'instructions' => 'Nur wenn aktiviert, wird die Meldung öffentlich angezeigt.',
            'acf'          => [
                'type'          => 'true_false',
                'ui'            => true,
                'default_value' => true,
            ],
        ],

        'int_sort_order' => [
            'label'        => 'Sortierung',
            'sortable'     => true,
            'instructions' => 'Kleinere Zahl erscheint weiter oben. Kann auch per Drag & Drop in der Listenansicht (links) sortiert werden.',
            'acf'          => [
                'type' => 'number',
            ],
        ],

        'str_image_1' => [
            'label' => 'Bild 1',
            'acf'   => [
                'type'         => 'acdb_file_selector',
                'file_type'    => 'image',
                'subfolder'    => '/news/',
                'image_width'  => 200,
                'image_height' => 200,
            ],
        ],

        'str_image_2' => [
            'label' => 'Bild 2',
            'acf'   => [
                'type'         => 'acdb_file_selector',
                'file_type'    => 'image',
                'subfolder'    => '/news/',
                'image_width'  => 200,
                'image_height' => 200,
            ],
        ],

        'str_image_3' => [
            'label' => 'Bild 3',
            'acf'   => [
                'type'         => 'acdb_file_selector',
                'file_type'    => 'image',
                'subfolder'    => '/news/',
                'image_width'  => 200,
                'image_height' => 200,
            ],
        ],

        'str_image_4' => [
            'label' => 'Bild 4',
            'acf'   => [
                'type'         => 'acdb_file_selector',
                'file_type'    => 'image',
                'subfolder'    => '/news/',
                'image_width'  => 200,
                'image_height' => 200,
            ],
        ],

        'str_image_5' => [
            'label' => 'Bild 5',
            'acf'   => [
                'type'         => 'acdb_file_selector',
                'file_type'    => 'image',
                'subfolder'    => '/news/',
                'image_width'  => 200,
                'image_height' => 200,
            ],
        ],

        'str_video' => [
            'label'        => 'Video',
            'instructions' => 'YouTube-Link oder volle URL zu einer Videodatei unter /news/.',
        ],

        'str_hyperlink' => [
            'label'        => 'Link "Mehr erfahren"',
            'instructions' => 'Optional: externer oder interner Link für einen "Mehr erfahren"-Button.',
            'acf'          => [
                'type' => 'text',
            ],
        ],
    ],
] );
