<?php
?>
<div class="loadingMessageContainerWrapper">
    <div class="counter">
        <div style="border: 1px solid #333; padding: 20px; border-radius: 20px;box-shadow: 0px 6px 9px -5px #000000;background:rgba(255,255,255,.5);">
            <img src="<?php echo (isset($image_logo[0]) ? $image_logo[0] : (MWC_PLUGIN_URL . 'images/progress_logo.gif')) ?>" class="" style="width:200px;">
            <p class="steps1" style="font-weight: bold; padding-top: 15px;"><?php echo (__('Checking if you Qualify for Special Offers...', 'mwc')) ?> </p>
            <p style="font-weight: bold; padding-top: 15px; display: none;" class="steps2"><?php echo (__('Congratulations You Qualified!', 'mwc')) ?></p>
            <p style="font-weight: bold; padding-top: 15px; display: none;" class="steps3"><?php echo (__('Checking 2 Warehouses For Available Stock...', 'mwc')) ?></p>
            <p style="font-weight: bold; padding-top: 15px; display: none;" class="steps4"><?php echo (__('Stock Available In Warehouse 1! Reserving Your Units...', 'mwc')) ?></p>
            <div class="baroutter">
                <hr class="bar">
            </div>
        </div>
    </div>
</div>