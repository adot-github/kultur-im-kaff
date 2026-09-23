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
})();
