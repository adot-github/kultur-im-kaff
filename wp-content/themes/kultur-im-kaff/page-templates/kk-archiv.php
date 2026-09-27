<?php
/**
 * Template Name: KiK: Vergangene Anlässe
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
            $kultur_content = kultur_split_lead_paragraph( apply_filters( 'the_content', get_the_content() ) );
            ?>
            <header class="kk-hero">
                <div class="kk-hero-photo"></div>
                <div class="kk-hero-veil"></div>
                <?php get_template_part( 'partials/navbar' ); ?>
                <div class="container-xl kk-hero-inner">
                    <div class="kk-eyebrow"><?php echo esc_html( kultur_get_eyebrow_text( get_the_ID() ) ); ?></div>
                    <h1 class="kk-h1"><?php the_title(); ?></h1>
                    <?php if ( $kultur_content['lead'] ) : ?>
                        <div class="kk-lead mb-5"><?php echo $kultur_content['lead']; ?></div>
                    <?php endif; ?>
                </div>
            </header>

            <main class="container-xl kk-pagehead pb-5">
                <?php echo $kultur_content['rest']; ?>

                 <div class="row">
                    <?php echo do_shortcode('[events archiv=1]'); ?>
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
