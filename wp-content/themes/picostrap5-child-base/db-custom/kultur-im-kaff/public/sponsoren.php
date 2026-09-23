<?php
global $wpdb;

$sponsor_table = $wpdb->prefix . 'kk_sponsoren';

$sponsors = $wpdb->get_results(
    "SELECT * FROM {$sponsor_table} ORDER BY id ASC",
    ARRAY_A
);

$upload_dir     = wp_upload_dir();
$logo_base_url  = trailingslashit( $upload_dir['baseurl'] ) . 'sponsoren/';
$logo_base_path = trailingslashit( $upload_dir['basedir'] ) . 'sponsoren/';

$resolve_logo = static function ( $value ) use ( $logo_base_url, $logo_base_path ) {
    $value = trim( (string) ( $value ?? '' ) );
    if ( $value === '' ) {
        return '';
    }

    if ( preg_match( '#^(https?:)?//#i', $value ) || str_starts_with( $value, '/' ) ) {
        return $value;
    }

    $file = basename( $value );

    if ( file_exists( $logo_base_path . $file ) ) {
        return $logo_base_url . $file;
    }

    // Endung fehlt oder stimmt nicht: passende Datei im Ordner suchen.
    $stem = pathinfo( $file, PATHINFO_FILENAME );
    foreach ( array( 'svg', 'png', 'webp', 'jpg', 'jpeg', 'gif' ) as $ext ) {
        if ( file_exists( $logo_base_path . $stem . '.' . $ext ) ) {
            return $logo_base_url . $stem . '.' . $ext;
        }
    }

    return $logo_base_url . $file;
};
?>
<section class="kk-logowall">
  <div class="kk-logowall-head">
    <h2 class="kk-h2">Wer das möglich macht</h2>
    <span class="kk-label">Sponsoren und Gönner 2026/27</span>
  </div>
  <p class="kk-text mt-3 mb-4">Öffentliche Hand, Stiftungen und Betriebe aus dem Dorf — ohne sie gäbe es kein Programm.</p>

  <?php if ( ! empty( $sponsors ) ) : ?>
    <div class="kk-logogrid">
      <?php foreach ( $sponsors as $sponsor ) : ?>
        <?php
        $name = trim( (string) ( $sponsor['str_sponsor'] ?? '' ) );
        $text = trim( (string) ( $sponsor['mem_sponsor_text'] ?? '' ) );
        $url  = trim( (string) ( $sponsor['str_hyperlink'] ?? '' ) );
        $logo = $resolve_logo( $sponsor['str_logo'] ?? '' );

        if ( $logo === '' && $name === '' ) {
            continue;
        }

        $label    = $name !== '' ? $name : 'Sponsor';
        $has_flip = $text !== '' && $logo !== '';
        $classes  = 'kk-logo' . ( $has_flip ? ' kk-logo--flip' : '' );
        ?>
        <?php if ( $url !== '' ) : ?>
        <a class="<?php echo esc_attr( $classes ); ?>" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener">
        <?php else : ?>
        <div class="<?php echo esc_attr( $classes ); ?>">
        <?php endif; ?>
          <?php if ( $has_flip ) : ?>
            <span class="kk-logo-inner">
              <span class="kk-logo-face kk-logo-front">
                <img src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( $label ); ?>" loading="lazy">
              </span>
              <span class="kk-logo-face kk-logo-back">
                <span class="kk-logo-text"><?php echo esc_html( $text ); ?></span>
              </span>
            </span>
          <?php elseif ( $logo !== '' ) : ?>
            <img src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( $label ); ?>" loading="lazy">
          <?php else : ?>
            <span class="kk-label"><?php echo esc_html( $label ); ?></span>
          <?php endif; ?>
        <?php echo $url !== '' ? '</a>' : '</div>'; ?>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="kk-logowall-foot">
    <span class="kk-text">Wir freuen uns über neue Partnerschaften.</span>
    <a class="kk-btn" href="mailto:c.kessi@kultur-im-kaff.ch">Partner werden <span class="kk-tri"></span></a>
  </div>
</section>
