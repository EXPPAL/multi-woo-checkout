<?php 
?>
<header id="MWC_header">
    <div id="masthead" class="header_main">
        <?php
        $custom_logo_id = get_theme_mod('custom_logo');
        $image_logo = wp_get_attachment_image_src($custom_logo_id, 'full');
        ?>
        <div class="MWC_logo">
            <!-- Header logo -->
            <a href="<?php echo(get_home_url()) ?>" title="<?php bloginfo('title') ?>" rel="home">
                <img width="176" height="83" src="<?php echo($image_logo[0]) ?>" class="img_logo" alt="<?php bloginfo('title') ?>">
            </a>
        </div>
    </div>
</header>