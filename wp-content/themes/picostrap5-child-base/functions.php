<?php
/*
        _               _                  _____        _     _ _     _   _   _                         
       (_)             | |                | ____|      | |   (_) |   | | | | | |                        
  _ __  _  ___ ___  ___| |_ _ __ __ _ _ __| |__     ___| |__  _| | __| | | |_| |__   ___ _ __ ___   ___ 
 | '_ \| |/ __/ _ \/ __| __| '__/ _` | '_ \___ \   / __| '_ \| | |/ _` | | __| '_ \ / _ \ '_ ` _ \ / _ \
 | |_) | | (_| (_) \__ \ |_| | | (_| | |_) |__) | | (__| | | | | | (_| | | |_| | | |  __/ | | | | |  __/
 | .__/|_|\___\___/|___/\__|_|  \__,_| .__/____/   \___|_| |_|_|_|\__,_|  \__|_| |_|\___|_| |_| |_|\___|
 | |                                 | |                                                                
 |_|                                 |_|                                                                

                                                       
*************************************** WELCOME TO PICOSTRAP ***************************************

********************* THE BEST WAY TO EXPERIENCE SASS, BOOTSTRAP AND WORDPRESS *********************

    PLEASE WATCH THE VIDEOS FOR BEST RESULTS:
    https://www.youtube.com/playlist?list=PLtyHhWhkgYU8i11wu-5KJDBfA9C-D4Bfl

*/

//LOAD LC CONFIG TO DEFINE FRAMEWORK
require_once ("livecanvas/configuration.php");
require_once get_stylesheet_directory() . '/inc/site-design-renderer.php';

// DE-ENQUEUE PARENT THEME BOOTSTRAP JS BUNDLE
add_action( 'wp_print_scripts', function(){
    wp_dequeue_script( 'bootstrap5' );
    //wp_dequeue_script( 'dark-mode-switch' );  //optionally
}, 100 );

// ENQUEUE THE BOOTSTRAP JS BUNDLE (AND EVENTUALLY MORE LIBS) FROM THE CHILD THEME DIRECTORY
add_action( 'wp_enqueue_scripts', function() {
    //enqueue js in footer, defer
    wp_enqueue_script( 'bootstrap5-childtheme', get_stylesheet_directory_uri() . "/js/bootstrap.bundle.min.js", array(), null, array('strategy' => 'defer', 'in_footer' => true)  );
    wp_enqueue_style( 'kultur-montserrat-alternates', 'https://fonts.googleapis.com/css2?family=Montserrat+Alternates:wght@400;500;600;700;800&display=swap', array(), null );
    // wp_enqueue_style( 'kultur-site-design', get_stylesheet_directory_uri() . '/site-design-source/assets/theme.css', array(), '1.0.0' );
    // wp_enqueue_script( 'kultur-site-design', get_stylesheet_directory_uri() . '/site-design-source/assets/app.js', array(), '1.0.0', array('strategy' => 'defer', 'in_footer' => true) );
    
    //optional: example of how to globally lazyload js files eg lottie player, using defer
    //wp_enqueue_script( 'lottie-player', 'https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js', array(), null, array('strategy' => 'defer', 'in_footer' => true)  );
}, 101);

// HACK HERE: ENQUEUE YOUR CUSTOM JS FILES, IF NEEDED
add_action( 'wp_enqueue_scripts', function() {	   
    
    //UNCOMMENT next row to include the js/custom.js file globally
    //wp_enqueue_script('custom', get_stylesheet_directory_uri() . '/js/custom.js', array(/* 'jquery' */), null, array('strategy' => 'defer', 'in_footer' => true) ); 

    //UNCOMMENT next 3 rows to load the js file only on one page
    //if (is_page('mypageslug')) {
    //    wp_enqueue_script('custom', get_stylesheet_directory_uri() . '/js/custom.js', array(/* 'jquery' */), null, array('strategy' => 'defer', 'in_footer' => true) ); 
    //}  

}, 102);

// OPTIONAL: ADD MORE NAV MENUS
register_nav_menus( array(
    'primary' => __( 'Primary Menu', 'picostrap5' ),
    'footer' => __( 'Footer Menu', 'picostrap5' ),
) );
// THEN USE SHORTCODE:  [lc_nav_menu theme_location="third" container_class="" container_id="" menu_class="navbar-nav"]

// OPTIONAL: FOR SECURITY: DISABLE APPLICATION PASSWORDS. Uncomment if needed
//add_filter( 'wp_is_application_passwords_available', '__return_false' );

// ADD YOUR CUSTOM PHP CODE DOWN BELOW /////////////////////////

/**
 * Only show our own "KiK: ..." page templates in the page editor, hiding
 * the parent theme's built-in templates (Blank, Sidebar Left/Right, etc.).
 */
add_filter( 'theme_page_templates', function( $post_templates ) {
    foreach ( $post_templates as $file => $name ) {
        if ( 0 !== stripos( $name, 'KiK' ) ) {
            unset( $post_templates[ $file ] );
        }
    }
    return $post_templates;
} );

/**
 * New pages get "KiK: Default Template" pre-assigned, so it shows selected
 * in the editor's Template dropdown right away.
 */
add_action( 'wp_insert_post', function( $post_id, $post, $update ) {
    if ( $update || 'page' !== $post->post_type ) {
        return;
    }
    if ( ! get_post_meta( $post_id, '_wp_page_template', true ) ) {
        update_post_meta( $post_id, '_wp_page_template', 'page-templates/kk-default.php' );
    }
}, 10, 3 );

/**
 * Any page left without an explicitly assigned template (older pages, or
 * "Default template" picked in the dropdown) renders with "KiK: Default
 * Template" instead of the theme's own page.php.
 */
add_filter( 'page_template', function( $template ) {
    if ( ! get_page_template_slug( get_queried_object_id() ) ) {
        $fallback = locate_template( 'page-templates/kk-default.php' );
        if ( $fallback ) {
            return $fallback;
        }
    }
    return $template;
} );

/**
 * Returns the title of the top-level "primary" menu item that the given
 * page belongs to (its own title if it is itself a top-level item, or its
 * top-level ancestor's title if it sits in a dropdown).
 */
function kultur_get_top_level_nav_title( $post_id ) {
    $locations = get_nav_menu_locations();
    if ( empty( $locations['primary'] ) ) {
        return '';
    }

    $menu_items = wp_get_nav_menu_items( $locations['primary'] );
    if ( ! $menu_items ) {
        return '';
    }

    $items_by_id = array();
    foreach ( $menu_items as $item ) {
        $items_by_id[ (int) $item->ID ] = $item;
    }

    $current_item = null;
    foreach ( $menu_items as $item ) {
        if ( 'post_type' === $item->type && (int) $item->object_id === (int) $post_id ) {
            $current_item = $item;
            break;
        }
    }

    if ( ! $current_item ) {
        return '';
    }

    $top_item = $current_item;
    while ( (int) $top_item->menu_item_parent > 0 && isset( $items_by_id[ (int) $top_item->menu_item_parent ] ) ) {
        $top_item = $items_by_id[ (int) $top_item->menu_item_parent ];
    }

    return $top_item->title;
}

/**
 * Eyebrow text for kk-* page templates: manual 'kk_eyebrow' override wins,
 * otherwise the page's top-level primary-menu title, otherwise 'Verein'.
 */
function kultur_get_eyebrow_text( $post_id ) {
    $eyebrow = get_post_meta( $post_id, 'kk_eyebrow', true );
    if ( $eyebrow ) {
        return $eyebrow;
    }

    $nav_title = kultur_get_top_level_nav_title( $post_id );
    if ( $nav_title ) {
        return $nav_title;
    }

    return 'Kultur im Kaff';
}

/**
 * Splits filtered post content into its first paragraph (shown as a lead-in
 * inside the page's hero header) and the remainder (shown further down in
 * the normal content area). Falls back to an empty lead if the content
 * doesn't start with a plain paragraph.
 */
function kultur_split_lead_paragraph( $content ) {
    if ( preg_match( '/^\s*(<p[^>]*>.*?<\/p>)/is', $content, $matches ) ) {
        return array(
            'lead' => $matches[1],
            'rest' => substr( $content, strlen( $matches[0] ) ),
        );
    }

    return array(
        'lead' => '',
        'rest' => $content,
    );
}

