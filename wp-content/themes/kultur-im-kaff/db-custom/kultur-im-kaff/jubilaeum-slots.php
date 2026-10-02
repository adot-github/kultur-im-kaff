<?php
/**
 * Jubiläum-Slots (wp_kk_jub_slots): Daten für den Kalender [kk-jubilaeum-calendar]
 * und AJAX-Anmeldung für einen freien Slot.
 *
 * Wird aus functions.php geladen – auch im Admin, weil admin-ajax.php is_admin() ist.
 */

defined( 'ABSPATH' ) || exit;

// Zeitraum der Slots (Do/Fr, siehe sql/kk_jub_slots_seed.sql): Navigation im Kalender und Buchungsprüfung
const KK_JUB_DATE_FROM = '2027-06-17';
const KK_JUB_DATE_TO   = '2027-08-27';

// Open Stage (wp_kk_jub_slot_types.id): Dauer des Beitrags wählbar, sie bestimmt beim Speichern die Endzeit
const KK_JUB_TYPE_OPEN_STAGE = 1;
const KK_JUB_DURATIONS       = array( 30, 45, 60, 75, 90 ); // Minuten

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
        // Open Stage: Formular ohne Zeit im Titel, dafür mit "Dauer des Beitrags"
        'kkOpenStage' => (int) ( $row['fky_slot_type'] ?? 0 ) === KK_JUB_TYPE_OPEN_STAGE,
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
    $slot    = $slot_id ? $wpdb->get_row( $wpdb->prepare( "SELECT id, dtm_slot_date, dtm_slot_from, fky_slot_type, ysn_slot_booked FROM {$table} WHERE id = %d", $slot_id ), ARRAY_A ) : null;
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

    // Open Stage: gewählte Dauer bestimmt die Endzeit (überschreibt dtm_slot_to)
    $duration = 0;
    if ( (int) $slot['fky_slot_type'] === KK_JUB_TYPE_OPEN_STAGE ) {
        $duration = absint( $_POST['duration'] ?? 0 );
        if ( ! in_array( $duration, KK_JUB_DURATIONS, true ) ) {
            $errors[] = 'Bitte wähle die Dauer des Beitrags.';
        } else {
            $data['dtm_slot_to'] = gmdate( 'H:i:s', strtotime( '1970-01-01 ' . $slot['dtm_slot_from'] . ' UTC' ) + $duration * 60 );
        }
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

    kk_jub_send_registration_mail( $row, $data, $duration );

    wp_send_json_success(
        array(
            'message'     => 'Danke! Deine Anmeldung ist eingegangen.',
            'appointment' => kk_jub_slot_to_appointment( $row ),
        )
    );
}

/**
 * Bestätigungsmail nach der Anmeldung: an die anmeldende Person und an KK_JUB_MAIL_TO,
 * Bcc an KK_JUB_MAIL_BCC. Alle Angaben als Tabelle, oben links das blaue Logo.
 * Ein Fehler beim Versand macht die Anmeldung nicht rückgängig, er wird nur geloggt.
 */
const KK_JUB_MAIL_TO  = 'e.notter@adot.ch';
const KK_JUB_MAIL_BCC = 'eugen.notter@fhnw.ch';

function kk_jub_send_registration_mail( array $slot, array $data, $duration = 0 ) {
    $date = strtotime( substr( (string) $slot['dtm_slot_date'], 0, 10 ) );
    $when = date_i18n( 'l, j. F Y', $date ) . ', ' . substr( $slot['dtm_slot_from'], 0, 5 ) . '–' . substr( $slot['dtm_slot_to'], 0, 5 ) . ' Uhr';
    $type = trim( (string) ( $slot['str_slot_type_name'] ?? '' ) );

    $url  = $data['str_event_url'];
    $rows = array(
        'Slot'                                  => esc_html( $type !== '' ? $type : (string) $slot['str_slot_name'] ),
        'Datum/Zeit'                            => esc_html( $when ),
        'Dauer des Beitrags'                    => $duration ? $duration . ' Minuten' : null, // nur Open Stage
        'Titel des Beitrages'                   => esc_html( $data['str_event_title'] ),
        'Beschreibung des Beitrages'            => nl2br( esc_html( $data['txt_event_description'] ) ),
        'Verein/Gruppe/Künstler:in'             => esc_html( $data['str_event_club'] ),
        'Beschreibung Verein/Gruppe/Künstler:in' => nl2br( esc_html( $data['txt_event_club_text'] ) ),
        'Vorname'                               => esc_html( $data['str_event_first_name'] ),
        'Nachname'                              => esc_html( $data['str_event_last_name'] ),
        'E-Mail'                                => esc_html( $data['str_event_email'] ),
        'Telefon'                               => esc_html( $data['str_event_phone'] ),
        'Website/Link'                          => $url !== '' ? '<a href="' . esc_url( $url ) . '" style="color:#562BFF;">' . esc_html( $url ) . '</a>' : '',
    );

    // Inline-Styles: viele Mailprogramme ignorieren <style>-Blöcke
    $th_style = 'padding:10px 16px 10px 0;border-bottom:1px solid #e3def0;font-weight:600;color:#5B3E7E;white-space:nowrap;width:1%;';
    $td_style = 'padding:10px 0;border-bottom:1px solid #e3def0;color:#160234;';

    $rows  = array_filter( $rows, fn( $value ) => $value !== null );
    $table = '';
    foreach ( $rows as $label => $value ) {
        $label  = esc_html( $label );
        $value  = $value !== '' ? $value : '–';
        $table .= <<<HTML
                <tr>
                  <th align="left" valign="top" style="{$th_style}">{$label}</th>
                  <td valign="top" style="{$td_style}">{$value}</td>
                </tr>

HTML;
    }

    $name     = trim( $data['str_event_first_name'] );
    $greeting = $name !== '' ? 'Hallo ' . esc_html( $name ) . ',<br>' : '';

    $body = <<<HTML
<!doctype html>
<html lang="de">
<head><meta charset="utf-8"></head>
<body style="margin:0;padding:0;background:#f4f2fa;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f2fa;">
    <tr><td align="center" style="padding:24px 12px;">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:640px;background:#ffffff;font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:1.5;color:#160234;">
        <tr>
          <td style="padding:24px 28px 8px;">
            <img src="cid:kk-logo" width="140" alt="Kultur im Kaff" style="display:block;border:0;width:140px;height:auto;">
          </td>
        </tr>
        <tr>
          <td style="padding:8px 28px 0;">
            <h1 style="margin:0 0 12px;font-size:22px;color:#160234;">Anmeldung Jubiläum</h1>
            <p style="margin:0 0 20px;">
              {$greeting}
              vielen Dank für deine Anmeldung. Der Slot ist für dich reserviert, wir melden uns bei dir. Hier deine Angaben:
            </p>
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;border-top:2px solid #160234;">
{$table}            </table>
          </td>
        </tr>
        <tr>
          <td style="padding:24px 28px 28px;font-size:13px;color:#5B3E7E;">Kultur im Kaff</td>
        </tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;

    $subject = 'Anmeldung Jubiläum: ' . ( $type !== '' ? $type . ', ' : '' ) . date_i18n( 'j. F Y', $date );
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'Bcc: ' . KK_JUB_MAIL_BCC,
    );

    // Logo als eingebettetes Bild (cid:kk-logo) – wird auch angezeigt, wenn externe Bilder blockiert sind
    $logo  = get_stylesheet_directory() . '/img/kik-blau.png';
    $embed = function ( $phpmailer ) use ( $logo ) {
        if ( is_readable( $logo ) ) {
            $phpmailer->addEmbeddedImage( $logo, 'kk-logo', 'kultur-im-kaff.png', 'base64', 'image/png' );
        }
    };
    add_action( 'phpmailer_init', $embed );
    $sent = wp_mail( array( $data['str_event_email'], KK_JUB_MAIL_TO ), $subject, $body, $headers );
    remove_action( 'phpmailer_init', $embed );

    if ( ! $sent ) {
        error_log( 'kk_jub: Bestätigungsmail für Slot ' . (int) $slot['id'] . ' konnte nicht versendet werden.' );
    }
    return $sent;
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
