<?php
/**
 * Cache-Busting für Medien aus /wp-content/uploads/.
 *
 * Wird ein Bild unter gleichem Namen ersetzt (z. B. events/braente.jpg), zeigen Browser – vor allem
 * Safari auf dem Handy – oft noch die alte Version aus dem Cache. Deshalb bekommt jede Upload-Adresse
 * im ausgelieferten HTML die Änderungszeit der Datei angehängt: braente.jpg?v=1760012345.
 * Neue Datei = neue Adresse = wird neu geladen; unveränderte Dateien bleiben im Cache.
 *
 * Zentral über einen Ausgabepuffer, damit alle Templates (Anlässe, News, Spielorte, Sponsoren, Vorstand …)
 * und Bilder in Editor-Texten erfasst werden, ohne jede Stelle einzeln anzupassen.
 * Betroffen: src, srcset, href, data-*, style="…url(…)" – nur im Frontend, nur existierende Dateien,
 * nur Adressen ohne eigenen Query-String.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'template_redirect', function () {
    if ( is_admin() || is_feed() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
        return;
    }
    ob_start( 'kk_media_version_html' );
}, 0 );

/** Hängt ?v=<filemtime> an alle Upload-Adressen im HTML */
function kk_media_version_html( $html ) {
    if ( $html === '' || stripos( $html, '/wp-content/uploads/' ) === false ) {
        return $html;
    }
    $uploads = wp_get_upload_dir();
    $basedir = wp_normalize_path( $uploads['basedir'] );
    $baseurl = (string) wp_parse_url( $uploads['baseurl'], PHP_URL_PATH ); // z. B. /wp-content/uploads
    if ( $baseurl === '' ) {
        return $html;
    }

    static $mtimes = array();
    // (absolute oder relative) Adresse unter /wp-content/uploads/ mit Medien-Endung, ohne folgenden Query-String
    $pattern = '~((?:https?:)?(?://[^/\s"\'()<>]+)?' . preg_quote( $baseurl, '~' ) . '/[^\s"\'()<>?#]+?\.(?:jpe?g|png|gif|webp|avif|svg|mp4|webm|m4v|ogg|pdf))(?![?\w])~i';

    $result = preg_replace_callback( $pattern, function ( $m ) use ( $basedir, $baseurl, &$mtimes ) {
        $url  = $m[1];
        $path = (string) wp_parse_url( $url, PHP_URL_PATH );
        $host = (string) wp_parse_url( $url, PHP_URL_HOST );
        if ( $host !== '' && strcasecmp( $host, (string) wp_parse_url( home_url(), PHP_URL_HOST ) ) !== 0 ) {
            return $url; // fremde Domain
        }
        $file = $basedir . substr( rawurldecode( $path ), strlen( $baseurl ) );
        if ( strpos( $file, '..' ) !== false ) {
            return $url;
        }
        if ( ! array_key_exists( $file, $mtimes ) ) {
            $mtimes[ $file ] = is_file( $file ) ? (int) filemtime( $file ) : 0;
        }
        return $mtimes[ $file ] ? $url . '?v=' . $mtimes[ $file ] : $url;
    }, $html );

    // Bei einem Regex-Fehler nie eine leere Seite ausliefern
    return is_string( $result ) ? $result : $html;
}
