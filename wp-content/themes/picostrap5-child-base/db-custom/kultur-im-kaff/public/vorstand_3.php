<?php

global $wpdb;

$table = $wpdb->prefix . 'kk_persons';
$query = "SELECT * FROM {$table} ORDER BY int_sort_order ASC, id ASC";
$rows = $wpdb->get_results( $query, ARRAY_A );

if ( empty( $rows ) ) {
    echo <<<'HTML'
<div class="container-xl"><h1>Vorstand</h1><p>Keine Einträge vorhanden.</p></div>
HTML;
    return;
}

$cards = [];

foreach ( $rows as $person ) {
    $first = trim( (string) ( $person['str_firstname'] ?? '' ) );
    $last  = trim( (string) ( $person['str_lastname'] ?? '' ) );
    $name  = trim( $first . ' ' . $last );
    $role  = trim( (string) ( $person['str_role'] ?? '' ) );
    $email = trim( (string) ( $person['str_email'] ?? '' ) );
    $tags  = trim( (string) ( $person['str_tags'] ?? '' ) );
    $claim = trim( (string) ( $person['str_claim'] ?? '' ) );
    $image = trim( (string) ( $person['str_image'] ?? '' ) );

    if ( $image !== '' ) {
        if ( ! preg_match( '#^(https?:)?//#', $image ) && ! preg_match( '#^/#', $image ) ) {
            $upload_dir = wp_upload_dir();
            $base_url   = trailingslashit( $upload_dir['baseurl'] );
            $base_path  = trailingslashit( $upload_dir['basedir'] );
            $file_name  = basename( $image );
            $candidates = array(
                'vorstand/' . $file_name,
                'vostand/' . $file_name,
            );

            $resolved = false;
            foreach ( $candidates as $candidate ) {
                if ( file_exists( $base_path . $candidate ) ) {
                    $image = $base_url . $candidate;
                    $resolved = true;
                    break;
                }
            }

            if ( ! $resolved ) {
                $image = $base_url . 'vorstand/' . ltrim( $image, '/' );
            }
        }
    } else {
        $upload_dir = wp_upload_dir();
        $image = trailingslashit( $upload_dir['baseurl'] ) . 'vorstand/default.jpg';
    }

    $role_html = ( $role !== '' ) ? '<div class="kk-card-role">' . esc_html( $role ) . '</div>' : '';
    $email_html = ( $email !== '' ) ? '<div class="kk-card-email"><a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a></div>' : '';

    $tags_html = '';
    if ( $tags !== '' ) {
        $tag_items = [];
        foreach ( preg_split( '/\s*\|\s*/', $tags ) as $tag ) {
            $tag = trim( (string) $tag );
            if ( $tag !== '' ) {
                $tag_items[] = '<span class="kk-tag">' . esc_html( $tag ) . '</span>';
            }
        }
        if ( ! empty( $tag_items ) ) {
            $tags_html = '<div class="kk-tags mt-2">' . implode( '', $tag_items ) . '</div>';
        }
    }

    $claim_html = ( $claim !== '' ) ? '<div class="kk-card-fact mt-2">«' . esc_html( $claim ) . '»</div>' : '';

    $cards[] = array(
        'image' => esc_url( $image ),
        'name'  => esc_html( $name ),
        'role'  => $role_html,
        'email' => $email_html,
        'tags'  => $tags_html,
        'claim' => $claim_html,
    );
}

$output = '';
foreach ( $cards as $card ) {
    $output .= <<<HTML
<div class="col-12 col-sm-6 col-md-4 col-lg-3 d-flex">
    <div class="kk-card w-100">
        <img class="kk-img kk-img-portrait" src="{$card['image']}" alt="{$card['name']}">
        <div class="kk-card-body">
            <div class="kk-card-name">{$card['name']}</div>
            {$card['role']}
            {$card['email']}
            {$card['tags']}
            {$card['claim']}
        </div>
    </div>
</div>
HTML;
}

echo '<div class="row g-4">' . $output . '</div>';




