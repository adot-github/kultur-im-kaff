<?php
/*
 * Tabelle wp_kk_event_locations (Spielorte) – Gerüst vom Admin Builder, Einstellungen von Hand.
 * Wird aus admin/index.php eingebunden ($editor kommt von dort).
 * Leere Parameter sind wirkungslos und können gelöscht werden; auskommentierte Parameter
 * wirken schon durch ihr Vorhandensein und sind deshalb nur als Vorlage aufgeführt.
 */
if (!isset($editor)) {
    return;
}

// Feldbeschriftungen aus wp_acdb_database_fields (Fallback: Text nach ??)
$labels = acdb_field_labels('kk_event_locations');

$editor->add_table_config([
    'table' => 'kk_event_locations',
    // 'id'   => 'acdb_kk_event_locations', // Standard: acdb_<tabelle>
    // 'skin' => 'iframe',        // Ansicht erzwingen: 'iframe' = Liste links, Formular rechts
    // 'sql'  => [
    //     'table'         => '',  // andere Tabelle/View für die Liste
    //     'select_fields' => [],  // zusätzliche SELECT-Ausdrücke
    // ],
    'menu' => [
        'menu_parent' => 'acdb_kk_events', // Config-ID des Hauptmenüs
        'page_title'  => '– Spielorte',
        'menu_title'  => '– Spielorte',
        'icon'        => 'dashicons-location-alt',
        'position'    => 26,
        'capacity'    => 'edit_others_posts',
    ],
    'list' => [
        'labels' => [
            'title'      => 'Spielorte bearbeiten',
            'button_add' => 'Neuen Spielort hinzufügen',
        ],
        'fields'                => ['str_location', 'str_city'], // Spalten der Liste
        'fields_iframe'         => ['str_location'], // Anzeige in der Liste links (geteilte Ansicht)
        'orderby_default'       => 'str_location',
        'order_default'         => 'asc',
        'condition'             => '', // zusätzliche SQL-Bedingung, z. B. "ysn_active = 1"
        'language_filter_field' => '', // Polylang: nur Datensätze der aktuellen Sprache
        'drag_sort'             => '', // Spalte für die Reihenfolge per Ziehen
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
            'title_add'     => 'Neuen Spielort hinzufügen',
            'title_edit'    => 'Spielort bearbeiten',
            'button_add'    => 'Spielort speichern',
            'button_edit'   => 'Spielort speichern',
            'button_saveas' => 'Als neuen Spielort speichern',
        ],
        'fields_analyze' => false, // true = Hinweise auf fehlende/unbekannte Felder
        // Reihenfolge und Breite: "feld: klassen", "-" = neue Zeile, tab:Titel … tab-end, accordion:Titel … accordion-end
        'fields_visual' => '
            str_location: col-md-8
            str_google: col-md-4
            str_address: col-md-4
            str_zip: col-md-2
            str_city: col-md-6
            str_travel: col-md-12
            mem_description: col-md-12
            -
            str_image_1: col-md-4
            str_image_2: col-md-4
            str_image_3: col-md-4
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

        'str_location' => [
            'label'          => $labels['str_location'] ?? 'Name',
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

        'mem_description' => [
            'label'          => $labels['mem_description'] ?? 'Beschreibung',
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
                'type' => 'acdb_ckeditor',
                'instructions' => '', // Hinweis unter dem Feld
                'instruction_placement' => '', // '' = unter dem Feld, 'label' = unter der Beschriftung
                'required' => 0,
                'readonly' => 0,
                'placeholder' => '',
                'default_value' => '',
            ],
            'ckeditor' => [
                'mode'      => 'standalone', // 'inline' oder 'standalone'
                'subfolder' => '', // Upload-Ordner für Bilder im Editor
                'ck_config' => [], // weitere CKEditor-Einstellungen
            ],
        ],

        'str_address' => [
            'label'          => $labels['str_address'] ?? 'Adresse (Strasse/Nr.)',
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
                'instructions' => '', // Hinweis unter dem Feld
                'instruction_placement' => '', // '' = unter dem Feld, 'label' = unter der Beschriftung
                'required' => 0,
                'readonly' => 0,
                'placeholder' => '',
                'default_value' => '',
                'maxlength' => 255,
            ],
        ],

        'str_zip' => [
            'label'          => $labels['str_zip'] ?? 'PLZ',
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
                'instructions' => '', // Hinweis unter dem Feld
                'instruction_placement' => '', // '' = unter dem Feld, 'label' = unter der Beschriftung
                'required' => 0,
                'readonly' => 0,
                'placeholder' => '',
                'default_value' => '',
                'maxlength' => 255,
            ],
        ],

        'str_city' => [
            'label'          => $labels['str_city'] ?? 'Ort',
            'sortable'       => true,
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

        'str_travel' => [
            'label'          => $labels['str_travel'] ?? 'Anreise',
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
                'instructions' => 'Kurzer Hinweistext, z. B. Bus-/Parkplatz-Info.', // Hinweis unter dem Feld
                'instruction_placement' => '', // '' = unter dem Feld, 'label' = unter der Beschriftung
                'required' => 0,
                'readonly' => 0,
                'placeholder' => '',
                'default_value' => '',
                'maxlength' => 255,
            ],
        ],

        'str_google' => [
            'label'          => $labels['str_google'] ?? 'Google-Maps-Link',
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
                'type' => 'url',
                'instructions' => 'Bitte die vollständige URL aus der Browser-Adresszeile einfügen (mit Koordinaten, z. B. "@47.41...,8.04..."), nicht den kurzen "Teilen"-Link – nur so kann der Kartenausschnitt auf der Spielorte-Seite angezeigt werden.', // Hinweis unter dem Feld
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
                'subfolder' => '/spielorte/', // Ordner unter wp-content/uploads
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
                'subfolder' => '/spielorte/', // Ordner unter wp-content/uploads
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
                'subfolder' => '/spielorte/', // Ordner unter wp-content/uploads
                'extensions' => 'jpg,jpeg,png,webp,gif,svg',
                'image_width' => 200,
                'image_height' => 200,
                'disable_upload' => false,
            ],
        ],
    ],
]);
