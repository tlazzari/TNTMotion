<?php

function my_custom_header_block_patterns() {
    // Register a custom header pattern for Suppliers
    register_block_pattern(
        'twentytwentyfive-child/suppliers-header',
        array(
            'title'       => __( 'Suppliers Header', 'twentytwentyfive-child' ),
            'description' => __( 'A custom header for the Suppliers page.', 'twentytwentyfive-child' ),
            'content'     => '<!-- wp:group {"align":"full"} -->' .
                             '<div class="wp-block-group alignfull">' .
                             '<!-- wp:site-title /-->' .
                             '<!-- wp:navigation /-->' .
                             '</div>' .
                             '<!-- /wp:group -->',
        )
    );

    // Register a custom header pattern for Clients
    register_block_pattern(
        'twentytwentyfive-child/clients-header',
        array(
            'title'       => __( 'Clients Header', 'twentytwentyfive-child' ),
            'description' => __( 'A custom header for the Clients page.', 'twentytwentyfive-child' ),
            'content'     => '<!-- wp:group {"align":"full"} -->' .
                             '<div class="wp-block-group alignfull">' .
                             '<!-- wp:site-title /-->' .
                             '<!-- wp:navigation /-->' .
                             '</div>' .
                             '<!-- /wp:group -->',
        )
    );
}
add_action( 'init', 'my_custom_header_block_patterns' );

add_action( 'init', 'my_custom_block_patterns' );

// Enqueue parent and child theme styles
function twentytwentyfive_child_enqueue_styles() {
    wp_enqueue_style( 'twentytwentyfive-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'twentytwentyfive-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array('twentytwentyfive-style')
    );
}
add_action( 'wp_enqueue_scripts', 'twentytwentyfive_child_enqueue_styles' );

// Register 'primary' menu location
function twentytwentyfive_child_register_menus() {
    register_nav_menus(array(
        'primary' => __( 'Primary Menu', 'twentytwentyfive-child' ),
    ));
}
add_action( 'init', 'twentytwentyfive_child_register_menus' );
?>
