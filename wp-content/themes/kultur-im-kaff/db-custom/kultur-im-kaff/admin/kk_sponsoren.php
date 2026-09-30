<?php
/*
 * Tabelle wp_kk_sponsoren – erstellt mit dem Admin Builder am 30.09.2026.
 * Wird aus admin/index.php eingebunden ($editor kommt von dort).
 * Leere Parameter sind wirkungslos und können gelöscht werden; auskommentierte Parameter
 * wirken schon durch ihr Vorhandensein und sind deshalb nur als Vorlage aufgeführt.
 */
if (!isset($editor)) {
    return;
}

// Feldbeschriftungen aus wp_acdb_database_fields (Fallback: Text nach ??)
$labels = acdb_field_labels('kk_sponsoren');

$editor->add_table_config([
    'table' => 'kk_sponsoren',
    // 'id'   => 'acdb_kk_sponsoren', // Standard: acdb_<tabelle>
    // 'skin' => 'iframe',        // Ansicht erzwingen: 'iframe' = Liste links, Formular rechts
    // 'sql'  => [
    //     'table'         => '',  // andere Tabelle/View für die Liste
    //     'select_fields' => [],  // zusätzliche SELECT-Ausdrücke
    // ],
    'menu' => [
        'menu_parent' => 'acdb_kk_events', // Config-ID des Hauptmenüs
        'page_title'  => '– Sponsoren',
        'menu_title'  => '– Sponsoren',
        'position'    => 44,
        'capacity'    => 'edit_others_posts',
    ],
    'list' => [
        'labels' => [
            'title'      => 'Sponsoren',
            'button_add' => 'Neuer Datensatz',
        ],
        'fields'                => ['str_sponsor'], // Spalten der Liste
        'fields_iframe'         => ['str_sponsor'], // Anzeige in der Liste links (geteilte Ansicht)
        'orderby_default'       => 'str_sponsor',
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
            'title_add'     => 'Neuer Datensatz',
            'title_edit'    => 'Sponsoren bearbeiten',
            'button_add'    => 'Speichern',
            'button_edit'   => 'Speichern',
            'button_saveas' => 'Als neuen Datensatz speichern',
        ],
        'fields_analyze' => false, // true = Hinweise auf fehlende/unbekannte Felder
        // Reihenfolge und Breite: "feld: klassen", "-" = neue Zeile, tab:Titel … tab-end, accordion:Titel … accordion-end
        'fields_visual' => '
            str_sponsor: col-md-6
            mem_sponsor_text: col-md-12
            str_logo: col-md-6
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

        'str_sponsor' => [
            'label'          => $labels['str_sponsor'] ?? 'Sponsor',
            'class'          => '', // Bootstrap-Klassen (fields_visual hat Vorrang)
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

        'mem_sponsor_text' => [
            'label'          => $labels['mem_sponsor_text'] ?? 'Beschreibung',
            'class'          => '', // Bootstrap-Klassen (fields_visual hat Vorrang)
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

        'str_logo' => [
            'label'          => $labels['str_logo'] ?? 'Logo',
            'class'          => '', // Bootstrap-Klassen (fields_visual hat Vorrang)
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
                'subfolder' => '/sponsoren/', // Ordner unter wp-content/uploads
                'extensions' => 'jpg,jpeg,png,webp,gif,svg',
                'image_width' => 300,
                'image_height' => 300,
                'disable_upload' => false,
            ],
        ],

        'str_hyperlink' => [
            'label'          => $labels['str_hyperlink'] ?? 'Link',
            'class'          => '', // Bootstrap-Klassen (fields_visual hat Vorrang)
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
                'instructions' => '', // Hinweis unter dem Feld
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
