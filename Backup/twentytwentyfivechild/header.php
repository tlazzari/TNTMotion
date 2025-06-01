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

<!-- Message will print on every page -->
<?php
    echo '<!-- Header Message: This message is displayed on every page -->';
    echo '<h2>Welcome to ' . get_bloginfo('name') . '!</h2>'; // You can customize the message here
?>

<?php
// Conditional logic to load custom header for specific pages
get_template_part('header', 'custom');
    // Default header content
    ?>
    <header class="default-header">
        <h1>Default Site Header</h1>
        <!-- Default header content -->
    </header>
    <?php
}
?>

<!-- Your header continues below -->
