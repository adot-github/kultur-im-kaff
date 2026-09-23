<?php
global $wpdb;

$event_table = $wpdb->prefix . 'kk_events';
$loc_table   = $wpdb->prefix . 'kk_event_locations';

$locations = $wpdb->get_results(
    "SELECT l.*,
            COUNT(e.id) AS event_count
     FROM {$loc_table} l
     LEFT JOIN {$event_table} e ON e.fky_location = l.id
     GROUP BY l.id
     ORDER BY l.str_location ASC, l.id ASC",
    ARRAY_A
);

$events = $wpdb->get_results(
    "SELECT e.*, l.str_location AS location_name, l.str_city AS location_city, l.str_address AS location_address
     FROM {$event_table} e
     LEFT JOIN {$loc_table} l ON l.id = e.fky_location
     WHERE e.dtm_date_from IS NOT NULL
     ORDER BY e.dtm_date_from ASC, e.id ASC",
    ARRAY_A
);

if ( empty( $locations ) ) {
    echo '<div class="container-xl"><h1>Spielorte</h1><p>Keine Einträge vorhanden.</p></div>';
    return;
}

$format_date = static function ( $from, $to = null ) {
    if ( empty( $from ) ) {
        return '';
    }

    $weekday = date_i18n( 'l', strtotime( $from ) );
    $from_value = date_i18n( 'j. F Y', strtotime( $from ) );
    $to_value = $to ? date_i18n( 'j. F Y', strtotime( $to ) ) : '';

    if ( $to_value !== '' && $from_value !== $to_value ) {
        return $weekday . ', ' . $from_value . ' – ' . $to_value;
    }

    return $weekday . ', ' . $from_value;
};

$normalize_text = static function ( $value ) {
    return trim( (string) ( $value ?? '' ) );
};

$location_image_base = trailingslashit( wp_upload_dir()['baseurl'] ) . 'spielorte/';
$resolve_image = static function ( $value ) use ( $location_image_base ) {
    $value = trim( (string) ( $value ?? '' ) );
    if ( $value === '' ) {
        return '';
    }

    if ( preg_match( '#^(https?:)?//#i', $value ) || str_starts_with( $value, '/' ) ) {
        return $value;
    }

    if ( str_starts_with( $value, 'wp-content/' ) ) {
        return home_url( '/' ) . $value;
    }

    if ( str_starts_with( $value, 'uploads/' ) ) {
        return home_url( '/wp-content/' ) . $value;
    }

    return $location_image_base . ltrim( $value, '/' );
};

/**
 * Baut aus einem Google-Maps-Link (mit Koordinaten) eine einbettbare
 * OpenStreetMap-Kartenausschnitt-URL. Ohne Koordinaten: leerer String.
 */
$map_embed_from_link = static function ( $value ) {
    $value = trim( (string) ( $value ?? '' ) );
    if ( $value === '' ) {
        return '';
    }

    $lat = null;
    $lng = null;
    if ( preg_match( '/!3d(-?\d+(?:\.\d+)?)!4d(-?\d+(?:\.\d+)?)/', $value, $m ) ) {
        $lat = (float) $m[1];
        $lng = (float) $m[2];
    } elseif ( preg_match( '/[@?&](?:ll|q|center)=(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)/', $value, $m ) ) {
        $lat = (float) $m[1];
        $lng = (float) $m[2];
    } elseif ( preg_match( '/@(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)/', $value, $m ) ) {
        $lat = (float) $m[1];
        $lng = (float) $m[2];
    }

    if ( $lat === null || $lng === null ) {
        return '';
    }

    $d_lng = 0.006;
    $d_lat = 0.004;
    $bbox = implode(
        ',',
        array(
            round( $lng - $d_lng, 6 ),
            round( $lat - $d_lat, 6 ),
            round( $lng + $d_lng, 6 ),
            round( $lat + $d_lat, 6 ),
        )
    );

    return 'https://www.openstreetmap.org/export/embed.html?bbox=' . rawurlencode( $bbox )
        . '&layer=mapnik&marker=' . rawurlencode( $lat . ',' . $lng );
};

$events_by_location = array();
foreach ( $events as $event ) {
    $location_id = (int) ( $event['fky_location'] ?? 0 );
    if ( $location_id <= 0 ) {
        continue;
    }

    if ( ! isset( $events_by_location[ $location_id ] ) ) {
        $events_by_location[ $location_id ] = array();
    }

    $events_by_location[ $location_id ][] = $event;
}

$events_page = get_page_by_path( 'programm' );
$events_page_url = $events_page ? get_permalink( $events_page ) : home_url( '/' );

$venue_items = array();
$used_anchors = array();
foreach ( $locations as $location ) {
    $location_id = (int) ( $location['id'] ?? 0 );
    $name        = $normalize_text( $location['str_location'] ?? '' );
    $description = $normalize_text( $location['mem_description'] ?? '' );
    $street      = $normalize_text( $location['str_address'] ?? '' );
    $zip         = $normalize_text( $location['str_zip'] ?? '' );
    $city        = $normalize_text( $location['str_city'] ?? '' );
    $travel      = $normalize_text( $location['str_travel'] ?? '' );
    $map_url     = $normalize_text( $location['str_google'] ?? '' );
    $map_embed   = $map_embed_from_link( $map_url );

    $address_parts = array_filter(
        array(
            $street,
            trim( $zip . ' ' . $city ),
        ),
        static function ( $part ) {
            return $part !== '';
        }
    );
    $address = implode( ', ', $address_parts );

    $anchor_base = sanitize_title( $name !== '' ? $name : 'spielort-' . $location_id );
    if ( $anchor_base === '' ) {
        $anchor_base = 'spielort-' . $location_id;
    }
    $anchor = $anchor_base;
    $anchor_suffix = 2;
    while ( in_array( $anchor, $used_anchors, true ) ) {
        $anchor = $anchor_base . '-' . $anchor_suffix;
        $anchor_suffix++;
    }
    $used_anchors[] = $anchor;

    $slides = array();
    foreach ( array( 'str_image_1', 'str_image_2', 'str_image_3' ) as $image_key ) {
        $src = $resolve_image( $location[ $image_key ] ?? '' );
        if ( $src !== '' ) {
            $slides[] = $src;
        }
    }

    $location_events = $events_by_location[ $location_id ] ?? array();
    usort(
        $location_events,
        static function ( $a, $b ) {
            $left = strtotime( (string) ( $a['dtm_date_from'] ?? '1970-01-01' ) );
            $right = strtotime( (string) ( $b['dtm_date_from'] ?? '1970-01-01' ) );
            return $left <=> $right;
        }
    );

    $agenda = array();
    foreach ( $location_events as $event ) {
        $event_title = $normalize_text( $event['str_title'] ?? '' );
        if ( $event_title === '' ) {
            continue;
        }
        $date_label = $format_date( $event['dtm_date_from'] ?? '', $event['dtm_date_to'] ?? '' );
        $event_id = (int) ( $event['id'] ?? 0 );
        $agenda[] = array(
            'date' => $date_label,
            'title' => $event_title,
            'url' => $event_id > 0 ? $events_page_url . '#event-' . $event_id : '',
        );
    }

    $count = count( $agenda );
    $badge = ( $count > 1 )
        ? $count . ' Anlässe in dieser Saison'
        : ( $count === 1 ? '1 Anlass in dieser Saison' : 'Kein Anlass in dieser Saison' );

    $venue_items[] = array(
        'anchor'      => $anchor,
        'title'       => $name !== '' ? $name : 'Spielort',
        'description' => $description,
        'address'     => $address,
        'map_url'     => $map_url,
        'map_embed'   => $map_embed,
        'travel'      => $travel,
        'slides'      => $slides,
        'badge'       => $badge,
        'agenda'      => $agenda,
    );
}

ob_start();
?>
<div class="container-xl kk-pagehead pb-5">
    <?php foreach ( $venue_items as $venue ) : ?>
        <section id="<?php echo esc_attr( $venue['anchor'] ); ?>" class="row g-4 kk-venue align-items-start">
            <div class="col-12 col-lg-5">
                <div class="kk-carousel" data-carousel>
                    <?php if ( ! empty( $venue['slides'] ) ) : ?>
                        <?php foreach ( $venue['slides'] as $index => $slide_src ) : ?>
                            <div class="kk-slide<?php echo 0 === $index ? ' active' : ''; ?>" data-slide>
                                <img src="<?php echo esc_url( $slide_src ); ?>" alt="<?php echo esc_attr( $venue['title'] ); ?>" loading="lazy">
                            </div>
                        <?php endforeach; ?>
                        <?php if ( count( $venue['slides'] ) > 1 ) : ?>
                            <button class="kk-carbtn prev" type="button" data-car="prev" aria-label="Vorheriges Foto"><svg viewBox="0 0 12 20" width="11" height="18" aria-hidden="true"><polygon points="11,0 11,20 0,10" fill="currentColor"></polygon></svg></button>
                            <button class="kk-carbtn next" type="button" data-car="next" aria-label="Nächstes Foto"><svg viewBox="0 0 12 20" width="11" height="18" aria-hidden="true"><polygon points="1,0 1,20 12,10" fill="currentColor"></polygon></svg></button>
                            <div class="kk-dots">
                                <?php foreach ( $venue['slides'] as $index => $slide_src ) : ?>
                                    <button class="kk-dot<?php echo 0 === $index ? ' active' : ''; ?>" type="button" data-dot aria-label="Foto <?php echo esc_attr( $index + 1 ); ?>"></button>
                                <?php endforeach; ?>
                            </div>
                            <div class="kk-counter" data-counter>1 / <?php echo count( $venue['slides'] ); ?></div>
                        <?php endif; ?>
                    <?php else : ?>
                        <div class="kk-ph kk-slide active" data-slide><?php echo esc_html( $venue['title'] ); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <h2 class="kk-h2"><?php echo esc_html( $venue['title'] ); ?></h2>
                <?php if ( $venue['description'] !== '' ) : ?>
                    <p class="kk-text kk-text-narrow mt-3 mb-0"><?php echo esc_html( $venue['description'] ); ?></p>
                <?php endif; ?>
                <div class="kk-venue-badge"><?php echo esc_html( $venue['badge'] ); ?></div>
                <?php if ( ! empty( $venue['agenda'] ) ) : ?>
                    <div class="kk-agenda mt-3">
                        <?php foreach ( $venue['agenda'] as $item ) : ?>
                            <?php if ( $item['url'] !== '' ) : ?>
                                <a class="kk-agenda-item" href="<?php echo esc_url( $item['url'] ); ?>"><span class="kk-agenda-date"><?php echo esc_html( $item['date'] ); ?></span><br><?php echo esc_html( $item['title'] ); ?></a>
                            <?php else : ?>
                                <div class="kk-agenda-item"><span class="kk-agenda-date"><?php echo esc_html( $item['date'] ); ?></span><br><?php echo esc_html( $item['title'] ); ?></div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-12 col-lg-3">
                <?php if ( $venue['address'] !== '' ) : ?>
                    <div class="kk-label">Adresse</div>
                    <div class="kk-small mt-1"><?php echo esc_html( $venue['address'] ); ?></div>
                <?php endif; ?>
                <?php if ( $venue['map_embed'] !== '' ) : ?>
                    <div class="kk-map mt-3">
                        <iframe src="<?php echo esc_url( $venue['map_embed'] ); ?>" title="<?php echo esc_attr( 'Karte: ' . $venue['title'] ); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        <?php if ( $venue['map_url'] !== '' ) : ?>
                            <a href="<?php echo esc_url( $venue['map_url'] ); ?>" target="_blank" rel="noopener noreferrer">Grössere Karte</a>
                        <?php endif; ?>
                    </div>
                <?php elseif ( $venue['map_url'] !== '' ) : ?>
                    <a class="kk-ph kk-img mt-3" href="<?php echo esc_url( $venue['map_url'] ); ?>" target="_blank" rel="noopener noreferrer">KARTE ÖFFNEN</a>
                <?php else : ?>
                    <div class="kk-ph kk-img mt-3">KARTENAUSSCHNITT</div>
                <?php endif; ?>

                <?php if ( $venue['travel'] !== '' ) : ?>
                    <div class="kk-label mt-3">Anreise</div>
                    <div class="kk-small mt-1"><?php echo esc_html( $venue['travel'] ); ?></div>
                <?php endif; ?>
            </div>
        </section>
    <?php endforeach; ?>
</div>
<script>
(function () {
    function initCarousels() {
        document.querySelectorAll('[data-carousel]').forEach(function (car) {
            if (car.dataset.carouselReady === '1') {
                return;
            }
            car.dataset.carouselReady = '1';

            var slides = car.querySelectorAll('[data-slide]');
            if (slides.length < 2) {
                return;
            }

            var dots = car.querySelectorAll('[data-dot]');
            var counter = car.querySelector('[data-counter]');
            var index = 0;

            function show(next) {
                index = (next + slides.length) % slides.length;
                slides.forEach(function (slide, i) { slide.classList.toggle('active', i === index); });
                dots.forEach(function (dot, i) { dot.classList.toggle('active', i === index); });
                if (counter) {
                    counter.textContent = (index + 1) + ' / ' + slides.length;
                }
            }

            var prev = car.querySelector('[data-car="prev"]');
            var nextBtn = car.querySelector('[data-car="next"]');
            if (prev) { prev.addEventListener('click', function () { show(index - 1); }); }
            if (nextBtn) { nextBtn.addEventListener('click', function () { show(index + 1); }); }
            dots.forEach(function (dot, i) { dot.addEventListener('click', function () { show(i); }); });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCarousels);
    } else {
        initCarousels();
    }
})();
</script>
<?php
$output = ob_get_clean();
echo $output;
