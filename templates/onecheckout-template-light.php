<?php

?>

<!DOCTYPE html>
<!--[if lte IE 9 ]><html class="ie lt-ie9" <?php language_attributes(); ?>> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->

<head>
    <meta charset="<?php bloginfo('charset'); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

    <link rel="profile" href="http://gmpg.org/xfn/11" />
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php do_action('flatsome_before_page'); ?>
    <div class="MWC_template">
        <div id="wrapper" class="MWC_template">
            <!-- custom main content -->
            <div id="main" class="<?php flatsome_main_classes();  ?>">
                <div id="content" role="main">
                    <?php
                    while (have_posts()) : the_post();
                        the_content();
                    endwhile;
                    ?>
                </div>
            </div>
        </div>
        <?php do_action('flatsome_after_page'); ?>


        <!-- custom footer -->
        <footer class="MWC_footer">
            <?php
            $link_faq = wp_get_nav_menu_items(get_nav_menu_locations()['mwc-onecheckout-faq-menu']);
            $menus_end = wp_get_nav_menu_items(get_nav_menu_locations()['mwc-onecheckout-end-menu']);
            ?>

            <div class="footer_end">

                <div class="copyright_footer"> <?php echo (pll__('Copyright') . ' ' . date("Y")) ?> © <?php bloginfo('title') ?></div>

                <div class="footer_menu">
                    <?php
                    if ($menus_end) {
                        foreach ($menus_end as $key => $menu) {
                    ?>
                            <a href="<?php echo ($menu->url) ?>"><?php echo ($menu->title) ?></a>
                    <?php
                        }
                    }
                    ?>
                </div>

                <img class="img_dmca_protected" src="<?= MWC_PLUGIN_URL . 'images/dmca-protected.png' ?>">
            </div>
        </footer>

        <?php
        //get style, script footer
        wp_footer();
        ?>
    </div>
</body>

</html>