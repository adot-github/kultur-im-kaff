<?php
/**
 * Jubiläum-Slots (wp_kk_jub_slots): Daten für den Kalender [kk-jubilaeum-calendar]
 * und AJAX-Anmeldung für einen freien Slot.
 *
 * Wird aus functions.php geladen – auch im Admin, weil admin-ajax.php is_admin() ist.
 */

defined( 'ABSPATH' ) || exit;

const KK_JUB_DATE_FROM = '2027-06-04';
const KK_JUB_DATE_TO   = '2027-08-28';

/**
 * Alle Slots im Jubiläumszeitraum als Termine für bs-calendar.
 * Enthält bewusst keine Personendaten (Name, E-Mail, Telefon) – nur den Titel.
 */
function kk_jub_get_calendar_slots() {
    global $wpdb;

    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT s.id, s.str_slot_name, s.dtm_slot_date, s.dtm_slot_from, s.dtm_slot_to, s.fky_slot_type,
                    s.ysn_slot_booked, s.str_event_club,
                    t.str_slot_type_name, t.str_slot_type_color
             FROM {$wpdb->prefix}kk_jub_slots s
             LEFT JOIN {$wpdb->prefix}kk_jub_slot_types t ON t.id = s.fky_slot_type
             WHERE DATE(s.dtm_slot_date) BETWEEN %s AND %s
             ORDER BY s.dtm_slot_date ASC, s.dtm_slot_from ASC",
            KK_JUB_DATE_FROM,
            KK_JUB_DATE_TO
        ),
        ARRAY_A
    );

    return array_map( 'kk_jub_slot_to_appointment', $rows ?: array() );
}

/**
 * ysn_slot_booked: 0 = frei, 1 = reserviert (Anmeldung eingegangen), 2 = gebucht (Verein/Gruppe/Künstler:in wird angezeigt).
 */
function kk_jub_slot_to_appointment( $row ) {
    $date  = substr( (string) $row['dtm_slot_date'], 0, 10 );
    $state = min( 2, max( 0, (int) $row['ysn_slot_booked'] ) );
    $type  = trim( (string) ( $row['str_slot_type_name'] ?? '' ) );
    $color = ltrim( trim( (string) ( $row['str_slot_type_color'] ?? '' ) ), '#' );
    $club  = trim( (string) ( $row['str_event_club'] ?? '' ) );

    if ( $state === 0 ) {
        $label = ( $type !== '' ? $type : 'Slot' ) . ' · frei';
    } elseif ( $state === 1 ) {
        $label = 'reserviert';
    } else {
        $label = $club !== '' ? $club : 'gebucht';
    }

    return array(
        'id'       => (int) $row['id'],
        'title'    => $label,
        'start'    => $date . ' ' . $row['dtm_slot_from'],
        'end'      => $date . ' ' . $row['dtm_slot_to'],
        'color'    => preg_match( '/^[0-9a-f]{6}$/i', $color ) ? '#' . $color : 'primary',
        'kkType'   => $type,
        'kkName'   => trim( (string) ( $row['str_slot_name'] ?? '' ) ),
        'kkState'  => $state,
        'kkBooked' => $state !== 0,
    );
}

/**
 * AJAX: Anmeldung für einen Slot. Setzt ysn_slot_booked atomar, damit ein Slot
 * nicht doppelt vergeben werden kann.
 */
add_action( 'wp_ajax_kk_jub_register', 'kk_jub_ajax_register' );
add_action( 'wp_ajax_nopriv_kk_jub_register', 'kk_jub_ajax_register' );

function kk_jub_ajax_register() {
    global $wpdb;
    $table = $wpdb->prefix . 'kk_jub_slots';

    if ( ! check_ajax_referer( 'kk_jub_register', 'nonce', false ) ) {
        wp_send_json_error( array( 'message' => 'Die Sitzung ist abgelaufen. Bitte lade die Seite neu und versuche es nochmals.' ), 403 );
    }

    // Honeypot: von Menschen unsichtbar, Bots füllen es aus
    if ( ! empty( $_POST['kk_website'] ) ) {
        wp_send_json_error( array( 'message' => 'Die Anmeldung konnte nicht gespeichert werden.' ), 400 );
    }

    $slot_id = absint( $_POST['slot_id'] ?? 0 );
    $slot    = $slot_id ? $wpdb->get_row( $wpdb->prepare( "SELECT id, dtm_slot_date, ysn_slot_booked FROM {$table} WHERE id = %d", $slot_id ), ARRAY_A ) : null;
    $date    = $slot ? substr( (string) $slot['dtm_slot_date'], 0, 10 ) : '';

    if ( ! $slot || $date < KK_JUB_DATE_FROM || $date > KK_JUB_DATE_TO || $date < current_time( 'Y-m-d' ) ) {
        wp_send_json_error( array( 'message' => 'Dieser Slot ist nicht verfügbar.' ), 404 );
    }
    if ( (int) $slot['ysn_slot_booked'] !== 0 ) {
        wp_send_json_error( array( 'message' => 'Dieser Slot wurde soeben vergeben. Bitte wähle einen anderen.', 'booked' => true ), 409 );
    }

    $text = function ( $key ) {
        return mb_substr( sanitize_text_field( wp_unslash( $_POST[ $key ] ?? '' ) ), 0, 255 );
    };
    $longtext = function ( $key ) {
        return sanitize_textarea_field( wp_unslash( $_POST[ $key ] ?? '' ) );
    };
    $data = array(
        'str_event_title'       => $text( 'title' ),
        'txt_event_description' => $longtext( 'description' ),
        'str_event_club'        => $text( 'club' ),
        'txt_event_club_text'   => $longtext( 'club_text' ),
        'str_event_url'         => kk_jub_normalize_url( wp_unslash( $_POST['url'] ?? '' ) ),
        'str_event_first_name'  => $text( 'first_name' ),
        'str_event_last_name'   => $text( 'last_name' ),
        'str_event_phone'       => $text( 'phone' ),
        'str_event_email'       => sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ),
    );

    $errors = array();
    foreach ( array(
        'str_event_title'       => 'Titel des Beitrages',
        'txt_event_description' => 'Beschreibung des Beitrages',
        'str_event_first_name'  => 'Vorname',
        'str_event_last_name'   => 'Nachname',
        'str_event_phone'       => 'Telefon',
    ) as $col => $label ) {
        if ( $data[ $col ] === '' ) {
            $errors[] = $label . ' fehlt.';
        }
    }
    if ( ! is_email( $data['str_event_email'] ) ) {
        $errors[] = 'Bitte gib eine gültige E-Mail-Adresse an.';
    }
    if ( trim( (string) ( $_POST['url'] ?? '' ) ) !== '' && $data['str_event_url'] === '' ) {
        $errors[] = 'Bitte gib eine gültige Webadresse an (mit https://).';
    }

    if ( $errors ) {
        wp_send_json_error( array( 'message' => implode( ' ', $errors ) ), 422 );
    }

    // Atomar belegen: greift nur, wenn der Slot in diesem Moment noch frei ist
    $set    = array();
    $values = array();
    foreach ( $data as $col => $value ) {
        $set[]    = "{$col} = %s";
        $values[] = $value;
    }
    $values[] = $slot_id;
    $claimed  = $wpdb->query(
        $wpdb->prepare(
            "UPDATE {$table} SET ysn_slot_booked = 1, dtm_date_updated = NOW(), " . implode( ', ', $set ) . ' WHERE id = %d AND ysn_slot_booked = 0',
            $values
        )
    );

    if ( $claimed === false ) {
        wp_send_json_error( array( 'message' => 'Die Anmeldung konnte nicht gespeichert werden. Bitte versuche es später nochmals.' ), 500 );
    }
    if ( $claimed !== 1 ) {
        wp_send_json_error( array( 'message' => 'Dieser Slot wurde soeben vergeben. Bitte wähle einen anderen.', 'booked' => true ), 409 );
    }

    $row = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT s.id, s.str_slot_name, s.dtm_slot_date, s.dtm_slot_from, s.dtm_slot_to, s.fky_slot_type, s.ysn_slot_booked, s.str_event_club,
                    t.str_slot_type_name, t.str_slot_type_color
             FROM {$table} s LEFT JOIN {$wpdb->prefix}kk_jub_slot_types t ON t.id = s.fky_slot_type
             WHERE s.id = %d",
            $slot_id
        ),
        ARRAY_A
    );

    wp_send_json_success(
        array(
            'message'     => 'Danke! Deine Anmeldung ist eingegangen.',
            'appointment' => kk_jub_slot_to_appointment( $row ),
        )
    );
}

/**
 * Webadresse aus dem Formular: "www.band.ch" wird zu "https://www.band.ch".
 * Leer bei leerer oder ungültiger Eingabe.
 */
function kk_jub_normalize_url( $url ) {
    $url = trim( (string) $url );
    if ( $url === '' ) {
        return '';
    }
    if ( ! preg_match( '#^https?://#i', $url ) ) {
        $url = 'https://' . ltrim( $url, '/' );
    }
    $url = esc_url_raw( $url, array( 'http', 'https' ) );
    $host = (string) wp_parse_url( $url, PHP_URL_HOST );
    return ( strlen( $url ) <= 255 && filter_var( $url, FILTER_VALIDATE_URL ) && strpos( $host, '.' ) !== false ) ? $url : '';
}
