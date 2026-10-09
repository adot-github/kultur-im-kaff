<?php
/*
 * Tabelle wp_kk_events (Programm / Archiv) – Gerüst vom Admin Builder, Einstellungen von Hand.
 * Wird aus admin/index.php eingebunden ($editor kommt von dort).
 * Verknüpfungen:
 *  - fky_location -> wp_kk_event_locations (einfache Fremdschlüssel-Relation, "fky")
 *  - tags         -> wp_kk_event_tags      (Mehrfach-Relation über die Kreuztabelle wp_kk_event_to_tags, "dbx")
 * Leere Parameter sind wirkungslos und können gelöscht werden; auskommentierte Parameter
 * wirken schon durch ihr Vorhandensein und sind deshalb nur als Vorlage aufgeführt.
 */
if (!isset($editor)) {
    return;
}

// Hauptmenü "Kultur im Kaff"; die übrigen Tabellen hängen per menu_parent 'acdb_kk_events' darunter
// Feldbeschriftungen aus wp_acdb_database_fields (Fallback: Text nach ??)
$labels = acdb_field_labels('kk_events');

$root_config_id = $editor->add_table_config([
    'table' => 'kk_events',
    // 'id'   => 'acdb_kk_events', // Standard: acdb_<tabelle>
    // 'skin' => 'iframe',        // Ansicht erzwingen: 'iframe' = Liste links, Formular rechts
    // 'sql'  => [
    //     'table'         => '',  // andere Tabelle/View für die Liste
    //     'select_fields' => [],  // zusätzliche SELECT-Ausdrücke
    // ],
    'menu' => [
        'rename_root_label' => 'Kultur im Kaff', // Beschriftung des Hauptmenüs
        'page_title'        => '– Anlässe',
        'menu_title'        => '– Anlässe',
        'icon'              => get_stylesheet_directory_uri() . '/db-custom/kultur-im-kaff/admin/kik.png',
        'position'          => 1,
        'capacity'          => 'edit_others_posts', // benötigte Berechtigung
    ],
    'list' => [
        'labels' => [
            'title'      => 'Anlässe bearbeiten',
            'button_add' => 'Neuen Anlass hinzufügen',
        ],
        'fields'                => ['str_title', 'dtm_date_from', 'fky_location'], // Spalten der Liste
        'fields_iframe'         => ['str_title', 'dtm_date_from'], // Anzeige in der Liste links (geteilte Ansicht)
        'orderby_default'       => 'dtm_date_from',
        'order_default'         => 'desc',
        'condition'             => '', // zusätzliche SQL-Bedingung, z. B. "ysn_active = 1"
        'language_filter_field' => '', // Polylang: nur Datensätze der aktuellen Sprache
        'drag_sort'             => '', // Spalte für die Reihenfolge per Ziehen
        // 'rows_per_page'      => 50, // Einträge pro Seite (Standard 900)
        'tree'                  => [], // Baumstruktur: parent_field, title_field, filter_label
        'help'                  => '', // Hilfeseite (.md oder .html), z. B. __DIR__ . '/help/datei.html'
        // Filter im Bereich "Ansicht anpassen" bzw. "Filter und Tools"
        'screen_options'        => [
            'show_by_season' => [
                'label'    => 'Anzeigen',
                'default'  => '',
                'type'     => 'radio',
                'callback' => function ($value) {
                    if ($value == '1') {
                        return 'ysn_current_season = 1';
                    }
                    if ($value == '2') {
                        return 'ysn_anniversary = 1';
                    }
                    return '';
                },
                'choice'   => [
                    ['label' => 'Alle',               'value' => ''],
                    ['label' => 'Aktuelles Programm', 'value' => 1],
                    ['label' => 'Jubiläumsanlässe',   'value' => 2],
                ],
            ],
        ],
        'actions' => [], // Zeilenaktionen: 'aktion' => ['label' => '…', 'condition' => fn($row) => true]
        'buttons' => [], // Knöpfe neben "Neuer Datensatz": ['label' => '…', 'action' => 'aktion']
    ],
    'form' => [
        'labels' => [
            'title_add'     => 'Neuen Anlass hinzufügen',
            'title_edit'    => 'Anlass bearbeiten',
            'button_add'    => 'Anlass speichern',
            'button_edit'   => 'Anlass speichern',
            'button_saveas' => 'Als neuen Anlass speichern',
        ],
        'fields_analyze' => false, // true = Hinweise auf fehlende/unbekannte Felder
        // Reihenfolge und Breite: "feld: klassen", "-" = neue Zeile, tab:Titel … tab-end, accordion:Titel … accordion-end
        'fields_visual' => '
            tab:Hauptinformationen
            str_title: col-md-8
            fky_location: col-md-4
            mem_description: col-md-8
            -
            str_artist: col-md-6
            str_artist_detail: col-md-6
            dtm_date_from: col-md-2
            dtm_time_from: col-md-2
            dtm_date_to: col-md-2
            dtm_time_to: col-md-2
            str_date_extra: col-md-4
            tags: col-md-12
            ysn_current_season: col-md-3
            ysn_anniversary: col-md-3
            ysn_active: col-md-3
            tab:Preise
            num_price_adults: col-md-3
            num_price_members: col-md-3
            num_price_children: col-md-3
            str_price_remark: col-md-3
            tab:Links & Medien
            str_image: col-md-6
            str_video: col-md-6
            str_hyperlink: col-md-6
            str_hyperlink_sales: col-md-6
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

        'fky_location' => [
            'label'          => $labels['fky_location'] ?? 'Spielort',
            'sortable'       => true,
            'searchable'     => false,
            'is_form_hidden' => false,
            'formatter'      => [
                'list' => 'fky', // actions, fky, label_by_value, html, button
                'save' => '', // array_to_list, link, media_id_to_image_url, email_with_name
            ],
            'default'        => '', // Vorgabe für neue Datensätze (Wert oder fn($value, $data))
            // 'value_force'  => fn($value, $data) => $value, // Wert bei jedem Speichern setzen
            // 'before_render' => fn($field, $record) => $field, // Feld pro Datensatz anpassen
            'data'           => [], // data-*-Attribute am Feld
            'acf' => [
                'type' => 'acdb_relationship',
                'instructions' => '', // Hinweis unter dem Feld
                'instruction_placement' => '', // '' = unter dem Feld, 'label' = unter der Beschriftung
                'required' => 0,
                'readonly' => 0,
                'placeholder' => '',
                'default_value' => '',
            ],
            'fky' => [
                'multiple' => false, // mehrere Werte: true + formatter save 'array_to_list'
                'db'       => [
                    'table'     => 'kk_event_locations',
                    'id'        => 'id',
                    'label'     => 'str_location',
                    'condition' => '',
                    'order_by'  => '',
                ],
            ],
        ],

        'mem_description' => [
            'label'          => $labels['mem_description'] ?? 'Beschreibung',
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

        'str_artist' => [
            'label'          => $labels['str_artist'] ?? 'Künstler:in/Act',
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

        'str_artist_detail' => [
            'label'          => $labels['str_artist_detail'] ?? 'Künstler:in-Detail',
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
                'instructions' => 'Kurze Zusatzzeile, z. B. Rollenaufteilung oder Besetzung.', // Hinweis unter dem Feld
                'instruction_placement' => '', // '' = unter dem Feld, 'label' = unter der Beschriftung
                'required' => 0,
                'readonly' => 0,
                'placeholder' => '',
                'default_value' => '',
                'maxlength' => 255,
            ],
        ],

        'dtm_date_from' => [
            'label'          => $labels['dtm_date_from'] ?? 'Datum von',
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
                'display_format' => 'j.n.Y', // Anzeige in Liste und Formular, z. B. 1.1.2027
                'instructions' => '', // Hinweis unter dem Feld
                'instruction_placement' => '', // '' = unter dem Feld, 'label' = unter der Beschriftung
                'required' => 0,
                'readonly' => 0,
                'placeholder' => '',
                'default_value' => '',
            ],
        ],

        'dtm_time_from' => [
            'label'          => $labels['dtm_time_from'] ?? 'Zeit von',
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
                'type' => 'time_picker',
                'instructions' => '', // Hinweis unter dem Feld
                'instruction_placement' => '', // '' = unter dem Feld, 'label' = unter der Beschriftung
                'required' => 0,
                'readonly' => 0,
                'placeholder' => '',
                'default_value' => '',
            ],
        ],

        'dtm_date_to' => [
            'label'          => $labels['dtm_date_to'] ?? 'Datum bis',
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
                'type' => 'date_picker',
                'display_format' => 'j.n.Y', // Anzeige in Liste und Formular, z. B. 1.1.2027
                'instructions' => 'Nur bei mehrtägigen Anlässen (z. B. Ausstellungen) ausfüllen.', // Hinweis unter dem Feld
                'instruction_placement' => '', // '' = unter dem Feld, 'label' = unter der Beschriftung
                'required' => 0,
                'readonly' => 0,
                'placeholder' => '',
                'default_value' => '',
            ],
        ],

        'dtm_time_to' => [
            'label'          => $labels['dtm_time_to'] ?? 'Zeit bis',
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
                'type' => 'time_picker',
                'instructions' => '', // Hinweis unter dem Feld
                'instruction_placement' => '', // '' = unter dem Feld, 'label' = unter der Beschriftung
                'required' => 0,
                'readonly' => 0,
                'placeholder' => '',
                'default_value' => '',
            ],
        ],

        'str_date_extra' => [
            'label'          => $labels['str_date_extra'] ?? 'Zusatzinfo zu Datum/Ort',
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
                'instructions' => 'Erscheint klein unter Datum/Zeit/Ort, z. B. "Kulturbar ab 19.00 Uhr" oder "Kein Vorverkauf".', // Hinweis unter dem Feld
                'instruction_placement' => '', // '' = unter dem Feld, 'label' = unter der Beschriftung
                'required' => 0,
                'readonly' => 0,
                'placeholder' => '',
                'default_value' => '',
                'maxlength' => 255,
            ],
        ],

        // Keine Tabellenspalte: Mehrfach-Relation über die Kreuztabelle wp_kk_event_to_tags
        'tags' => [
            'label'          => $labels['tags'] ?? 'Tags',
            'sortable'       => false,
            'searchable'     => false,
            'is_form_hidden' => false,
            'formatter'      => [
                'list' => '', // actions, fky, label_by_value, html, button
                'save' => '', // array_to_list, link, media_id_to_image_url, email_with_name
            ],
            'data'           => [], // data-*-Attribute am Feld
            'acf' => [
                'type' => 'acdb_relationship',
                'instructions' => '', // Hinweis unter dem Feld
                'instruction_placement' => '', // '' = unter dem Feld, 'label' = unter der Beschriftung
                'required' => 0,
                'readonly' => 0,
            ],
            'dbx' => [
                'allow_new' => true, // neue Tags direkt im Feld anlegen
                'db'        => [
                    'tbx_table'     => 'kk_event_to_tags',
                    'tbx_id_main'   => 'fky_event_id',
                    'tbx_id_linked' => 'fky_tag_id',
                    'linked_table'  => 'kk_event_tags',
                    'linked_label'  => 'str_tag',
                ],
            ],
        ],

        'ysn_current_season' => [
            'label'          => $labels['ysn_current_season'] ?? 'Aktuelle Saison',
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
                'instructions' => '', // Hinweis unter dem Feld
                'instruction_placement' => '', // '' = unter dem Feld, 'label' = unter der Beschriftung
                'required' => 0,
                'readonly' => 0,
                'placeholder' => '',
                'default_value' => 0,
                'ui' => 1, // Schalter statt Checkbox
            ],
        ],

        'ysn_anniversary' => [
            'label'          => $labels['ysn_anniversary'] ?? 'Jubiläum',
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
                'instructions' => '', // Hinweis unter dem Feld
                'instruction_placement' => '', // '' = unter dem Feld, 'label' = unter der Beschriftung
                'required' => 0,
                'readonly' => 0,
                'placeholder' => '',
                'default_value' => 0,
                'ui' => 1, // Schalter statt Checkbox
            ],
        ],

        'ysn_active' => [
            'label'          => $labels['ysn_active'] ?? 'Aktiv',
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
                'type' => 'true_false',
                'instructions' => '', // Hinweis unter dem Feld
                'instruction_placement' => '', // '' = unter dem Feld, 'label' = unter der Beschriftung
                'required' => 0,
                'readonly' => 0,
                'placeholder' => '',
                'default_value' => 0,
                'ui' => 1, // Schalter statt Checkbox
            ],
        ],

        'num_price_adults' => [
            'label'          => $labels['num_price_adults'] ?? 'Preis Erwachsene (CHF)',
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
                'step' => '0.05',
            ],
        ],

        'num_price_members' => [
            'label'          => $labels['num_price_members'] ?? 'Preis Mitglieder (CHF)',
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
                'step' => '0.05',
            ],
        ],

        'num_price_children' => [
            'label'          => $labels['num_price_children'] ?? 'Preis Kinder (CHF)',
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
                'step' => '0.05',
            ],
        ],

        'str_price_remark' => [
            'label'          => $labels['str_price_remark'] ?? 'Preis-Bemerkung',
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
                'instructions' => 'Wird zusätzlich zu den Preisen angezeigt, z. B. "Kollekte". Sind alle drei Preise leer/0, ersetzt diese Bemerkung die Preiszeile.', // Hinweis unter dem Feld
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
                'subfolder' => kk_uploads_subfolder( 'events' ), // Ordner unter wp-content/uploads
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
                'instructions' => 'YouTube-Link oder volle URL zu einer Videodatei unter /events/.', // Hinweis unter dem Feld
                'instruction_placement' => '', // '' = unter dem Feld, 'label' = unter der Beschriftung
                'required' => 0,
                'readonly' => 0,
                'placeholder' => '',
                'default_value' => '',
                'maxlength' => 255,
            ],
        ],

        'str_hyperlink' => [
            'label'          => $labels['str_hyperlink'] ?? 'Link «Mehr Infos»',
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
                'instructions' => 'Seite der Website suchen oder beliebige URL eingeben.', // Hinweis unter dem Feld
                'instruction_placement' => '', // '' = unter dem Feld, 'label' = unter der Beschriftung
                'required' => 0,
                'readonly' => 0,
                'placeholder' => '',
                'default_value' => '',
                'post_type' => ['page'], // durchsuchte Inhalte
            ],
        ],

        'str_hyperlink_sales' => [
            'label'          => $labels['str_hyperlink_sales'] ?? 'Vorverkauf-Link',
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
                'instructions' => 'Wird im Archiv nie angezeigt, auch wenn hier ein Link gesetzt ist.', // Hinweis unter dem Feld
                'instruction_placement' => '', // '' = unter dem Feld, 'label' = unter der Beschriftung
                'required' => 0,
                'readonly' => 0,
                'placeholder' => '',
                'default_value' => '',
            ],
        ],
    ],
]);
