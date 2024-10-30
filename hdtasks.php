<?php
/*
 * Plugin Name: HDTasks
 * Description: The easiest project task management solution for teams and creatives
 * Plugin URI: https://harmonicdesign.ca?utm_source=HDTasks&utm_medium=pluginPage
 * Author: Harmonic Design
 * Author URI: https://harmonicdesign.ca?utm_source=HDTasks_author&utm_medium=pluginPage
 * Version: 0.2
 * Notes: This plugin is still in the early stages of development, and as such, some features you require may not have been implimented yet.
 */

if (!defined('ABSPATH')) {
    die('Invalid request.');
}

if (!defined('HDT_PLUGIN_VERSION')) {
    define('HDT_PLUGIN_VERSION', '0.2');
}

// TODO: Check perlinks on new site

/* Enqueue admin scripts to relevant pages
------------------------------------------------------- */
function hdt_add_admin_scripts($hook)
{
    global $post;
    // Only enqueue if we're on the
    // add/edit questions, quizzes, or settings page
    if ($hook == "hdtasks_page_hdt_about" || $hook == "toplevel_page_hdt_projects") {
        function hdt_print_scripts()
        {
            wp_enqueue_style(
                'hdt_admin_style',
                plugin_dir_url(__FILE__) . './includes/admin_style.css?v=' . HDT_PLUGIN_VERSION
            );
            wp_enqueue_script(
                'hdq_admin_script',
                plugins_url('./includes/admin_script.js?v=' . HDT_PLUGIN_VERSION, __FILE__),
                array('jquery'),
                '1.0',
                true
            );
        }
        hdt_print_scripts();
    }
}
add_action('admin_enqueue_scripts', 'hdt_add_admin_scripts', 10, 1);

/* Include the basic required files
------------------------------------------------------- */
require dirname(__FILE__) . '/includes/post_type.php'; // custom post types
require dirname(__FILE__) . '/includes/functions.php'; // general functions

// function to check if we are active
function hdt_exists()
{
    return;
}

/* Run the following on HDTasks plugin activation
------------------------------------------------------- */
function hdt_activate_plugin()
{
    hdt_custom_post_type();
    hdt_custom_taxonomy();
    flush_rewrite_rules(); // flush permalinks
}
register_activation_hook(__FILE__, 'hdt_activate_plugin');

/* Create HDTasks Settings page
------------------------------------------------------- */
function hdt_create_settings_page()
{
    function hdt_register_projects_page()
    {
        add_menu_page("HDTasks", "HDTasks", 'publish_posts', "hdt_projects", 'hdt_register_projects_page_callback', "dashicons-list-view", 6);
    }
    add_action('admin_menu', 'hdt_register_projects_page');

    function hdt_register_settings_page()
    {
        add_submenu_page('hdt_projects', 'Projects', 'Projects', 'publish_posts', 'hdt_projects', 'hdt_register_projects_page_callback');
        add_submenu_page('hdt_projects', 'HDTasks About', 'About / Options', 'publish_posts', 'hdt_about', 'hdt_register_settings_page_callback');
    }
    add_action('admin_menu', 'hdt_register_settings_page', 11);

}
add_action('init', 'hdt_create_settings_page');

function hdt_register_projects_page_callback()
{
    require dirname(__FILE__) . '/includes/hdt_projects.php';
}

function hdt_register_settings_page_callback()
{
    require dirname(__FILE__) . '/includes/hdt_about_options.php';
}
