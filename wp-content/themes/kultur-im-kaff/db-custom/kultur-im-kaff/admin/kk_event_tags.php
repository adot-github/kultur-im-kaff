<?php
/*
Admin-Konfiguration für die Tabelle wp_kk_event_tags.
Verknüpfung mit Anlässen läuft über die Kreuztabelle wp_kk_event_to_tags
(siehe Feld "tags" in kk_events.php) – hier nur Verwaltung der Tag-Namen selbst
(z. B. Tippfehler korrigieren, ungenutzte Tags aufräumen).
*/

$editor->add_table_config( [
    'table' => 'kk_event_tags',
    'skin'  => 'iframe',

    'menu' => [
        'menu_parent' => $root_config_id,
        'page_title'  => '– Tags',
        'menu_title'  => '– Tags',
        'icon'        => 'dashicons-tag',
        'position'    => 27,
    ],

    'list' => [
        'fields'          => [ 'str_tag', 'id' ],
        'fields_iframe'   => [ 'str_tag', 'id' ],
        'orderby_default' => 'str_tag',
        'order_default'   => 'asc',
        'labels'          => [
            'title'      => 'Tags bearbeiten',
            'button_add' => 'Neuen Tag hinzufügen',
        ],
    ],

    'form' => [
        'labels' => [
            'title_add'   => 'Neuen Tag hinzufügen',
            'title_edit'  => 'Tag bearbeiten',
            'button_add'  => 'Tag speichern',
            'button_edit' => 'Tag speichern',
        ],
        'fields' => [
            'str_tag' => 'col-md-12',
        ],
    ],

    'fields' => [
        'id' => [
            'label' => 'ID',
        ],

        'str_tag' => [
            'label'      => 'Tag',
            'sortable'   => true,
            'searchable' => true,
            'formatter'  => [
                'list' => 'actions',
            ],
        ],
    ],
] );
