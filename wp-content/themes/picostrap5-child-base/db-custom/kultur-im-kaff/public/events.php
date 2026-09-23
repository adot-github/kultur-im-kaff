<?php
global $wpdb;

// [events] wird per Shortcode mit einem "archiv"-Attribut aufgerufen:
// [events archiv=0] -> Startseite: nur kommende Anlässe (inkl. 10 Tage Kulanz in die Vergangenheit)
// [events archiv=1] -> /archiv/: nur vergangene Anlässe, ohne "Vorverkauf"-Button
$opts = shortcode_atts(
    array( 'archiv' => '0' ),
    is_array( $attributes ?? null ) ? $attributes : array()
);
$is_archiv = (string) $opts['archiv'] === '1';
$date_cutoff = date_i18n( 'Y-m-d', strtotime( '-5 days' ) );

$event_table = $wpdb->prefix . 'kk_events';
$tag_table   = $wpdb->prefix . 'kk_event_tags';
$join_table  = $wpdb->prefix . 'kk_event_to_tags';
$loc_table   = $wpdb->prefix . 'kk_event_locations';

$events = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT e.*, l.str_location AS location_name, l.str_city AS location_city
         FROM {$event_table} e
         LEFT JOIN {$loc_table} l ON l.id = e.fky_location
         WHERE e.dtm_date_from " . ( $is_archiv ? '<=' : '>' ) . ' %s
         ORDER BY e.dtm_date_from ' . ( $is_archiv ? 'DESC' : 'ASC' ) . ', e.id ASC',
        $date_cutoff
    ),
    ARRAY_A
);

if ( empty( $events ) ) {
    echo '<div class="container-xl"><h1>Programm</h1><p>Keine Einträge vorhanden.</p></div>';
    return;
}

$tag_rows = $wpdb->get_results(
    "SELECT t.id, t.str_tag, j.fky_event_id
     FROM {$tag_table} t
     LEFT JOIN {$join_table} j ON j.fky_tag_id = t.id
     ORDER BY j.fky_event_id ASC, t.id ASC",
    ARRAY_A
);

$tag_map = [];
foreach ( $tag_rows as $row ) {
    $event_id = (int) ( $row['fky_event_id'] ?? 0 );
    $tag_name = trim( (string) ( $row['str_tag'] ?? '' ) );
    if ( $event_id <= 0 || $tag_name === '' ) {
        continue;
    }

    if ( ! isset( $tag_map[ $event_id ] ) ) {
        $tag_map[ $event_id ] = [];
    }

    $tag_map[ $event_id ][] = $tag_name;
}

foreach ( $events as $event_index => $event ) {
    $event_id = (int) ( $event['id'] ?? 0 );
    $event_tags = $tag_map[ $event_id ] ?? [];
    $tag_chunks[ $event_index ] = $event_tags;
}

$format_date = function ( $from, $to = null ) {
    $weekday = $from ? date_i18n( 'l,', strtotime( $from ) ) : '';
    $from_value = $from ? date_i18n( 'j. F Y', strtotime( $from ) ) : '';
    $to_value = $to ? date_i18n( 'j. F Y', strtotime( $to ) ) : '';

    if ( $from_value && $to_value && $from_value !== $to_value ) {
        return $weekday . "\n" . $from_value . ' – ' . $to_value;
    }

    return $weekday . "\n" . $from_value;
};

$format_time = function ( $time ) {
    if ( empty( $time ) ) {
        return '';
    }

    $time = trim( (string) $time );
    $time = preg_replace( '/\bUhr\b/i', 'Uhr', $time );

    $stamp = strtotime( $time );
    if ( ! $stamp ) {
        return '';
    }

    return date_i18n( 'H:i', $stamp ) . ' Uhr';
};

$base_image_url = trailingslashit( wp_upload_dir()['baseurl'] ) . 'events/';
$normalize_media_url = static function ( $value ) {
    $value = trim( (string) $value );
    if ( $value === '' ) {
        return '';
    }

    $site_url = home_url( '/' );
    $site_host = parse_url( $site_url, PHP_URL_HOST );
    $parsed = parse_url( $value );

    if ( is_array( $parsed ) && ! empty( $parsed['host'] ) ) {
        $parsed_host = strtolower( (string) $parsed['host'] );
        $site_host = strtolower( (string) $site_host );

        if ( $parsed_host === $site_host || $parsed_host === 'www.' . $site_host ) {
            return $value;
        }

        if ( ! empty( $parsed['path'] ) && preg_match( '#/(?:wp-content|uploads)/#i', $parsed['path'] ) ) {
            return home_url( $parsed['path'] );
        }
    }

    if ( preg_match( '#^(https?:)?//#i', $value ) ) {
        return $value;
    }

    if ( preg_match( '#^https?://#i', $value ) ) {
        return $value;
    }

    $relative = ltrim( $value, '/' );
    if ( str_starts_with( $relative, 'wp-content/' ) ) {
        return home_url( '/' ) . $relative;
    }

    if ( str_starts_with( $relative, 'uploads/' ) ) {
        return home_url( '/' ) . 'wp-content/' . $relative;
    }

    return home_url( '/wp-content/uploads/events/' ) . ltrim( $relative, '/' );
};

$items = [];
foreach ( $events as $index => $event ) {
    $event_title     = trim( (string) ( $event['str_title'] ?? '' ) );
    $artist          = trim( (string) ( $event['str_artist'] ?? '' ) );
    $artist_detail   = trim( (string) ( $event['str_artist_detail'] ?? '' ) );
    $description     = trim( (string) ( $event['mem_description'] ?? '' ) );
    $extra           = trim( (string) ( $event['str_date_extra'] ?? '' ) );
    $venue_name      = trim( (string) ( $event['location_name'] ?? '' ) );
    $sales_url       = trim( (string) ( $event['str_hyperlink_sales'] ?? '' ) );
    $link_url        = trim( (string) ( $event['str_hyperlink'] ?? '' ) );
    $video_url       = $normalize_media_url( $event['str_video'] ?? '' );
    $image_src       = $normalize_media_url( $event['str_image'] ?? '' );
    $date_from       = $event['dtm_date_from'] ?? '';
    $date_to         = $event['dtm_date_to'] ?? '';
    $time_from       = $event['dtm_time_from'] ?? '';
    $time_to         = $event['dtm_time_to'] ?? '';
    $event_tags      = $tag_chunks[ $index ] ?? [];

    $date_label      = $format_date( $date_from, $date_to );
    $time_label      = $format_time( $time_from );
    $time_end_label  = $format_time( $time_to );
    $event_year      = $date_from ? date_i18n( 'Y', strtotime( $date_from ) ) : '';

    if ( $time_label !== '' && $time_end_label !== '' && $time_label !== $time_end_label ) {
        $time_label = $time_label . ' – ' . $time_end_label;
    }

    if ( $image_src !== '' ) {
        if ( ! preg_match( '#^(https?:)?//#', $image_src ) && ! preg_match( '#^/#', $image_src ) ) {
            $image_src = $base_image_url . ltrim( $image_src, '/' );
        }
        if ( preg_match( '#^uploads/#', $image_src ) ) {
            $image_src = trailingslashit( wp_upload_dir()['baseurl'] ) . ltrim( $image_src, '/uploads/' );
        }
    }

    $meta = trim( implode( ' · ', array_filter( [ $date_label, $venue_name ] ) ) );
    $card_order = $index % 2 === 0 ? 'order-2 order-lg-1' : 'order-2 order-lg-2';
    $media_order = $index % 2 === 0 ? 'order-1 kk-media order-lg-2 kk-media-right ps-0' : 'order-1 kk-media order-lg-1 kk-media-left pe-0';

    $tags_html = '';
    if ( ! empty( $event_tags ) ) {
        $tag_output = [];
        foreach ( $event_tags as $tag ) {
            $tag = trim( (string) $tag );
            if ( $tag !== '' ) {
                $tag_output[] = '<span class="kk-tag">' . esc_html( $tag ) . '</span>';
            }
        }
        if ( ! empty( $tag_output ) ) {
            $tags_html = '<div class="kk-tags">' . implode( '', $tag_output ) . '</div>';
        }
    }

    $price_defs = array(
        'Erwachsene' => $event['num_price_adults'] ?? null,
        'Mitglieder' => $event['num_price_members'] ?? null,
        'Kinder'     => $event['num_price_children'] ?? null,
    );

    $price_parts = array();
    $has_price   = false;
    foreach ( $price_defs as $price_label => $price_value ) {
        if ( $price_value === null || $price_value === '' ) {
            continue;
        }
        if ( (float) $price_value > 0 ) {
            $has_price = true;
        }
        $price_parts[] = '<span class="kk-price-item"><strong>' . $price_label . ': CHF ' . number_format( (float) $price_value, 2, '.', ' ' ) . '</strong></span>';
    }

    $price_remark = trim( (string) ( $event['str_price_remark'] ?? '' ) );

    // Preiszeile nur auf der Programm-Seite: im Archiv (vergangene Anlässe) ganz weglassen.
    // Sonst nur zeigen, wenn mindestens ein Preis > 0 ist oder eine Bemerkung existiert.
    $price_html = '';
    if ( ! $is_archiv && ( $has_price || $price_remark !== '' ) ) {
        $price_segments = $has_price ? $price_parts : array();
        if ( $price_remark !== '' ) {
            $price_segments[] = '<span class="kk-price-item"><strong>' . esc_html( $price_remark ) . '</strong></span>';
        }
        $price_html = '<div class="kk-price-list">' . implode( ' | ', $price_segments ) . '</div>';
    }

    $video_html = '';
    if ( ! empty( $video_url ) ) {
        $video_html = '<button class="kk-videolink" type="button" data-video data-video-url="' . esc_url( $video_url ) . '" data-format="landscape" data-title="' . esc_attr( $event_title ) . '" data-meta="' . esc_attr( $meta ) . '"><span class="kk-tri"></span>Video ansehen</button>';
    }

    $image_html = '';
    if ( $image_src !== '' ) {
        $image_html = '<button class="kk-img-btn" type="button" data-image="' . esc_url( $image_src ) . '" data-title="' . esc_attr( $event_title ) . '" data-meta="' . esc_attr( $meta ) . '"><img class="kk-img" src="' . esc_url( $image_src ) . '" alt="' . esc_attr( $event_title ) . '"></button>';
    }

    $rail_date = $date_label !== '' ? esc_html( $date_label ) : '';
    $rail_time = $time_label !== '' ? esc_html( $time_label ) : '';
    $rail_venue = '';
    if ( $venue_name !== '' ) {
        $venue_href = esc_url( home_url( '/spielorte/' ) ) . '#' . sanitize_title( $venue_name );
        $rail_venue = '<a href="' . $venue_href . '">' . esc_html( $venue_name ) . '</a>';
    }
    $rail_extra = $extra !== '' ? esc_html( $extra ) : '';
    $action_extra = $extra !== '' ? '<span class="kk-small kk-muted">' . esc_html( $extra ) . '</span>' : '';

    $items[] = array(
        'anchor' => 'event-' . (int) ( $event['id'] ?? 0 ),
        'year' => esc_attr( $event_year ),
        'tags_attr' => esc_attr( implode( '|', $event_tags ) ),
        'date' => $rail_date,
        'time' => $rail_time,
        'venue' => $rail_venue,
        'extra' => $rail_extra,
        'action_extra' => $action_extra,
        'card_order' => esc_attr( $card_order ),
        'media_order' => esc_attr( $media_order ),
        'tags' => $tags_html,
        'title' => esc_html( $event_title ),
        'artist' => esc_html( $artist ),
        'description' => wp_kses_post( $description ),
        'artist_detail' => esc_html( $artist_detail ),
        'price' => $price_html,
        'sales' => ( ! $is_archiv && ! empty( $sales_url ) ) ? '<a class="kk-btn" href="' . esc_url( $sales_url ) . '" target="_blank" rel="noopener noreferrer">Vorverkauf <span class="kk-tri"></span></a>' : '',
        'link' => ! empty( $link_url ) ? '<a class="kk-videolink" href="' . esc_url( $link_url ) . '" target="_blank" rel="noopener noreferrer"><span class="kk-tri"></span>Mehr Infos</a>' : '',
        'video' => $video_html,
        'image' => $image_html,
    );
}

// kk-tags-categories: nur Tags, die auch tatsächlich unter den unten angezeigten
// Veranstaltungen vorkommen (also je nach archiv=0/1 unterschiedlich).
$available_tags = array();
foreach ( $tag_chunks as $chunk_tags ) {
    foreach ( $chunk_tags as $tag_name ) {
        $available_tags[ $tag_name ] = true;
    }
}
$available_tags = array_keys( $available_tags );
sort( $available_tags, SORT_STRING | SORT_FLAG_CASE );

$category_buttons = '<button class="kk-chip active" type="button" data-filter-tag="Alle">Alle</button>';
foreach ( $available_tags as $tag_name ) {
    $category_buttons .= '<button class="kk-chip" type="button" data-filter-tag="' . esc_attr( $tag_name ) . '">' . esc_html( $tag_name ) . '</button>';
}

// kk-tags-years: analog dazu nur Jahre, in denen auch tatsächlich ein angezeigter Anlass liegt.
$available_years = array();
foreach ( $items as $item ) {
    if ( $item['year'] !== '' ) {
        $available_years[ $item['year'] ] = true;
    }
}
$available_years = array_keys( $available_years );
rsort( $available_years, SORT_STRING ); // neuestes Jahr zuerst

$year_buttons = '<button class="kk-chip active" type="button" data-filter-year="Alle">Alle</button>';
foreach ( $available_years as $year_value ) {
    $year_buttons .= '<button class="kk-chip" type="button" data-filter-year="' . esc_attr( $year_value ) . '">' . esc_html( $year_value ) . '</button>';
}

// kk-tags-years (Jahresfilter) nur im Archiv, kk-tags-categories immer auf beiden Seiten.
$output = '<div class="row kk-tags-container mb-4">' . "\n";

if ( $is_archiv ) {
    $output .= <<<HTML
  <div class="kk-tags kk-tags-years p-0 pb-4">
    {$year_buttons}
  </div>

HTML;
}

$output .= <<<HTML
  <div class="kk-tags kk-tags-categories p-0">
    {$category_buttons}
  </div>
</div>
HTML;

$events_total = count( $items );
$output .= "\n" . '<div class="kk-small kk-muted mt-3 mb-2" data-archive-count>' . $events_total . ' von ' . $events_total . ' Anlässen</div>' . "\n";

foreach ( $items as $item ) {
    $rail_date_html = $item['date'];
    $rail_time_html = $item['time'];
    $rail_venue_html = $item['venue'];
    $rail_extra_html = $item['extra'];

    $output .= <<<HTML

<article id="{$item['anchor']}" class="row g-0 kk-row" data-year="{$item['year']}" data-tags="{$item['tags_attr']}">
    <aside class="col-12 col-lg-2 kk-rail">
        <div class="kk-rail-date">{$rail_date_html}</div>
        <div class="kk-rail-time">{$rail_time_html}</div>
        <div class="kk-rail-venue">{$rail_venue_html}</div>
        <div class="kk-small kk-muted mt-3">{$rail_extra_html}</div>
    </aside>

    <div class="col-12 col-lg-10">
        <div class="row g-0 align-items-start">
            <div class="col-12 col-lg-8 {$item['card_order']}">
                <div class="kk-body">
                    {$item['tags']}
                    <h2 class="kk-h2 kk-h2-spaced">{$item['title']}</h2>
                    <div class="kk-artist mt-2">{$item['artist']}</div>
                    {$item['artist_detail']}
                    <div class="kk-text mt-3 mb-0">{$item['description']}</div>

                    {$item['price']}
                    <div class="kk-actions">
                        {$item['sales']}
                        {$item['link']}
                        {$item['video']}
                        <!--
                        {$item['action_extra']}-->
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4 {$item['media_order']}">
                {$item['image']}
            </div>
        </div>
    </div>
</article>
HTML;
}

echo '<main class="container-fluid p-0 m-0 px-sm-3" id="programm" data-archive>' . $output . '</main>';
?>

<div class="kk-overlay" data-overlay="video">
  <div class="kk-overlay-inner">
    <div class="kk-overlay-head">
      <div>
        <div class="kk-overlay-title" data-overlay-title></div>
        <div class="kk-overlay-meta" data-overlay-meta></div>
      </div>
      <button class="kk-close" type="button" data-close aria-label="Schliessen">✕</button>
    </div>
    <div class="kk-player" data-player data-duration="2:14">
      <video class="kk-video-frame kk-video-file" data-video-file controls autoplay playsinline muted preload="auto"></video>
      <iframe class="kk-video-frame kk-video-embed" data-video-frame allowfullscreen loading="lazy" src=""></iframe>
      <div class="kk-ph" data-ph>VIDEO Querformat 16:9</div>
      <button class="kk-play" type="button" data-play aria-label="Abspielen"><span class="kk-glyph"></span></button>
      <div class="kk-controls">
        <div class="kk-track" data-seek><div class="kk-bar" data-bar></div></div>
        <div class="kk-times"><span data-time>0:00 / 2:14</span><span data-fmt>Querformat 16:9</span></div>
      </div>
    </div>
  </div>
</div>

<div class="kk-overlay" data-overlay="image">
  <div class="kk-overlay-inner">
    <div class="kk-overlay-head">
      <div>
        <div class="kk-overlay-title" data-overlay-title></div>
        <div class="kk-overlay-meta" data-overlay-meta></div>
      </div>
      <button class="kk-close" type="button" data-close aria-label="Schliessen">✕</button>
    </div>
    <img class="kk-overlay-img" alt="" data-overlay-image>
  </div>
</div>

<style>
  .kk-player .kk-video-frame {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    border: 0;
    display: none !important;
    z-index: 1;
    background: #000;
    object-fit: cover;
  }
  .kk-player .kk-video-file {
    display: none !important;
  }
  .kk-player.is-file-video .kk-video-file {
    display: block !important;
  }
  .kk-player .kk-video-embed {
    display: none !important;
  }
  .kk-player.is-embed-video .kk-video-embed {
    display: block !important;
  }
  .kk-player .kk-ph {
    z-index: 2;
    pointer-events: none;
  }
  .kk-player .kk-play {
    z-index: 4;
  }
  .kk-player.is-video-loaded .kk-ph {
    display: none;
  }
  .kk-player .kk-controls {
    z-index: 3;
  }
</style>

<script>
(function () {
  function setFormat(player, portrait) {
    if (!player) return;
    player.classList.toggle("portrait", portrait);
    var label = portrait ? "Hochformat 9:16" : "Querformat 16:9";
    var ph = player.querySelector("[data-ph]");
    if (ph) ph.textContent = "VIDEO " + label;
    player.querySelectorAll("[data-fmt]").forEach(function (el) {
      el.textContent = label;
    });
  }

  function resetProgress(player) {
    if (!player) return;
    var bar = player.querySelector("[data-bar]");
    var time = player.querySelector("[data-time]");
    if (bar) {
      bar.style.removeProperty("width");
      bar.classList.remove("seeked");
    }
    if (time) time.textContent = "0:00 / " + (player.dataset.duration || "1:30");
    var play = player.querySelector("[data-play]");
    if (play) play.classList.remove("playing");
  }

  function stopVideoPlayback() {
    var overlay = document.querySelector("[data-overlay=\"video\"]");
    if (!overlay) return;

    var iframe = overlay.querySelector("[data-video-frame]");
    var video = overlay.querySelector("[data-video-file]");

    if (iframe) {
      iframe.src = "";
      iframe.removeAttribute("src");
      iframe.style.display = "none";
    }

    if (video) {
      video.pause();
      video.currentTime = 0;
      video.removeAttribute("src");
      video.removeAttribute("muted");
      video.muted = false;
      video.load();
      video.style.display = "none";
    }
  }

  function closeAll() {
    stopVideoPlayback();
    document.querySelectorAll("[data-overlay]").forEach(function (overlay) {
      overlay.classList.remove("open");
    });
    document.body.classList.remove("overflow-hidden");
  }

  function openOverlay(overlay) {
    overlay.classList.add("open");
    document.body.classList.add("overflow-hidden");
  }

  function setLocalVideoFormat(player, video) {
    if (!player || !video) return;
    var width = Number(video.videoWidth || video.naturalWidth || 0);
    var height = Number(video.videoHeight || video.naturalHeight || 0);
    if (!width || !height) return;
    setFormat(player, height > width);
  }

  function resolveFormatFromButton(player, button) {
    if (!player || !button) return;
    var url = String(button.dataset.videoUrl || "").trim();
    if (isVideoFileUrl(url)) {
      setFormat(player, false);
      return;
    }
    setFormat(player, button.dataset.format === "portrait");
  }

  function startLocalVideo(player) {
    if (!player) return;
    var play = player.querySelector("[data-play]");
    var video = player.querySelector("[data-video-file]");
    var frame = player.querySelector("[data-video-frame]");
    if (!video || !video.src) return;

    if (frame) frame.style.display = "none";
    if (video) video.style.display = "block";

    player.classList.add("is-file-video");
    player.classList.remove("is-embed-video");
    video.removeAttribute("muted");
    video.muted = false;
    video.volume = 1;
    video.autoplay = false;
    video.playsInline = true;

    if (video.videoWidth && video.videoHeight) {
      setLocalVideoFormat(player, video);
    } else {
      video.onloadedmetadata = function () {
        setLocalVideoFormat(player, video);
      };
    }

    video.play().then(function () {
      if (play) play.classList.add("playing");
    }).catch(function () {
      if (play) play.classList.remove("playing");
      video.muted = true;
      video.setAttribute("muted", "muted");
    });
  }

  function startEmbeddedVideo(player) {
    if (!player) return;
    var play = player.querySelector("[data-play]");
    var frame = player.querySelector("[data-video-frame]");
    var video = player.querySelector("[data-video-file]");
    if (!frame || !frame.src) return;

    if (video) video.style.display = "none";
    if (frame) frame.style.display = "block";

    player.classList.add("is-embed-video");
    player.classList.remove("is-file-video");

    if (frame.contentWindow && typeof frame.contentWindow.postMessage === "function") {
      frame.contentWindow.postMessage(JSON.stringify({
        event: "command",
        func: "playVideo",
        args: []
      }), "*");
    }

    if (play) {
      play.classList.add("playing");
    }
  }

  function togglePlayerPlayback(player) {
    if (!player) return;

    var play = player.querySelector("[data-play]");
    var video = player.querySelector("[data-video-file]");
    var frame = player.querySelector("[data-video-frame]");
    var isPlaying = play ? play.classList.contains("playing") : false;

    if (video && video.src) {
      if (isPlaying) {
        video.pause();
        if (play) play.classList.remove("playing");
      } else {
        startLocalVideo(player);
      }
      return;
    }

    if (frame && frame.src) {
      if (isPlaying) {
        if (frame.contentWindow && typeof frame.contentWindow.postMessage === "function") {
          frame.contentWindow.postMessage(JSON.stringify({
            event: "command",
            func: "pauseVideo",
            args: []
          }), "*");
        }
        if (play) play.classList.remove("playing");
      } else {
        startEmbeddedVideo(player);
      }
    }
  }

  function isVideoFileUrl(url) {
    var value = String(url || "").trim();
    if (!value) return false;

    try {
      var parsed = new URL(value, window.location.href);
      return /\.(mp4|webm|ogg|m4v)(\?|$)/i.test(parsed.pathname || value);
    } catch (e) {
      return /\.(mp4|webm|ogg|m4v)(\?|$)/i.test(value);
    }
  }

  function setVideoSource(overlay, src) {
    var frame = overlay.querySelector("[data-video-frame]");
    var video = overlay.querySelector("[data-video-file]");
    var player = overlay.querySelector("[data-player]");
    if (player) {
      player.classList.remove("is-video-loaded", "is-file-video", "is-embed-video");
    }
    if (frame) {
      frame.src = "";
      frame.removeAttribute("src");
      frame.setAttribute("src", "");
      frame.style.display = "none";
    }
    if (video) {
      video.pause();
      video.removeAttribute("src");
      video.removeAttribute("muted");
      video.muted = false;
      video.load();
      video.style.display = "none";
      video.onloadedmetadata = null;
      video.onloadstart = null;
      video.onloadeddata = null;
    }
    if (!src) return;

    var url = String(src).trim();
    if (!url) return;

    try {
      var parsed = new URL(url, window.location.href);
      var host = parsed.hostname.toLowerCase();

      if (host.indexOf("youtube.com") !== -1 || host.indexOf("youtu.be") !== -1) {
        var videoId = "";
        if (host.indexOf("youtu.be") !== -1) {
          videoId = parsed.pathname.replace("/", "");
        } else {
          videoId = parsed.searchParams.get("v") || "";
        }
        if (videoId && frame) {
          frame.src = "https://www.youtube.com/embed/" + videoId + "?autoplay=1&rel=0&enablejsapi=1&mute=0";
          frame.setAttribute("allowfullscreen", "allowfullscreen");
          frame.setAttribute("allow", "autoplay; encrypted-media; picture-in-picture");
          if (player) {
            player.classList.add("is-embed-video", "is-video-loaded");
          }
          return;
        }
      }
    } catch (e) {
      // relative or malformed URLs are handled below by the file-type check
    }

    if (video && isVideoFileUrl(url)) {
      video.src = url;
      video.load();
      video.muted = false;
      video.volume = 1;
      video.autoplay = false;
      video.playsInline = true;

      var applyLocalVideoFormat = function () {
        if (player) {
          setLocalVideoFormat(player, video);
        }
      };

      video.onloadedmetadata = applyLocalVideoFormat;
      video.onloadeddata = applyLocalVideoFormat;
      video.onloadstart = applyLocalVideoFormat;

      if (video.readyState >= 1 && (video.videoWidth || video.naturalWidth)) {
        applyLocalVideoFormat();
      }

      if (player) {
        player.classList.add("is-file-video", "is-video-loaded");
      }
      return;
    }

    if (frame) {
      frame.src = url;
      if (player) {
        player.classList.add("is-embed-video", "is-video-loaded");
      }
    }
  }

  document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll("[data-video]").forEach(function (button) {
      button.addEventListener("click", function () {
        var overlay = document.querySelector("[data-overlay=\"video\"]");
        if (!overlay) return;
        overlay.querySelector("[data-overlay-title]").textContent = button.dataset.title || "";
        overlay.querySelector("[data-overlay-meta]").textContent = button.dataset.meta || "";
        var player = overlay.querySelector("[data-player]");
        resolveFormatFromButton(player, button);
        resetProgress(player);
        setVideoSource(overlay, button.dataset.videoUrl || "");
        if (player) {
          setTimeout(function () {
            var video = player.querySelector("[data-video-file]");
            if (video && video.videoWidth && video.videoHeight) {
              setLocalVideoFormat(player, video);
            }
          }, 150);
        }
        openOverlay(overlay);
      });
    });

    document.querySelectorAll("[data-image]").forEach(function (button) {
      button.addEventListener("click", function () {
        var overlay = document.querySelector("[data-overlay=\"image\"]");
        if (!overlay) return;
        overlay.querySelector("[data-overlay-title]").textContent = button.dataset.title || "";
        overlay.querySelector("[data-overlay-meta]").textContent = button.dataset.meta || "";
        overlay.querySelector("[data-overlay-image]").src = button.dataset.image || "";
        openOverlay(overlay);
      });
    });

    document.querySelectorAll("[data-close]").forEach(function (button) {
      button.addEventListener("click", closeAll);
    });

    document.querySelectorAll("[data-overlay]").forEach(function (overlay) {
      overlay.addEventListener("click", function (event) {
        if (event.target === overlay) closeAll();
      });
    });

    document.addEventListener("keydown", function (event) {
      if (event.key === "Escape") closeAll();
    });

    document.querySelectorAll("[data-player]").forEach(function (player) {
      var play = player.querySelector("[data-play]");
      var track = player.querySelector("[data-seek]");
      var bar = player.querySelector("[data-bar]");
      var time = player.querySelector("[data-time]");
      if (play) {
        play.addEventListener("click", function () {
          togglePlayerPlayback(player);
        });
      }
      if (track) {
        track.addEventListener("click", function (event) {
          var rect = track.getBoundingClientRect();
          var percent = Math.min(1, Math.max(0, (event.clientX - rect.left) / rect.width));
          if (bar) bar.style.width = (percent * 100) + "%";
          if (time) time.textContent = "0:" + String(Math.round(percent * 60)).padStart(2, "0") + " / " + (player.dataset.duration || "1:30");
        });
      }
    });
  });

  /* ---------- Jahres-/Tag-Filter (kk-tags-years / kk-tags-categories) ---------- */
  document.addEventListener("DOMContentLoaded", function () {
    var scope = document.querySelector("[data-archive]");
    if (!scope) return;

    var items = scope.querySelectorAll("[data-year]");
    var count = document.querySelector("[data-archive-count]");
    var tagButtons = document.querySelectorAll("[data-filter-tag]");
    var state = { year: "Alle", tag: "Alle" };

    function activate(selector, button) {
      document.querySelectorAll(selector).forEach(function (b) {
        b.classList.toggle("active", b === button);
      });
    }

    // Blendet Tag-Chips aus, für die es im gewählten Jahr keinen Anlass gibt.
    // Ist der aktuell gewählte Tag dadurch nicht mehr verfügbar, wird auf "Alle" zurückgesetzt.
    function updateTagAvailability() {
      var available = {};
      items.forEach(function (el) {
        if (state.year !== "Alle" && el.dataset.year !== state.year) {
          return;
        }
        String(el.dataset.tags || "").split("|").forEach(function (tag) {
          if (tag) {
            available[tag] = true;
          }
        });
      });

      var activeTagStillAvailable = state.tag === "Alle";
      tagButtons.forEach(function (button) {
        var tag = button.dataset.filterTag;
        if (tag === "Alle") {
          return;
        }
        var ok = !!available[tag];
        button.hidden = !ok;
        if (tag === state.tag && ok) {
          activeTagStillAvailable = true;
        }
      });

      if (!activeTagStillAvailable) {
        state.tag = "Alle";
        var allTagButton = document.querySelector('[data-filter-tag="Alle"]');
        if (allTagButton) {
          activate("[data-filter-tag]", allTagButton);
        }
      }
    }

    function apply() {
      var shown = 0;
      items.forEach(function (el) {
        var tags = String(el.dataset.tags || "").split("|");
        var ok = (state.year === "Alle" || el.dataset.year === state.year) &&
                 (state.tag === "Alle" || tags.indexOf(state.tag) > -1);
        el.hidden = !ok;
        if (ok) shown++;
      });
      if (count) {
        count.textContent = shown + " von " + items.length + " Anlässen";
      }
    }

    document.querySelectorAll("[data-filter-year]").forEach(function (button) {
      button.addEventListener("click", function () {
        state.year = button.dataset.filterYear;
        activate("[data-filter-year]", button);
        updateTagAvailability();
        apply();
      });
    });

    tagButtons.forEach(function (button) {
      button.addEventListener("click", function () {
        state.tag = button.dataset.filterTag;
        activate("[data-filter-tag]", button);
        apply();
      });
    });

    updateTagAvailability();
    apply();
  });
})();
</script>

<?php
