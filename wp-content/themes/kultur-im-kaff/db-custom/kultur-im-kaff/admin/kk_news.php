<?php
/*
 * Tabelle wp_kk_news (News-Meldungen) – Gerüst vom Admin Builder, Einstellungen von Hand.
 * Wird aus admin/index.php eingebunden ($editor kommt von dort).
 * Leere Parameter sind wirkungslos und können gelöscht werden; auskommentierte Parameter
 * wirken schon durch ihr Vorhandensein und sind deshalb nur als Vorlage aufgeführt.
 */
if (!isset($editor)) {
    return;
}

// Feldbeschriftungen aus wp_acdb_database_fields (Fallback: Text nach ??)
$labels = acdb_field_labels('kk_news');

$editor->add_table_config([
    'table' => 'kk_news',
    // 'id'   => 'acdb_kk_news', // Standard: acdb_<tabelle>
    'skin' => 'iframe',           // Ansicht erzwingen: 'iframe' = Liste links, Formular rechts
    // 'sql'  => [
    //     'table'         => '',  // andere Tabelle/View für die Liste
    //     'select_fields' => [],  // zusätzliche SELECT-Ausdrücke
    // ],
    'menu' => [
        'menu_parent' => 'acdb_kk_events', // Config-ID des Hauptmenüs
        'page_title'  => '– News',
        'menu_title'  => '– News',
        'icon'        => 'dashicons-megaphone',
        'position'    => 28,
        'capacity'    => 'edit_others_posts',
    ],
    'list' => [
        'labels' => [
            'title'      => 'News bearbeiten',
            'button_add' => 'Neue News hinzufügen',
        ],
        'fields'                => ['str_title', 'dtm_date_published', 'ysn_online', 'int_sort_order'], // Spalten der Liste
        'fields_iframe'         => ['str_title'], // Anzeige in der Liste links (geteilte Ansicht)
        'orderby_default'       => 'int_sort_order',
        'order_default'         => 'asc',
        'condition'             => '', // zusätzliche SQL-Bedingung, z. B. "ysn_active = 1"
        'language_filter_field' => '', // Polylang: nur Datensätze der aktuellen Sprache
        'drag_sort'             => 'int_sort_order', // Spalte für die Reihenfolge per Ziehen
        // 'rows_per_page'      => 50, // Einträge pro Seite (Standard 900)
        'tree'                  => [], // Baumstruktur: parent_field, title_field, filter_label
        'help'                  => '', // Hilfeseite (.md oder .html), z. B. __DIR__ . '/help/datei.html'
        'screen_options'        => [
            // Filter unter "Ansicht anpassen":
            // 'show_active' => [
            //     'label'    => 'Status',
            //     'default'  => '',
            //     'type'     => 'radio',
            //     'callback' => fn($value) => $value === '1' ? 'ysn_active = 1' : '',
            //     'choice'   => [['label' => 'Alle', 'value' => ''], ['label' => 'Aktiv', 'value' => 1]],
            // ],
        ],
        'actions' => [], // Zeilenaktionen: 'aktion' => ['label' => '…', 'condition' => fn($row) => true]
        'buttons' => [], // Knöpfe neben "Neuer Datensatz": ['label' => '…', 'action' => 'aktion']
    ],
    'form' => [
        'labels' => [
            'title_add'     => 'Neue News hinzufügen',
            'title_edit'    => 'News bearbeiten',
            'button_add'    => 'News speichern',
            'button_edit'   => 'News speichern',
            'button_saveas' => 'Als neue News speichern',
        ],
        'fields_analyze' => false, // true = Hinweise auf fehlende/unbekannte Felder
        // Reihenfolge und Breite: "feld: klassen", "-" = neue Zeile, tab:Titel … tab-end, accordion:Titel … accordion-end
        'fields_visual' => '
            str_title: col-md-12
            mem_lead: col-md-12
            dtm_date_published: col-md-4
            ysn_online: col-md-4
            int_sort_order: col-md-4
            -
            str_image_1: col-md-4
            str_image_2: col-md-4
            str_image_3: col-md-4
            str_image_4: col-md-4
            str_image_5: col-md-4
            -
            str_video: col-md-6
            str_hyperlink: col-md-6
        ',
        // 'fields' => [], // Alternative zu fields_visual (nicht beides verwenden)
        'conditions' => [], // 'button_edit' / 'button_saveas' => fn($row) => true
        'contents'   => [
            'before' => '', // Inhalt über den Feldern (Text oder fn($id))
            'after'  => '', // Inhalt unter den Feldern
        ],
        'buttons' => [], // 'name' => ['label' => '…', 'page' => 'aktion', 'class' => 'button', 'condition' => fn($row) => true]
    ],

    'fields' => [
        'id' => [
            'label'    => $labels['id'] ?? 'ID',
            'sortable' => true,
        ],

        'str_title' => [
            'label'          => $labels['str_title'] ?? 'Titel',
            'sortable'       => true,
            'searchable'     => true,
            'is_form_hidden' => false,
            'formatter'      => [
                'list' => 'actions', // actions, fky, label_by_value, html, button
                'save' => '', // array_to_list, link, media_id_to_image_url, email_with_name
            ],
            'default'        => '', // Vorgabe für neue Datensätze (Wert oder fn($value, $data))
            // 'value_force'  => fn($value, $data) => $value, // Wert bei jedem Speichern setzen
            // 'before_render' => fn($field, $record) => $field, // Feld pro Datensatz anpassen
            'data'           => [], // data-*-Attribute am Feld
            'acf' => [
                'type' => 'text',
                'instructions' => '', // Hinweis unter dem Feld
                'instruction_placement' => '', // '' = unter dem Feld, 'label' = unter der Beschriftung
                'required' => 0,
                'readonly' => 0,
                'placeholder' => '',
                'default_value' => '',
                'maxlength' => 255,
            ],
        ],

        'mem_lead' => [
            'label'          => $labels['mem_lead'] ?? 'Leadtext',
            'sortable'       => false,
            'searchable'     => true,
            'is_form_hidden' => false,
            'formatter'      => [
                'list' => '', // actions, fky, label_by_value, html, button
                'save' => '', // array_to_list, link, media_id_to_image_url, email_with_name
            ],
            'default'        => '', // Vorgabe für neue Datensätze (Wert oder fn($value, $data))
            // 'value_force'  => fn($value, $data) => $value, // Wert bei jedem Speichern setzen
            // 'before_render' => fn($field, $record) => $field, // Feld pro Datensatz anpassen
            'data'           => [], // data-*-Attribute am Feld
            'acf' => [
                'type' => 'textarea',
                'instructions' => 'Kurzer Anrisstext, der in der Übersicht angezeigt wird.', // Hinweis unter dem Feld
                'instruction_placement' => '', // '' = unter dem Feld, 'label' = unter der Beschriftung
                'required' => 0,
                'readonly' => 0,
                'placeholder' => '',
                'default_value' => '',
                'rows' => 4,
            ],
        ],

        'dtm_date_published' => [
            'label'          => $labels['dtm_date_published'] ?? 'Veröffentlichungsdatum',
            'sortable'       => true,
            'searchable'     => false,
            'is_form_hidden' => false,
            'formatter'      => [
                'list' => '', // actions, fky, label_by_value, html, button
                'save' => '', // array_to_list, link, media_id_to_image_url, email_with_name
            ],
            'default'        => '', // Vorgabe für neue Datensätze (Wert oder fn($value, $data))
            // 'value_force'  => fn($value, $data) => $value, // Wert bei jedem Speichern setzen
            // 'before_render' => fn($field, $record) => $field, // Feld pro Datensatz anpassen
            'data'           => [], // data-*-Attribute am Feld
            'acf' => [
                'type' => 'date_picker',
                'instructions' => '', // Hinweis unter dem Feld
                'instruction_placement' => '', // '' = unter dem Feld, 'label' = unter der Beschriftung
                'required' => 0,
                'readonly' => 0,
                'placeholder' => '',
                'default_value' => '',
            ],
        ],

        'ysn_online' => [
            'label'          => $labels['ysn_online'] ?? 'Online',
            'sortable'       => true,
            'searchable'     => false,
            'is_form_hidden' => false,
            'formatter'      => [
                'list' => '', // actions, fky, label_by_value, html, button
                'save' => '', // array_to_list, link, media_id_to_image_url, email_with_name
            ],
            'default'        => '', // Vorgabe für neue Datensätze (Wert oder fn($value, $data))
            // 'value_force'  => fn($value, $data) => $value, // Wert bei jedem Speichern setzen
            // 'before_render' => fn($field, $record) => $field, // Feld pro Datensatz anpassen
            'data'           => [], // data-*-Attribute am Feld
            'acf' => [
                'type' => 'true_false',
                'instructions' => 'Nur wenn aktiviert, wird die Meldung öffentlich angezeigt.', // Hinweis unter dem Feld
                'instruction_placement' => '', // '' = unter dem Feld, 'label' = unter der Beschriftung
                'required' => 0,
                'readonly' => 0,
                'placeholder' => '',
                'default_value' => 1,
                'ui' => 1, // Schalter statt Checkbox
            ],
        ],

        'int_sort_order' => [
            'label'          => $labels['int_sort_order'] ?? 'Sortierung',
            'sortable'       => true,
            'searchable'     => false,
            'is_form_hidden' => false,
            'formatter'      => [
                'list' => '', // actions, fky, label_by_value, html, button
                'save' => '', // array_to_list, link, media_id_to_image_url, email_with_name
            ],
            'default'        => '', // Vorgabe für neue Datensätze (Wert oder fn($value, $data))
            // 'value_force'  => fn($value, $data) => $value, // Wert bei jedem Speichern setzen
            // 'before_render' => fn($field, $record) => $field, // Feld pro Datensatz anpassen
            'data'           => [], // data-*-Attribute am Feld
            'acf' => [
                'type' => 'number',
                'instructions' => 'Kann auch per Drag & Drop in der Listenansicht (links) sortiert werden.', // Hinweis unter dem Feld
                'instruction_placement' => '', // '' = unter dem Feld, 'label' = unter der Beschriftung
                'required' => 0,
                'readonly' => 0,
                'placeholder' => '',
                'default_value' => '',
            ],
        ],

        'str_image_1' => [
            'label'          => $labels['str_image_1'] ?? 'Bild 1',
            'sortable'       => false,
            'searchable'     => false,
            'is_form_hidden' => false,
            'formatter'      => [
                'list' => '', // actions, fky, label_by_value, html, button
                'save' => '', // array_to_list, link, media_id_to_image_url, email_with_name
            ],
            'default'        => '', // Vorgabe für neue Datensätze (Wert oder fn($value, $data))
            // 'value_force'  => fn($value, $data) => $value, // Wert bei jedem Speichern setzen
            // 'before_render' => fn($field, $record) => $field, // Feld pro Datensatz anpassen
            'data'           => [], // data-*-Attribute am Feld
            'acf' => [
                'type' => 'acdb_file_selector',
                'instructions' => '', // Hinweis unter dem Feld
                'instruction_placement' => '', // '' = unter dem Feld, 'label' = unter der Beschriftung
                'required' => 0,
                'readonly' => 0,
                'placeholder' => '',
                'default_value' => '',
                'file_type' => 'image', // 'image' oder 'file'
                'subfolder' => kk_uploads_subfolder( 'news' ), // Ordner unter wp-content/uploads
                'extensions' => 'jpg,jpeg,png,webp,gif,svg',
                'image_width' => 200,
                'image_height' => 200,
                'disable_upload' => false,
            ],
        ],

        'str_image_2' => [
            'label'          => $labels['str_image_2'] ?? 'Bild 2',
            'sortable'       => false,
            'searchable'     => false,
            'is_form_hidden' => false,
            'formatter'      => [
                'list' => '', // actions, fky, label_by_value, html, button
                'save' => '', // array_to_list, link, media_id_to_image_url, email_with_name
            ],
            'default'        => '', // Vorgabe für neue Datensätze (Wert oder fn($value, $data))
            // 'value_force'  => fn($value, $data) => $value, // Wert bei jedem Speichern setzen
            // 'before_render' => fn($field, $record) => $field, // Feld pro Datensatz anpassen
            'data'           => [], // data-*-Attribute am Feld
            'acf' => [
                'type' => 'acdb_file_selector',
                'instructions' => '', // Hinweis unter dem Feld
                'instruction_placement' => '', // '' = unter dem Feld, 'label' = unter der Beschriftung
                'required' => 0,
                'readonly' => 0,
                'placeholder' => '',
                'default_value' => '',
                'file_type' => 'image', // 'image' oder 'file'
                'subfolder' => kk_uploads_subfolder( 'news' ), // Ordner unter wp-content/uploads
                'extensions' => 'jpg,jpeg,png,webp,gif,svg',
                'image_width' => 200,
                'image_height' => 200,
                'disable_upload' => false,
            ],
        ],

        'str_image_3' => [
            'label'          => $labels['str_image_3'] ?? 'Bild 3',
            'sortable'       => false,
            'searchable'     => false,
            'is_form_hidden' => false,
            'formatter'      => [
                'list' => '', // actions, fky, label_by_value, html, button
                'save' => '', // array_to_list, link, media_id_to_image_url, email_with_name
            ],
            'default'        => '', // Vorgabe für neue Datensätze (Wert oder fn($value, $data))
            // 'value_force'  => fn($value, $data) => $value, // Wert bei jedem Speichern setzen
            // 'before_render' => fn($field, $record) => $field, // Feld pro Datensatz anpassen
            'data'           => [], // data-*-Attribute am Feld
            'acf' => [
                'type' => 'acdb_file_selector',
                'instructions' => '', // Hinweis unter dem Feld
                'instruction_placement' => '', // '' = unter dem Feld, 'label' = unter der Beschriftung
                'required' => 0,
                'readonly' => 0,
                'placeholder' => '',
                'default_value' => '',
                'file_type' => 'image', // 'image' oder 'file'
                'subfolder' => kk_uploads_subfolder( 'news' ), // Ordner unter wp-content/uploads
                'extensions' => 'jpg,jpeg,png,webp,gif,svg',
                'image_width' => 200,
                'image_height' => 200,
                'disable_upload' => false,
            ],
        ],

        'str_image_4' => [
            'label'          => $labels['str_image_4'] ?? 'Bild 4',
            'sortable'       => false,
            'searchable'     => false,
            'is_form_hidden' => false,
            'formatter'      => [
                'list' => '', // actions, fky, label_by_value, html, button
                'save' => '', // array_to_list, link, media_id_to_image_url, email_with_name
            ],
            'default'        => '', // Vorgabe für neue Datensätze (Wert oder fn($value, $data))
            // 'value_force'  => fn($value, $data) => $value, // Wert bei jedem Speichern setzen
            // 'before_render' => fn($field, $record) => $field, // Feld pro Datensatz anpassen
            'data'           => [], // data-*-Attribute am Feld
            'acf' => [
                'type' => 'acdb_file_selector',
                'instructions' => '', // Hinweis unter dem Feld
                'instruction_placement' => '', // '' = unter dem Feld, 'label' = unter der Beschriftung
                'required' => 0,
                'readonly' => 0,
                'placeholder' => '',
                'default_value' => '',
                'file_type' => 'image', // 'image' oder 'file'
                'subfolder' => kk_uploads_subfolder( 'news' ), // Ordner unter wp-content/uploads
                'extensions' => 'jpg,jpeg,png,webp,gif,svg',
                'image_width' => 200,
                'image_height' => 200,
                'disable_upload' => false,
            ],
        ],

        'str_image_5' => [
            'label'          => $labels['str_image_5'] ?? 'Bild 5',
            'sortable'       => false,
            'searchable'     => false,
            'is_form_hidden' => false,
            'formatter'      => [
                'list' => '', // actions, fky, label_by_value, html, button
                'save' => '', // array_to_list, link, media_id_to_image_url, email_with_name
            ],
            'default'        => '', // Vorgabe für neue Datensätze (Wert oder fn($value, $data))
            // 'value_force'  => fn($value, $data) => $value, // Wert bei jedem Speichern setzen
            // 'before_render' => fn($field, $record) => $field, // Feld pro Datensatz anpassen
            'data'           => [], // data-*-Attribute am Feld
            'acf' => [
                'type' => 'acdb_file_selector',
                'instructions' => '', // Hinweis unter dem Feld
                'instruction_placement' => '', // '' = unter dem Feld, 'label' = unter der Beschriftung
                'required' => 0,
                'readonly' => 0,
                'placeholder' => '',
                'default_value' => '',
                'file_type' => 'image', // 'image' oder 'file'
                'subfolder' => kk_uploads_subfolder( 'news' ), // Ordner unter wp-content/uploads
                'extensions' => 'jpg,jpeg,png,webp,gif,svg',
                'image_width' => 200,
                'image_height' => 200,
                'disable_upload' => false,
            ],
        ],

        'str_video' => [
            'label'          => $labels['str_video'] ?? 'Video',
            'sortable'       => false,
            'searchable'     => false,
            'is_form_hidden' => false,
            'formatter'      => [
                'list' => '', // actions, fky, label_by_value, html, button
                'save' => '', // array_to_list, link, media_id_to_image_url, email_with_name
            ],
            'default'        => '', // Vorgabe für neue Datensätze (Wert oder fn($value, $data))
            // 'value_force'  => fn($value, $data) => $value, // Wert bei jedem Speichern setzen
            // 'before_render' => fn($field, $record) => $field, // Feld pro Datensatz anpassen
            'data'           => [], // data-*-Attribute am Feld
            'acf' => [
                'type' => 'text',
                'instructions' => 'YouTube-Link oder volle URL zu einer Videodatei', // Hinweis unter dem Feld
                'instruction_placement' => '', // '' = unter dem Feld, 'label' = unter der Beschriftung
                'required' => 0,
                'readonly' => 0,
                'placeholder' => '',
                'default_value' => '',
                'maxlength' => 255,
            ],
        ],

        'str_hyperlink' => [
            'label'          => $labels['str_hyperlink'] ?? 'Link "Mehr erfahren"',
            'sortable'       => false,
            'searchable'     => false,
            'is_form_hidden' => false,
            'formatter'      => [
                'list' => '', // actions, fky, label_by_value, html, button
                'save' => '', // array_to_list, link, media_id_to_image_url, email_with_name
            ],
            'default'        => '', // Vorgabe für neue Datensätze (Wert oder fn($value, $data))
            // 'value_force'  => fn($value, $data) => $value, // Wert bei jedem Speichern setzen
            // 'before_render' => fn($field, $record) => $field, // Feld pro Datensatz anpassen
            'data'           => [], // data-*-Attribute am Feld
            'acf' => [
                'type' => 'acdb_link',
                'instructions' => 'Externer oder interner Link', // Hinweis unter dem Feld
                'instruction_placement' => '', // '' = unter dem Feld, 'label' = unter der Beschriftung
                'required' => 0,
                'readonly' => 0,
                'placeholder' => '',
                'default_value' => '',
                'post_type' => ['page'], // durchsuchte Inhalte
            ],
        ],
    ],
]);
