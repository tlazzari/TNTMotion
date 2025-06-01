<?php
/**
 * The header for our theme
 *
 * This template displays the <head> section and the header based on page conditions.
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_Five_Child
 * @since Twenty Twenty-Five Child 1.0
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?> <!-- Critical for scripts and styles -->
</head>
<body <?php body_class(); ?>>

<?php
// Conditional logic to load custom header for specific pages
if (is_page(array('suppliers', 'clients-and-rfq', 'suppliers-and-rfq','supplier-rfq-product-table','clients','supplier-orders','tnt-invoices', 'bam'))) {
    // Optional Debugging Comment (Remove after testing)
    echo '<!-- Custom Header Loaded -->';
    get_template_part('header', 'custom');
} else {
    // Optional Debugging Comment (Remove after testing)
    echo '<!-- Default Header Loaded -->';
    ?>
    <!-- wp:group {"align":"full","layout":{"type":"default"}} -->
    <div class="wp-block-group alignfull">
        <!-- wp:group {"layout":{"type":"constrained"}} -->
        <div class="wp-block-group">
            <!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|30"}}},"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
            <div class="wp-block-group alignwide" style="padding-top:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--30)">
                <!-- wp:site-title {"level":0} /-->
                <!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|10"}},"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"right"}} -->
                <div class="wp-block-group">
                    <!-- wp:navigation {"overlayBackgroundColor":"base","overlayTextColor":"contrast","layout":{"type":"flex","justifyContent":"right","flexWrap":"wrap"}} /-->
                </div>
                <!-- /wp:group -->
            </div>
            <!-- /wp:group -->
        </div>
        <!-- /wp:group -->
    </div>
    <!-- /wp:group -->
    <?php
}
?>
