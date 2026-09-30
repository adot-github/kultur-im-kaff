<?php
/*
 * Tabelle wp_kk_persons – erstellt mit dem Admin Builder am 30.09.2026.
 * Wird aus admin/index.php eingebunden ($editor kommt von dort).
 * Leere Parameter sind wirkungslos und können gelöscht werden; auskommentierte Parameter
 * wirken schon durch ihr Vorhandensein und sind deshalb nur als Vorlage aufgeführt.
 */
if (!isset($editor)) {
    return;
}

// Feldbeschriftungen aus wp_acdb_database_fields (Fallback: Text nach ??)
$labels = acdb_field_labels('kk_persons');

$editor->add_table_config([
    'table' => 'kk_persons',
    // 'id'   => 'acdb_kk_persons', // Standard: acdb_<tabelle>
    // 'skin' => 'iframe',        // Ansicht erzwingen: 'iframe' = Liste links, Formular rechts
    'sql'  => [
        // 'table'      => '',  // andere Tabelle/View für die Liste
        'select_fields' => [
            "CONCAT(IFNULL(str_lastname, ''), ' ', IFNULL(str_firstname, '')) AS fullname", // Liste: "Nachname Vorname"
        ],
    ],
    'menu' => [
        'menu_parent' => 'acdb_kk_events', // Config-ID des Hauptmenüs
        'page_title'  => '– Vorstand',
        'menu_title'  => '– Vorstand',
        'position'    => 40,
        'capacity'    => 'edit_others_posts',
    ],
    'list' => [
        'labels' => [
            'title'      => 'Vorstand',
            'button_add' => 'Neuer Datensatz',
        ],
        'fields'                => ['fullname'], // Spalten der Liste
        'fields_iframe'         => ['fullname'], // Anzeige in der Liste links (geteilte Ansicht)
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
            'title_add'     => 'Neuer Datensatz',
            'title_edit'    => 'Persons bearbeiten',
            'button_add'    => 'Speichern',
            'button_edit'   => 'Speichern',
            'button_saveas' => 'Als neuen Datensatz speichern',
        ],
        'fields_analyze' => false, // true = Hinweise auf fehlende/unbekannte Felder
        // Reihenfolge und Breite: "feld: klassen", "-" = neue Zeile, tab:Titel … tab-end, accordion:Titel … accordion-end
        'fields_visual' => '
            str_firstname: col-md-6
            str_lastname: col-md-6
            str_role: col-md-6
            str_tags: col-md-6
            str_image: col-md-6
            str_claim: col-md-6
            str_email: col-md-6
            int_sort_order: col-md-3
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

        // Virtuelles Feld aus sql.select_fields: nur in der Liste (gesucht wird über Nach-/Vorname)
        'fullname' => [
            'label'          => $labels['fullname'] ?? 'Name',
            'sortable'       => true,
            'is_form_hidden' => true,
            'formatter'      => [
                'list' => 'actions',
            ],
        ],

        'str_firstname' => [
            'label'          => $labels['str_firstname'] ?? 'Vorname',
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

        'str_lastname' => [
            'label'          => $labels['str_lastname'] ?? 'Nachname',
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

        'str_role' => [
            'label'          => $labels['str_role'] ?? 'Funktion',
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

        'str_tags' => [
            'label'          => $labels['str_tags'] ?? 'Tags',
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

        'str_image' => [
            'label'          => $labels['str_image'] ?? 'Bild',
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
                'subfolder' => '/vorstand/', // Ordner unter wp-content/uploads
                'extensions' => 'jpg,jpeg,png,webp,gif,svg',
                'image_width' => 200,
                'image_height' => 200,
                'disable_upload' => false,
            ],
        ],

        'str_claim' => [
            'label'          => $labels['str_claim'] ?? 'Leitspruch',
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

        'str_email' => [
            'label'          => $labels['str_email'] ?? 'E-Mail',
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
                'type' => 'email',
                'instructions' => '', // Hinweis unter dem Feld
                'instruction_placement' => '', // '' = unter dem Feld, 'label' = unter der Beschriftung
                'required' => 0,
                'readonly' => 0,
                'placeholder' => '',
                'default_value' => '',
            ],
        ],

        'int_sort_order' => [
            'label'          => $labels['int_sort_order'] ?? 'Sortierung',
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
                'type' => 'number',
                'instructions' => '', // Hinweis unter dem Feld
                'instruction_placement' => '', // '' = unter dem Feld, 'label' = unter der Beschriftung
                'required' => 0,
                'readonly' => 0,
                'placeholder' => '',
                'default_value' => '',
            ],
        ],

    ],
]);
