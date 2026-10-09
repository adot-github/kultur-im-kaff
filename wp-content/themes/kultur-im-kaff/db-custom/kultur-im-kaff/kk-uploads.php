<?php
/**
 * Bildordner von Kultur im Kaff: alle Ordner (events, news, spielorte, sponsoren, vorstand)
 * liegen unter wp-content/uploads/kultur-im-kaff/. Templates und Configs holen den Pfad hier,
 * ein späterer Umzug ist damit eine Zeile (KK_UPLOADS_FOLDER).
 *
 * Wird vor den Admin-Configs geladen (functions.php des Moduls, eingebunden vom Plugin).
 */

defined( 'ABSPATH' ) || exit;

const KK_UPLOADS_FOLDER = 'kultur-im-kaff';
const KK_UPLOADS_SUBFOLDERS = array( 'events', 'news', 'spielorte', 'sponsoren', 'vorstand' );

/** URL im Bildordner, z. B. kk_uploads_url( 'events/' ) => https://…/wp-content/uploads/kultur-im-kaff/events/ */
function kk_uploads_url( $sub = '' ) {
    return trailingslashit( wp_upload_dir()['baseurl'] ) . KK_UPLOADS_FOLDER . '/' . ltrim( (string) $sub, '/' );
}

/** Dateipfad im Bildordner, z. B. kk_uploads_dir( 'sponsoren/' ) */
function kk_uploads_dir( $sub = '' ) {
    return trailingslashit( wp_upload_dir()['basedir'] ) . KK_UPLOADS_FOLDER . '/' . ltrim( (string) $sub, '/' );
}

/** 'subfolder' für acdb_file_selector-Felder, z. B. kk_uploads_subfolder( 'events' ) => /kultur-im-kaff/events/ */
function kk_uploads_subfolder( $sub ) {
    return '/' . KK_UPLOADS_FOLDER . '/' . trim( (string) $sub, '/' ) . '/';
}

/*
 * Einmaliger Umzug uploads/<ordner>/ -> uploads/kultur-im-kaff/<ordner>/ (uploads ist nicht in Git,
 * darum läuft er auf jeder Installation selbst – beim ersten Aufruf nach dem Deployment).
 * - verschiebt die Ordner (bestehende Zielordner werden zusammengeführt, nichts wird überschrieben)
 * - ersetzt "/uploads/<ordner>/" in den Textspalten der wp_kk_*-Tabellen und in Seiteninhalten
 *   (keine Optionen/Revisionen; in den Bildfeldern stehen nur Dateinamen)
 * Ergebnis in der Option kk_uploads_migrated. Kann nach dem Umzug auf allen Servern entfernt werden.
 */
add_action( 'init', 'kk_uploads_migrate', 1 );

function kk_uploads_migrate() {
    if ( get_option( 'kk_uploads_migrated' ) ) {
        return;
    }
    // Sperre: add_option ist ein INSERT und schlägt bei parallelen Aufrufen fehl
    if ( ! add_option( 'kk_uploads_migrating', time(), '', false ) ) {
        if ( time() - (int) get_option( 'kk_uploads_migrating' ) < 300 ) {
            return;
        }
        update_option( 'kk_uploads_migrating', time(), false ); // hängengebliebene Sperre
    }

    global $wpdb;
    $log    = array();
    $base   = trailingslashit( wp_upload_dir()['basedir'] );
    $target = $base . KK_UPLOADS_FOLDER . '/';
    wp_mkdir_p( $target );

    foreach ( KK_UPLOADS_SUBFOLDERS as $sub ) {
        $from = $base . $sub;
        $to   = $target . $sub;
        if ( ! is_dir( $from ) ) {
            continue;
        }
        if ( ! file_exists( $to ) && @rename( $from, $to ) ) {
            $log[] = "$sub verschoben";
            continue;
        }
        // Zusammenführen: Dateien einzeln, vorhandene Ziele bleiben
        wp_mkdir_p( $to );
        $moved = 0;
        $kept  = 0;
        foreach ( (array) scandir( $from ) as $name ) {
            if ( $name === '.' || $name === '..' || is_dir( "$from/$name" ) ) {
                continue;
            }
            if ( ! file_exists( "$to/$name" ) && @rename( "$from/$name", "$to/$name" ) ) {
                $moved++;
            } else {
                $kept++;
            }
        }
        @rmdir( $from ); // nur wenn leer
        $log[] = "$sub zusammengeführt ($moved verschoben" . ( $kept ? ", $kept blieben im alten Ordner" : '' ) . ')';
    }

    // Pfade in Textspalten
    $replaced = 0;
    $tables   = $wpdb->get_col( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $wpdb->prefix . 'kk_' ) . '%' ) );
    foreach ( $tables as $table ) {
        $columns = $wpdb->get_results( "SHOW COLUMNS FROM `{$table}`", ARRAY_A );
        foreach ( $columns as $col ) {
            if ( ! preg_match( '/char|text/i', $col['Type'] ) ) {
                continue;
            }
            foreach ( KK_UPLOADS_SUBFOLDERS as $sub ) {
                $old = "/uploads/{$sub}/";
                $new = '/uploads/' . KK_UPLOADS_FOLDER . "/{$sub}/";
                $replaced += (int) $wpdb->query( $wpdb->prepare(
                    "UPDATE `{$table}` SET `{$col['Field']}` = REPLACE(`{$col['Field']}`, %s, %s) WHERE `{$col['Field']}` LIKE %s",
                    $old, $new, '%' . $wpdb->esc_like( $old ) . '%'
                ) );
            }
        }
    }
    foreach ( KK_UPLOADS_SUBFOLDERS as $sub ) {
        $old = "/uploads/{$sub}/";
        $new = '/uploads/' . KK_UPLOADS_FOLDER . "/{$sub}/";
        $replaced += (int) $wpdb->query( $wpdb->prepare(
            "UPDATE {$wpdb->posts} SET post_content = REPLACE(post_content, %s, %s) WHERE post_type <> 'revision' AND post_content LIKE %s",
            $old, $new, '%' . $wpdb->esc_like( $old ) . '%'
        ) );
    }
    $log[] = "$replaced Datensätze mit Pfaden angepasst";

    update_option( 'kk_uploads_migrated', wp_date( 'Y-m-d H:i:s' ) . ': ' . implode( '; ', $log ), false );
    delete_option( 'kk_uploads_migrating' );
    error_log( 'kk_uploads_migrate: ' . implode( '; ', $log ) );
}
