<?php
/**
 * Renders the static design export inside the WordPress theme shell.
 */
function kultur_site_design_pages() {
    return array(
        'programm' => 'index.html',
        'spielorte' => 'spielorte.html',
        'vorstand' => 'vorstand.html',
        'vorstand-verspielt' => 'vorstand-verspielt.html',
        'archiv' => 'archiv.html',
        'video' => 'video.html',
        'formular' => 'formular.html',
        'seite' => 'seite.html',
    );
}

function kultur_site_design_url($slug) {
    return get_permalink(get_page_by_path($slug)) ?: home_url('/' . $slug . '/');
}

function kultur_get_board_members() {
    global $wpdb;

    if ( ! isset( $wpdb ) ) {
        return array();
    }

    $table = $wpdb->prefix . 'kk_persons';

    if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
        return array();
    }

    $rows = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY sort_order ASC, id ASC", ARRAY_A );

    return is_array( $rows ) ? $rows : array();
}

function kultur_render_board_member_cards($slug, $rows) {
    if ( empty( $rows ) ) {
        return '';
    }

    $is_verspielt = 'vorstand-verspielt' === $slug;
    $cards = '';

    foreach ( $rows as $row ) {
        $name = esc_html( $row['name'] ?? '' );
        $role = esc_html( $row['role'] ?? '' );
        $quote = esc_html( $row['quote'] ?? '' );
        $image = esc_url( $row['image'] ?? '' );
        $tags = trim( (string) ( $row['tags'] ?? '' ) );

        if ( ! $image ) {
            $image = get_stylesheet_directory_uri() . '/site-design-source/assets/board/default.png';
        }

        $tag_html = '';
        if ( $tags !== '' ) {
            foreach ( preg_split('/\s*\|\s*/', $tags ) as $tag ) {
                $tag = trim( (string) $tag );
                if ( $tag !== '' ) {
                    $tag_html .= '<span class="kk-tag">' . esc_html( $tag ) . '</span>';
                }
            }
        }

        if ( $is_verspielt ) {
            $cards .= '<div class="col-6 col-md-4 col-lg-3 d-flex">
                <div class="kk-card">
                    <div class="kk-card-media">
                        <img class="kk-img kk-img-portrait" src="' . $image . '" alt="' . $name . '">
                        <span class="kk-sticker"></span>
                    </div>
                    <div class="kk-card-body">
                        <div class="kk-card-name">' . $name . '</div>
                        <div class="kk-card-role">' . $role . '</div>
                        <div class="kk-tags">' . $tag_html . '</div>
                        <div class="kk-card-fact">«' . $quote . '»</div>
                        <a class="kk-videolink mt-2" href="' . esc_url( kultur_site_design_url( 'vorstand' ) ) . '">Mehr dazu</a>
                    </div>
                </div>
            </div>';
        } else {
            $cards .= '<div class="col-6 col-md-4 col-lg-3 d-flex">
                <div class="kk-card">
                    <img class="kk-img kk-img-portrait" src="' . $image . '" alt="' . $name . '">
                    <div class="kk-card-body">
                        <div class="kk-card-name">' . $name . '</div>
                        <div class="kk-card-role">' . $role . '</div>
                    </div>
                </div>
            </div>';
        }
    }

    return '<div class="row g-4">' . $cards . '</div>';
}

function kultur_render_site_design($slug) {
    $pages = kultur_site_design_pages();
    $file = isset($pages[$slug]) ? $pages[$slug] : $pages['seite'];
    $path = trailingslashit(get_stylesheet_directory()) . 'site-design-source/' . $file;
    $html = file_get_contents($path);

    if ($html === false) {
        return;
    }

    if (preg_match_all('/<style\\b[^>]*>(.*?)<\\/style>/is', $html, $matches)) {
        echo '<style>' . implode("\n", $matches[1]) . '</style>';
    }

    preg_match('/<body[^>]*>(.*?)<\\/body>/is', $html, $body);
    $content = isset($body[1]) ? $body[1] : $html;
    if ( in_array( $slug, array( 'vorstand', 'vorstand-verspielt' ), true ) ) {
        $members = kultur_get_board_members();
        if ( ! empty( $members ) ) {
            $pattern = '/<div class="row g-4">.*?<\/div>\s*(?=<div class="row g-4 mt-4|<\/main>)/is';
            $content = preg_replace( $pattern, kultur_render_board_member_cards( $slug, $members ), $content, 1 );
        }
    }
    $asset_url = trailingslashit(get_stylesheet_directory_uri()) . 'site-design-source/assets/';
    $content = str_replace('assets/', $asset_url, $content);

    foreach ($pages as $page_slug => $page_file) {
        $content = str_replace(
            array('data-go="' . $page_file . '"', 'href="' . $page_file . '"'),
            array('data-go="' . esc_url(kultur_site_design_url($page_slug)) . '"', 'href="' . esc_url(kultur_site_design_url($page_slug)) . '"'),
            $content
        );
    }

    $content = preg_replace('/<script\\b[^>]*>.*?<\\/script>/is', '', $content);
    echo $content;
}
