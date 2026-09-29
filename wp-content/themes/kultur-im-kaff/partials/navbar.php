<nav class="kk-nav" aria-label="<?php esc_attr_e('Main navigation', 'picostrap5'); ?>">
    <div class="container-xl d-flex flex-wrap align-items-end justify-content-between gap-2 py-2 pb-2 pt-3">
        <a class="kk-logo-header" href="<?php echo esc_url(home_url('/')); ?>"><img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/img/kik-' . kk_color_skin() . '.png'); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>"></a>

        <button class="navbar-toggler d-lg-none kk-nav-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#kkNavbar" aria-controls="kkNavbar" aria-expanded="false" aria-label="<?php esc_attr_e('Toggle navigation', 'picostrap5'); ?>">
            <span class="kk-toggle-line"></span>
            <span class="kk-toggle-line"></span>
            <span class="kk-toggle-line"></span>
        </button>

        <div class="collapse navbar-collapse" id="kkNavbar">
            <?php
            wp_nav_menu(
                array(
                    // Menüort per get_template_part( 'partials/navbar', null, array( 'menu_location' => '…' ) )
                    'theme_location' => $args['menu_location'] ?? 'primary',
                    'container' => false,
                    'menu_class' => 'kk-navlinks',
                    'menu_id' => '',
                    'fallback_cb' => '__return_false',
                    'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                    'walker' => new bootstrap_5_wp_nav_menu_walker(),
                )
            );
            ?>
        </div>
    </div>
</nav>
<?php // "Nach oben"-Button, erscheint sobald die Navigation aus dem Bild gescrollt ist; Desktop: bündig am rechten Containerrand ?>
<div class="kk-totop-wrap">
    <div class="container-xl">
        <button type="button" class="kk-totop" aria-label="Nach oben" hidden>
            <svg viewBox="0 0 24 24" width="24" height="24" aria-hidden="true"><path d="M12 5l-7 7m7-7l7 7M12 5v14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
    </div>
</div>
<script>
(function () {
    var nav = document.querySelector('.kk-nav');
    var btn = document.querySelector('.kk-totop');
    if (!nav || !btn || !('IntersectionObserver' in window)) return;
    btn.hidden = false;
    new IntersectionObserver(function (entries) {
        btn.classList.toggle('is-visible', !entries[0].isIntersecting);
    }).observe(nav);
    btn.addEventListener('click', function () {
        var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        window.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' });
    });
})();
</script>
