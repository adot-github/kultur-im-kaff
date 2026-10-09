<?php
(function () {
    if ( ! function_exists( 'Acdb_DB_Editor' ) ) {
        return;
    }
    $editor = Acdb_DB_Editor();
    $editor->init_grid();

    require_once __DIR__ . '/kk_events.php';
    require_once __DIR__ . '/kk_event_locations.php';
    require_once __DIR__ . '/kk_event_tags.php';
    require_once __DIR__ . '/kk_news.php';

    // Menü-Icon (kik.png, 28x28) vertikal zentrieren – WP-Standard ist padding-top: 9px
    add_action( 'admin_head', function () {
        echo '<style>#adminmenu #toplevel_page_acdb_kk_events div.wp-menu-image img{padding-top:3px;}</style>';
    } );
    
    require_once __DIR__ . '/kk_jub_slots.php';
    require_once __DIR__ . '/kk_jub_slot_types.php';
    require_once __DIR__ . '/kk_persons.php';
    require_once __DIR__ . '/kk_sponsoren.php';

    // Bildverwaltung (Ordner und Dateien unter wp-content/uploads) im Menü "Kultur im Kaff"
    if ( function_exists( 'acdb_media_manager' ) ) {
        acdb_media_manager( array(
            'slug'   => 'kk_bilder',
            'parent' => 'acdb_kk_events',
            'label'  => '– Bilder',
            'root'   => KK_UPLOADS_FOLDER, // nur uploads/kultur-im-kaff/ (siehe kk-uploads.php)
        ) );
    }
})();
