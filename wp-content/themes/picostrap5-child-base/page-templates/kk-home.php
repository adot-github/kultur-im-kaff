<?php
/**
 * Template Name: KiK: Home Template
 */
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
  <head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php wp_head(); ?>
  </head>
  <body <?php body_class(); ?> >
    <?php wp_body_open(); ?>  
 <?php
    if ( have_posts() ) :
        while ( have_posts() ) : the_post();
            ?>
            <header class="kk-hero">
                <div class="kk-hero-photo"></div>
                <div class="kk-hero-veil"></div>
                <?php get_template_part( 'partials/navbar' ); ?>
                <div class="container-xl kk-hero-inner">
                    <div class="kk-eyebrow"><?php echo esc_html( kultur_get_eyebrow_text( get_the_ID() ) ); ?></div>
                    <h1 class="kk-hero-title"><?php the_title(); ?>
                    </h1>
                    <div class="position-relative">
                        <a class="kk-badge" href="/150-jahre-kulturkreis-kuettigen-rombach/">
                            <span class="kk-badge-kicker">Aktuell</span>
                            <span class="kk-badge-figure">150</span>
                            <span class="kk-badge-text">Jahre Kultur im&nbsp;Kaff</span>
                            <span class="kk-badge-more">Mehr dazu</span>
                        </a>
                    </div>

                    <div class="row g-4 mt-3 align-items-end">
                        <div class="col-12 col-md-7">
                            <div class="kk-lead mb-0"><?php the_content(); ?></div>
                        </div>

                        <div class="col-12 col-md-5 d-flex flex-wrap flex-lg-nowrap gap-2 justify-content-md-end">
                            <a class="kk-btn" href="#programm">Programm</a>
                            <a class="kk-btn kk-btn-yellow" href="/news/">News</a>
                            <a class="kk-btn kk-btn-outline" href="/mitglied-werden/">Mitglied werden</a>
                        </div>
                    </div>
                </div>
            </header>
            
            <main class="container-xl pb-5">
                <div class="row">
                    <?php echo do_shortcode('[events archiv=0]'); ?>
                </div>

                <div class="row mt-5">
                    <?php echo do_shortcode('[sponsoren]'); ?>
                </div>

            </main>
            <?php
        endwhile;
    else :
        _e( 'Sorry, no posts matched your criteria.', 'textdomain' );
    endif;

    get_template_part( 'partials/footer' );
    get_footer();
?>
