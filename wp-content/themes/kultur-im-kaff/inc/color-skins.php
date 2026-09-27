<?php
/**
 * Farbschemen pro Seite.
 * - ACF-Feld "Farbschema" in der rechten Seitenleiste jeder Seite
 * - Body-Klasse "kk-skin-{slug}" (Farben in sass/_kk-theme.scss)
 * - Logo in partials/navbar.php: img/kik-{slug}.png
 *
 * Für Ansichten ohne eigenes Feld (Archiv, 404 …) kann vor dem Header
 * global $kk_forced_color_skin gesetzt werden.
 */

const KK_COLOR_SKINS = array(
    'rot'     => 'Rot',
    'blau'    => 'Blau',
    'gruen'   => 'Grün',
    'magenta' => 'Magenta',
);

const KK_COLOR_SKIN_DEFAULT = 'rot';

add_action( 'acf/include_fields', function() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    acf_add_local_field_group( array(
        'key'      => 'group_kk_color_skin',
        'title'    => 'Farbschema',
        'fields'   => array(
            array(
                'key'           => 'field_kk_color_skin',
                'label'         => 'Farbschema',
                'name'          => 'color_skin',
                'type'          => 'select',
                'instructions'  => 'Ohne Auswahl: Rot',
                'choices'       => KK_COLOR_SKINS,
                'default_value' => false,
                'return_format' => 'value',
                'multiple'      => 0,
                'allow_null'    => 1,
                'ui'            => 0,
            ),
        ),
        'location' => array(
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'page',
                ),
            ),
        ),
        'position'        => 'side',
        'style'           => 'default',
        'label_placement' => 'top',
        'active'          => true,
        'show_in_rest'    => 0,
    ) );
} );

/**
 * Farbschema der aktuellen Ansicht (Slug aus KK_COLOR_SKINS).
 */
function kk_color_skin() {
    global $kk_forced_color_skin;

    $skin = $kk_forced_color_skin ?? null;

    if ( ! $skin && is_singular() && function_exists( 'get_field' ) ) {
        $skin = get_field( 'color_skin', get_queried_object_id() );
    }

    return array_key_exists( (string) $skin, KK_COLOR_SKINS ) ? $skin : KK_COLOR_SKIN_DEFAULT;
}

add_filter( 'body_class', function( $classes ) {
    $classes[] = 'kk-skin-' . kk_color_skin();
    return $classes;
} );
