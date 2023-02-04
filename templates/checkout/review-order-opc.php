<?php
/**
 * OPC review order form template with Remove/Quantity columns
 *
 * @version 2.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<?php if ( ! is_ajax() ) : ?>
<div class="opc_order_review">
	<input type="hidden" name="is_opc" value="1" />
</div>
<?php endif; ?>