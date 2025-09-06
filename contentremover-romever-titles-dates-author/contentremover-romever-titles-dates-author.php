<?php
/*
Plugin Name:  ContentRemover - Remove titles, dates, and authors
Description: A customizable plugin to hide post titles, dates, and author names on WordPress posts and pages via a metabox.
Version: 1.0.1
Author: Lion
License: GPL2
*/

// Define the plugin version constant
define('CONTENTREMOVER_VERSION', '1.0.1');

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load plugin text domain for translations
function contentremover_load_textdomain() {
    load_plugin_textdomain('contentremover-romever-titles-dates-author', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'contentremover_load_textdomain');

// Add metabox to post/page editor
function contentremover_add_meta_box() {
    add_meta_box(
        'contentremover_meta_box',
        esc_html__('Content Remover Settings', 'contentremover-romever-titles-dates-author'),
        'contentremover_meta_box_callback',
        ['post', 'page'],
        'side'
    );
}
add_action('add_meta_boxes', 'contentremover_add_meta_box');

// Callback function to render the metabox
function contentremover_meta_box_callback($post) {
    wp_nonce_field('contentremover_save_meta_box_data', 'contentremover_meta_box_nonce');
    
    $hide_title = get_post_meta($post->ID, '_contentremover_hide_title', true);
    $hide_date = get_post_meta($post->ID, '_contentremover_hide_date', true);
    $hide_author = get_post_meta($post->ID, '_contentremover_hide_author', true);
    
    echo '<p><label><input type="checkbox" name="contentremover_hide_title" value="1"' . checked($hide_title, 1, false) . '> ' . esc_html__('Hide Title', 'contentremover-romever-titles-dates-author') . '</label></p>';
    echo '<p><label><input type="checkbox" name="contentremover_hide_date" value="1"' . checked($hide_date, 1, false) . '> ' . esc_html__('Hide Date', 'contentremover-romever-titles-dates-author') . '</label></p>';
    echo '<p><label><input type="checkbox" name="contentremover_hide_author" value="1"' . checked($hide_author, 1, false) . '> ' . esc_html__('Hide Author', 'contentremover-romever-titles-dates-author') . '</label></p>';
}

// Save metabox data
function contentremover_save_meta_box_data($post_id) {
    if (!isset($_POST['contentremover_meta_box_nonce']) || 
        !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['contentremover_meta_box_nonce'])), 'contentremover_save_meta_box_data')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (isset($_POST['contentremover_hide_title'])) {
        update_post_meta($post_id, '_contentremover_hide_title', 1);
    } else {
        delete_post_meta($post_id, '_contentremover_hide_title');
    }
    if (isset($_POST['contentremover_hide_date'])) {
        update_post_meta($post_id, '_contentremover_hide_date', 1);
    } else {
        delete_post_meta($post_id, '_contentremover_hide_date');
    }
    if (isset($_POST['contentremover_hide_author'])) {
        update_post_meta($post_id, '_contentremover_hide_author', 1);
    } else {
        delete_post_meta($post_id, '_contentremover_hide_author');
    }
}
add_action('save_post', 'contentremover_save_meta_box_data');

// Enqueue scripts and styles for hiding content
function contentremover_enqueue_scripts_and_styles() {
    if (is_singular()) {
        global $post;
        $hide_author = get_post_meta($post->ID, '_contentremover_hide_author', true);
        $hide_date = get_post_meta($post->ID, '_contentremover_hide_date', true);

        wp_enqueue_script(
            'contentremover-script',
            plugins_url('/js/contentremover.js', __FILE__),
            array('jquery'),
            CONTENTREMOVER_VERSION,
            true
        );

        wp_localize_script('contentremover-script', 'contentRemoverSettings', [
            'hideAuthor' => $hide_author,
            'hideDate' => $hide_date,
        ]);
    }
}
add_action('wp_enqueue_scripts', 'contentremover_enqueue_scripts_and_styles');

// Function to conditionally remove the title using a filter
function contentremover_conditionally_remove_title($title, $id = null) {
    if (is_singular() && in_the_loop() && !is_admin() && get_post_meta($id, '_contentremover_hide_title', true)) {
        return '';
    }
    return $title;
}
add_filter('the_title', 'contentremover_conditionally_remove_title', 10, 2);
