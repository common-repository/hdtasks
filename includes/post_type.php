<?php
/*
 * HDTasks custom post types and taxonomy
*/

/* Register Tasks Post Type
------------------------------------------------------- */
function hdt_custom_post_type()
{
    $labels = array(
        'name' => _x('Tasks', 'Post Type General Name', 'text_domain'),
        'singular_name' => _x('Task', 'Post Type Singular Name', 'text_domain'),
        'menu_name' => __('HDTasks', 'text_domain'),
        'name_admin_bar' => __('Post Type', 'text_domain'),
        'archives' => __('Item Archives', 'text_domain'),
        'attributes' => __('Item Attributes', 'text_domain'),
        'parent_item_colon' => __('Parent Item:', 'text_domain'),
        'all_items' => __('All Items', 'text_domain'),
        'add_new_item' => __('Add New Item', 'text_domain'),
        'add_new' => __('Add New', 'text_domain'),
        'new_item' => __('New Item', 'text_domain'),
        'edit_item' => __('Edit Item', 'text_domain'),
        'update_item' => __('Update Item', 'text_domain'),
        'view_item' => __('View Item', 'text_domain'),
        'view_items' => __('View Items', 'text_domain'),
        'search_items' => __('Search Item', 'text_domain'),
        'not_found' => __('Not found', 'text_domain'),
        'not_found_in_trash' => __('Not found in Trash', 'text_domain'),
        'featured_image' => __('Featured Image', 'text_domain'),
        'set_featured_image' => __('Set featured image', 'text_domain'),
        'remove_featured_image' => __('Remove featured image', 'text_domain'),
        'use_featured_image' => __('Use as featured image', 'text_domain'),
        'insert_into_item' => __('Insert into item', 'text_domain'),
        'uploaded_to_this_item' => __('Uploaded to this item', 'text_domain'),
        'items_list' => __('Items list', 'text_domain'),
        'items_list_navigation' => __('Items list navigation', 'text_domain'),
        'filter_items_list' => __('Filter items list', 'text_domain'),
    );
    $args = array(
        'label' => __('Task', 'text_domain'),
        'description' => __('Post Type Description', 'text_domain'),
        'labels' => $labels,
        'supports' => array('title', 'editor', 'hdt_projects'),
        'hierarchical' => true,
        'public' => false,
        'show_ui' => false,
        'show_in_menu' => false,
        'menu_position' => 55,
        'menu_icon' => 'dashicons-list-view',
        'show_in_admin_bar' => false,
        'show_in_nav_menus' => false,
        'can_export' => true,
        'has_archive' => false,
        'exclude_from_search' => true,
        'publicly_queryable' => false,
        'capability_type' => 'page',
    );
    register_post_type('hdt_tasks', $args);
}
add_action('init', 'hdt_custom_post_type', 0);

/* Regester Projects taxonomy
------------------------------------------------------- */
function hdt_custom_taxonomy()
{
    $labels = array(
        'name' => _x('Projects', 'Taxonomy General Name', 'text_domain'),
        'singular_name' => _x('Project', 'Taxonomy Singular Name', 'text_domain'),
        'menu_name' => __('Projects', 'text_domain'),
        'all_items' => __('All Items', 'text_domain'),
        'parent_item' => __('Parent Item', 'text_domain'),
        'parent_item_colon' => __('Parent Item:', 'text_domain'),
        'new_item_name' => __('New Item Name', 'text_domain'),
        'add_new_item' => __('Add New Item', 'text_domain'),
        'edit_item' => __('Edit Item', 'text_domain'),
        'update_item' => __('Update Item', 'text_domain'),
        'view_item' => __('View Item', 'text_domain'),
        'separate_items_with_commas' => __('Separate items with commas', 'text_domain'),
        'add_or_remove_items' => __('Add or remove items', 'text_domain'),
        'choose_from_most_used' => __('Choose from the most used', 'text_domain'),
        'popular_items' => __('Popular Items', 'text_domain'),
        'search_items' => __('Search Items', 'text_domain'),
        'not_found' => __('Not Found', 'text_domain'),
        'no_terms' => __('No items', 'text_domain'),
        'items_list' => __('Items list', 'text_domain'),
        'items_list_navigation' => __('Items list navigation', 'text_domain'),
    );

    $rewrite = array(
        'slug' => "client-project",
        'with_front' => true,
        'hierarchical' => false,
    );

    $args = array(
        'labels' => $labels,
        'hierarchical' => false,
        'public' => true,
        'show_ui' => false,
        'show_admin_column' => false,
        'show_in_nav_menus' => false,
        'show_tagcloud' => false,
        'rewrite' => $rewrite,
    );
    register_taxonomy('hdt_projects', array('hdt_tasks'), $args);
}
add_action('init', 'hdt_custom_taxonomy', 0);

/* Generate random string for project slug
------------------------------------------------------- */
function hdt_generate_random_string($length = 6)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

/* Set a unique slug for Projects before saving
------------------------------------------------------- */
add_filter('wp_insert_term_data', function ($data, $taxonomy, $args) {
    if ($taxonomy === "hdt_projects") {
        $slug = hdt_generate_random_string();
        $data['slug'] = wp_unique_term_slug($slug, (object) $args);
    }
    return $data;
}, 10, 3);

/* Make HDTasks pages use category template
------------------------------------------------------- */
function hdt_add_project_template($template)
{
    global $post;
    if (is_tax('hdt_projects')) {
        $template = dirname(__FILE__) . '/template.php';
    }
    return $template;
}
add_filter('archive_template', 'hdt_add_project_template');
