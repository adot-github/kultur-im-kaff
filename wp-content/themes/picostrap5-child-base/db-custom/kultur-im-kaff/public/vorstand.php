<?php
global $wpdb;

$table = $wpdb->prefix . 'kk_persons';
$query = "SELECT * FROM {$table} ORDER BY int_sort_order ASC, id ASC";
$rows = $wpdb->get_results( $query, ARRAY_A );

if ( empty( $rows ) ) {
    echo '<div class="container-xl"><h1>Vorstand</h1><p>Keine Einträge vorhanden.</p></div>';
    return;
}

?>

<div class="row g-4">
    <?php foreach ( $rows as $person ) : ?>
        <?php
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
        ?>
        <div class="col-6 col-md-4 col-lg-3 d-flex">
            <div class="kk-card w-100">
                <img class="kk-img kk-img-portrait" src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $name ); ?>">
                <div class="kk-card-body">
                    <div class="kk-card-name"><?php echo esc_html( $name ); ?></div>
                    <?php if ( $role !== '' ) : ?>
                        <div class="kk-card-role"><?php echo esc_html( $role ); ?></div>
                    <?php endif; ?>
                    <?php if ( $email !== '' ) : ?>
                        <div class="kk-card-email">
                            <a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
                        </div>
                    <?php endif; ?>

                    <?php if ( $tags !== '' ) : ?>
                        <div class="kk-tags mt-2">
                            <?php foreach ( preg_split('/\s*\|\s*/', $tags ) as $tag ) : ?>
                                <?php $tag = trim( (string) $tag ); ?>
                                <?php if ( $tag !== '' ) : ?>
                                    <span class="kk-tag"><?php echo esc_html( $tag ); ?></span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?php if ( $claim !== '' ) : ?>
                        <div class="kk-card-fact mt-2">«<?php echo esc_html( $claim ); ?>»</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
