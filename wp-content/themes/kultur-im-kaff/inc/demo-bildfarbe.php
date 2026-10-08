<?php
/**
 * DEMO (temporär): Bilder schwarzweiss oder farbig anzeigen.
 *
 * Zwei Links in der Fusszeile setzen das Cookie "kk_bilder" (farbig|sw). Bei "farbig" bekommt
 * <html> die Klasse kk-bilder-farbig und die Graustufen-Filter der Fotos werden aufgehoben.
 * Standard bleibt schwarzweiss (wie im Theme-CSS).
 *
 * Entfernen: diese Datei löschen und die require-Zeile in functions.php entfernen.
 * Der Haken do_action( 'kk_footer_bottom' ) in partials/footer.php gibt danach nichts mehr aus.
 */

defined( 'ABSPATH' ) || exit;

const KK_BILDER_COOKIE = 'kk_bilder';

/** Ohne JavaScript: ?kk_bilder=farbig|sw setzt das Cookie und lädt die Seite ohne Parameter neu */
add_action( 'template_redirect', function () {
    if ( ! isset( $_GET[ KK_BILDER_COOKIE ] ) ) {
        return;
    }
    $mode = $_GET[ KK_BILDER_COOKIE ] === 'farbig' ? 'farbig' : 'sw';
    setcookie( KK_BILDER_COOKIE, $mode, array( 'expires' => time() + 30 * DAY_IN_SECONDS, 'path' => '/', 'samesite' => 'Lax', 'secure' => is_ssl() ) );
    wp_safe_redirect( remove_query_arg( KK_BILDER_COOKIE ) );
    exit;
} );

/** Früh im <head>: Klasse setzen, bevor die Seite gezeichnet wird (kein Aufflackern) + CSS für "farbig" */
add_action( 'wp_head', function () {
    ?>
<script>
(function () {
  if (/(?:^|;\s*)kk_bilder=farbig(?:;|$)/.test(document.cookie)) document.documentElement.classList.add('kk-bilder-farbig');
})();
</script>
<style id="kk-demo-bildfarbe">
  /* Demo: Fotos farbig (überschreibt filter:grayscale aus _kk-theme.scss) */
  html.kk-bilder-farbig .kk-img,
  html.kk-bilder-farbig .kk-slide img { filter: none; }
  html.kk-bilder-farbig .kk-hero-photo { filter: none; mix-blend-mode: normal; }

  .kk-demo-bildfarbe { display: flex; flex-wrap: wrap; align-items: center; gap: .25rem 1rem; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--kk-rule-light); font-size: var(--kk-fs-16); }
  .kk-demo-bildfarbe a { font-weight: 600; }
  .kk-demo-bildfarbe a[aria-current="true"] { text-decoration: none; pointer-events: none; opacity: .55; }
</style>
    <?php
}, 1 );

/** Links in der Fusszeile (Haken in partials/footer.php) */
add_action( 'kk_footer_bottom', function () {
    $farbig = ( $_COOKIE[ KK_BILDER_COOKIE ] ?? '' ) === 'farbig';
    ?>
    <div class="kk-demo-bildfarbe" data-kk-bilder>
        <span>Bilder anzeigen:</span>
        <a href="<?php echo esc_url( add_query_arg( KK_BILDER_COOKIE, 'sw' ) ); ?>" data-kk-bilder-mode="sw" aria-current="<?php echo $farbig ? 'false' : 'true'; ?>">Schwarzweiss</a>
        <a href="<?php echo esc_url( add_query_arg( KK_BILDER_COOKIE, 'farbig' ) ); ?>" data-kk-bilder-mode="farbig" aria-current="<?php echo $farbig ? 'true' : 'false'; ?>">Farbig</a>
    </div>
    <script>
    (function () {
      var box = document.querySelector('[data-kk-bilder]');
      if (!box) return;
      function mark(mode) {
        box.querySelectorAll('[data-kk-bilder-mode]').forEach(function (a) {
          a.setAttribute('aria-current', a.getAttribute('data-kk-bilder-mode') === mode ? 'true' : 'false');
        });
      }
      // Stand aus dem Cookie (die Seite kann aus einem Cache kommen)
      mark(document.documentElement.classList.contains('kk-bilder-farbig') ? 'farbig' : 'sw');
      box.addEventListener('click', function (e) {
        var a = e.target.closest('[data-kk-bilder-mode]');
        if (!a) return;
        e.preventDefault();
        var mode = a.getAttribute('data-kk-bilder-mode');
        document.cookie = 'kk_bilder=' + mode + '; path=/; max-age=' + (30 * 86400) + '; SameSite=Lax' + (location.protocol === 'https:' ? '; Secure' : '');
        document.documentElement.classList.toggle('kk-bilder-farbig', mode === 'farbig');
        mark(mode);
      });
    })();
    </script>
    <?php
} );
