<?php
/*
Admin-Konfiguration für die Tabelle wp_kk_events (Programm / Archiv).
Verknüpfungen:
 - fky_location  -> wp_kk_event_locations   (einfache Fremdschlüssel-Relation, "fky")
 - tags          -> wp_kk_event_tags        (Mehrfach-Relation über die Kreuztabelle wp_kk_event_to_tags, "dbx")
*/

$root_config_id = $editor->add_table_config( [
    'table' => 'kk_events',

    'menu' => [
        'rename_root_label' => 'Kultur im Kaff',
        'page_title'        => '– Anlässe',
        'menu_title'        => '– Anlässe',
        'icon'              => get_stylesheet_directory_uri() . '/db-custom/kultur-im-kaff/admin/kik.png',
        'position'          => 1,
    ],

    'list' => [
        'fields'          => [ 'str_title', 'dtm_date_from', 'fky_location' ],
        'fields_iframe'   => [ 'str_title', 'dtm_date_from' ],
        'orderby_default' => 'dtm_date_from',
        'order_default'   => 'desc',
        'labels'          => [
            'title'      => 'Anlässe bearbeiten',
            'button_add' => 'Neuen Anlass hinzufügen',
        ],
    ],

    'form' => [
        'labels' => [
            'title_add'   => 'Neuen Anlass hinzufügen',
            'title_edit'  => 'Anlass bearbeiten',
            'button_add'  => 'Anlass speichern',
            'button_edit' => 'Anlass speichern',
        ],
        'fields' => [
            [ 'type' => 'tab', 'label' => 'Hauptinformationen' ],
            'str_title'         => 'col-md-8',
            'fky_location'      => 'col-md-4',
            'mem_description'   => 'col-md-8',
            '-',
            'str_artist'        => 'col-md-6',
            'str_artist_detail' => 'col-md-6',
            'dtm_date_from'     => 'col-md-2',
            'dtm_time_from'     => 'col-md-2',
            'dtm_date_to'       => 'col-md-2',
            'dtm_time_to'       => 'col-md-2',
            'str_date_extra'    => 'col-md-4',
            'tags'              => 'col-md-12',

            [ 'type' => 'tab', 'label' => 'Preise' ],
            'num_price_adults'   => 'col-md-3',
            'num_price_members'  => 'col-md-3',
            'num_price_children' => 'col-md-3',
            'str_price_remark'   => 'col-md-3',

            [ 'type' => 'tab', 'label' => 'Links & Medien' ],
            'str_image'           => 'col-md-6',
            'str_video'           => 'col-md-6',
            'str_hyperlink'       => 'col-md-6',
            'str_hyperlink_sales' => 'col-md-6',
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

        'str_artist' => [
            'label'      => 'Künstler / Act',
            'searchable' => true,
        ],

        'str_artist_detail' => [
            'label'       => 'Künstler-Detail',
            'instructions' => 'Kurze Zusatzzeile, z. B. Rollenaufteilung oder Besetzung.',
        ],

        'fky_location' => [
            'label'     => 'Spielort',
            'sortable'  => true,
            'formatter' => [
                'list' => 'fky',
            ],
            'fky' => [
                'db' => [
                    'table' => 'kk_event_locations',
                    'id'    => 'id',
                    'label' => 'str_location',
                ],
            ],
            'acf' => [
                'type' => 'acdb_relationship',
            ],
        ],

        'dtm_date_from' => [
            'label'    => 'Datum von',
            'sortable' => true,
            'acf'      => [
                'type' => 'date_picker',
            ],
        ],

        'dtm_date_to' => [
            'label'        => 'Datum bis',
            'instructions' => 'Nur bei mehrtägigen Anlässen (z. B. Ausstellungen) ausfüllen.',
            'acf'          => [
                'type' => 'date_picker',
            ],
        ],

        'dtm_time_from' => [
            'label' => 'Zeit von',
            'acf'   => [
                'type' => 'time_picker',
            ],
        ],

        'dtm_time_to' => [
            'label' => 'Zeit bis',
            'acf'   => [
                'type' => 'time_picker',
            ],
        ],

        'str_date_extra' => [
            'label'        => 'Zusatzinfo zu Datum/Ort',
            'instructions' => 'Erscheint klein unter Datum/Zeit/Ort, z. B. "Kulturbar ab 19.00 Uhr" oder "Kein Vorverkauf".',
        ],

        'tags' => [
            'label' => 'Tags',
            'acf'   => [
                'type' => 'acdb_relationship',
            ],
            // Mehrfach-Relation über die Kreuztabelle wp_kk_event_to_tags.
            'dbx' => [
                'allow_new' => true,
                'db'        => [
                    'tbx_table'     => 'kk_event_to_tags',
                    'tbx_id_main'   => 'fky_event_id',
                    'tbx_id_linked' => 'fky_tag_id',
                    'linked_table'  => 'kk_event_tags',
                    'linked_label'  => 'str_tag',
                ],
            ],
        ],

        'mem_description' => [
            'label'      => 'Beschreibung',
            'searchable' => true,
            'acf'        => [
                'type' => 'acdb_ckeditor',
                'mode' => 'standalone',
            ],
        ],

        'num_price_adults' => [
            'label' => 'Preis Erwachsene (CHF)',
            'acf'   => [
                'type' => 'number',
                'step' => '0.05',
            ],
        ],

        'num_price_members' => [
            'label' => 'Preis Mitglieder (CHF)',
            'acf'   => [
                'type' => 'number',
                'step' => '0.05',
            ],
        ],

        'num_price_children' => [
            'label' => 'Preis Kinder (CHF)',
            'acf'   => [
                'type' => 'number',
                'step' => '0.05',
            ],
        ],

        'str_price_remark' => [
            'label'        => 'Preis-Bemerkung',
            'instructions' => 'Wird zusätzlich zu den Preisen angezeigt, z. B. "Kollekte". Sind alle drei Preise leer/0, ersetzt diese Bemerkung die Preiszeile.',
        ],

        'str_image' => [
            'label' => 'Bild',
            'acf'   => [
                'type'         => 'acdb_file_selector',
                'file_type'    => 'image',
                'subfolder'    => '/events/',
                'image_width'  => 200,
                'image_height' => 200,
            ],
        ],

        'str_video' => [
            'label'        => 'Video',
            'instructions' => 'YouTube-Link oder volle URL zu einer Videodatei unter /events/.',
        ],

        'str_hyperlink' => [
            'label' => 'Link "Mehr Infos"',
            'acf'   => [
                'type' => 'url',
            ],
        ],

        'str_hyperlink_sales' => [
            'label'        => 'Vorverkauf-Link',
            'instructions' => 'Wird im Archiv nie angezeigt, auch wenn hier ein Link gesetzt ist.',
            'acf'          => [
                'type' => 'url',
            ],
        ],
    ],
] );
