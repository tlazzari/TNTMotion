<?php
/**
 * The main template file
 *
 * This file is used to display the home page, blog posts, or any default content if no other templates are available.
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_Five_Child
 * @since Twenty Twenty-Five Child 1.0
 */

get_header(); // Include the header.php file from the theme

?>

<main id="main" class="site-main">

    <?php
    if ( have_posts() ) :
        // Start the loop to display posts or pages
        while ( have_posts() ) :
            the_post();

            // Output the content of the post or page
            get_template_part( 'template-parts/content', get_post_type() );

        endwhile;

        // Pagination, if necessary
        the_posts_navigation();

    else :
        // Display a message when no content is found
        get_template_part( 'template-parts/content', 'none' );
    endif;
    ?>

</main><!-- #main -->

<?php
get_footer(); // Include the footer.php file from the theme
?>
<?php
// Silence is golden.
