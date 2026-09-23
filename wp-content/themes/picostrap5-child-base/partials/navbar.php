<nav class="kk-nav" aria-label="<?php esc_attr_e('Main navigation', 'picostrap5'); ?>">
    <div class="container-xl d-flex flex-wrap align-items-end justify-content-between gap-2 py-2 pb-2 pt-3">
        <a class="kk-logo-header" href="<?php echo esc_url(home_url('/')); ?>"><img src="/wp-content/uploads/kik.png"></a>

        <button class="navbar-toggler d-lg-none kk-nav-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#kkNavbar" aria-controls="kkNavbar" aria-expanded="false" aria-label="<?php esc_attr_e('Toggle navigation', 'picostrap5'); ?>">
            <span class="kk-toggle-line"></span>
            <span class="kk-toggle-line"></span>
            <span class="kk-toggle-line"></span>
        </button>

        <div class="collapse navbar-collapse" id="kkNavbar">
            <?php
            wp_nav_menu(
                array(
                    'theme_location' => 'primary',
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