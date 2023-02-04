<?php
//get style, script head
wp_head();
?>

<div id="wrapper" class="MWC_template mwc_dark">

    <!-- custom header -->
    <?php // include("custom_header.php") 
    ?>

    <!-- custom main content -->
    <div id="main">
        <div id="content" role="main">
            <div class="container">
                <div class="row row-main">
                    <div class="col large-12">
                        <div class="col-inner">
                            <?php
                            while (have_posts()) : the_post();
                                the_content();
                            endwhile;
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- custom footer -->
    <footer class="MWC_footer">
        <?php
        $link_faq = wp_get_nav_menu_items(get_nav_menu_locations()['mwc-onecheckout-faq-menu']);
        $menus_end = wp_get_nav_menu_items(get_nav_menu_locations()['mwc-onecheckout-end-menu']);

        if ($link_faq) {
        ?>
            <div class="footer_faq">
                <span><?php echo (__('Have a Question?', 'MWC')) ?></span>&nbsp;<a href="<?php echo ($link_faq[0]->url) ?>"><?php echo (__('See Our FAQS', 'MWC')) ?></a>
            </div>
        <?php
        }
        ?>

        <div class="footer_end">
            <div class="footer_menu">
                <?php
                if ($menus_end) {
                    foreach ($menus_end as $key => $menu) {
                ?>
                        <a href="<?php echo ($menu->url) ?>"><?php echo ($menu->title) ?></a>
                        <?php if ($key < (count($menus_end) - 1)) {
                            echo (" | ");
                        } ?>
                <?php
                    }
                }
                ?>
            </div>
            <div class="copyright_footer">Copyright <?php echo (date("Y")) ?> © <?php bloginfo('title') ?></div>
        </div>
    </footer>
</div>

<?php
//get style, script footer
wp_footer();
?>