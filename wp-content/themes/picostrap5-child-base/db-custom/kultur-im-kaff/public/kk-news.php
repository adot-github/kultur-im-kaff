<?php
// [kk-news] gibt eine Übersicht aller News-Meldungen aus.
// Bildergalerie analog zu kk_event_locations (Spielorte-Karussell), Video-Ausgabe
// mit Overlay + "Video ansehen"-Link analog zu den Anlässen (events.php).
global $wpdb;

$news_table = $wpdb->prefix . 'kk_news';

$news_rows = $wpdb->get_results(
    "SELECT * FROM {$news_table} WHERE ysn_online = 1 ORDER BY int_sort_order ASC, id DESC",
    ARRAY_A
);

if ( empty( $news_rows ) ) {
    return;
}

$normalize_text = static function ( $value ) {
    return trim( (string) ( $value ?? '' ) );
};

$news_image_base = trailingslashit( wp_upload_dir()['baseurl'] ) . 'news/';
$resolve_image = static function ( $value ) use ( $news_image_base ) {
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

    return $news_image_base . ltrim( $value, '/' );
};

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

    $relative = ltrim( $value, '/' );
    if ( str_starts_with( $relative, 'wp-content/' ) ) {
        return home_url( '/' ) . $relative;
    }

    if ( str_starts_with( $relative, 'uploads/' ) ) {
        return home_url( '/' ) . 'wp-content/' . $relative;
    }

    return home_url( '/wp-content/uploads/news/' ) . ltrim( $relative, '/' );
};

$news_items = array();
$used_anchors = array();
foreach ( $news_rows as $row ) {
    $news_id = (int) ( $row['id'] ?? 0 );
    $title   = $normalize_text( $row['str_title'] ?? '' );
    $lead    = $normalize_text( $row['mem_lead'] ?? '' );
    $link    = $normalize_text( $row['str_hyperlink'] ?? '' );
    $video   = $normalize_media_url( $row['str_video'] ?? '' );

    $date_published = $normalize_text( $row['dtm_date_published'] ?? '' );
    $date_label = $date_published !== '' ? date_i18n( 'j. F Y', strtotime( $date_published ) ) : '';

    $slides = array();
    foreach ( array( 'str_image_1', 'str_image_2', 'str_image_3', 'str_image_4', 'str_image_5' ) as $image_key ) {
        $src = $resolve_image( $row[ $image_key ] ?? '' );
        if ( $src !== '' ) {
            $slides[] = $src;
        }
    }

    $anchor_base = sanitize_title( $title !== '' ? $title : 'news-' . $news_id );
    if ( $anchor_base === '' ) {
        $anchor_base = 'news-' . $news_id;
    }
    $anchor = $anchor_base;
    $anchor_suffix = 2;
    while ( in_array( $anchor, $used_anchors, true ) ) {
        $anchor = $anchor_base . '-' . $anchor_suffix;
        $anchor_suffix++;
    }
    $used_anchors[] = $anchor;

    $news_items[] = array(
        'anchor' => $anchor,
        'title'  => $title !== '' ? $title : 'News',
        'date'   => $date_label,
        'lead'   => $lead,
        'link'   => $link,
        'video'  => $video,
        'slides' => $slides,
    );
}

ob_start();
?>
<div class="kk-news-list">
    <?php foreach ( $news_items as $news ) : ?>
        <section id="<?php echo esc_attr( $news['anchor'] ); ?>" class="row g-4 kk-venue align-items-start">
            <div class="col-12 col-lg-5">
                <div class="kk-carousel" data-carousel>
                    <?php if ( ! empty( $news['slides'] ) ) : ?>
                        <?php foreach ( $news['slides'] as $index => $slide_src ) : ?>
                            <div class="kk-slide<?php echo 0 === $index ? ' active' : ''; ?>" data-slide>
                                <img src="<?php echo esc_url( $slide_src ); ?>" alt="<?php echo esc_attr( $news['title'] ); ?>" loading="lazy">
                            </div>
                        <?php endforeach; ?>
                        <?php if ( count( $news['slides'] ) > 1 ) : ?>
                            <button class="kk-carbtn prev" type="button" data-car="prev" aria-label="Vorheriges Foto"><svg viewBox="0 0 12 20" width="11" height="18" aria-hidden="true"><polygon points="11,0 11,20 0,10" fill="currentColor"></polygon></svg></button>
                            <button class="kk-carbtn next" type="button" data-car="next" aria-label="Nächstes Foto"><svg viewBox="0 0 12 20" width="11" height="18" aria-hidden="true"><polygon points="1,0 1,20 12,10" fill="currentColor"></polygon></svg></button>
                            <div class="kk-dots">
                                <?php foreach ( $news['slides'] as $index => $slide_src ) : ?>
                                    <button class="kk-dot<?php echo 0 === $index ? ' active' : ''; ?>" type="button" data-dot aria-label="Foto <?php echo esc_attr( $index + 1 ); ?>"></button>
                                <?php endforeach; ?>
                            </div>
                            <div class="kk-counter" data-counter>1 / <?php echo count( $news['slides'] ); ?></div>
                        <?php endif; ?>
                    <?php else : ?>
                        <div class="kk-ph kk-slide active" data-slide><?php echo esc_html( $news['title'] ); ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-12 col-lg-7">
                <?php if ( $news['date'] !== '' ) : ?>
                    <div class="kk-label mb-1"><?php echo esc_html( $news['date'] ); ?></div>
                <?php endif; ?>
                <h2 class="kk-h2"><?php echo esc_html( $news['title'] ); ?></h2>
                <?php if ( $news['lead'] !== '' ) : ?>
                    <p class="kk-text mt-3 mb-0"><?php echo nl2br( esc_html( $news['lead'] ) ); ?></p>
                <?php endif; ?>

                <div class="kk-actions mt-3">
                    <?php if ( $news['link'] !== '' ) : ?>
                        <a class="kk-btn" href="<?php echo esc_url( $news['link'] ); ?>" target="_blank" rel="noopener noreferrer">Mehr erfahren <span class="kk-tri"></span></a>
                    <?php endif; ?>
                    <?php if ( $news['video'] !== '' ) : ?>
                        <button class="kk-btn kk-btn-yellow" type="button" data-video data-video-url="<?php echo esc_url( $news['video'] ); ?>" data-format="landscape" data-title="<?php echo esc_attr( $news['title'] ); ?>" data-meta=""><span class="kk-tri"></span>Video ansehen</button>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endforeach; ?>
</div>

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

<style>
  .kk-news-list .kk-slide img {
    filter: none;
  }
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
})();
</script>
<?php
$output = ob_get_clean();
echo $output;
