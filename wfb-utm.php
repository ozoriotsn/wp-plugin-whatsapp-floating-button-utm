<?php
/**
 * Plugin Name: WFB UTM
 * Description: Add a WhatsApp floating button on specific pages with customizable phone number, UTM, and page targets.
 * Version: 1.0
 * Author: Ozorio Neto
 * Author URI: https://linkme.bio/ozoriotsn
 * Text Domain: wfb-utm
 * Domain Path: /languages
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 5.0
 * Tested up to: 6.8
 * Requires PHP: 7.0
 */


if (!defined('ABSPATH')) {
    exit;
}


add_action('wp_enqueue_scripts', 'wfb_utm_enqueue_styles');
add_action('admin_enqueue_scripts', 'wfb_utm_enqueue_admin_styles');
add_action('admin_menu', 'wfb_utm_create_menu');
add_action('wp_footer', 'wfb_utm_add_whatsapp_button');

function wfb_utm_enqueue_styles()
{
    wp_enqueue_style(
        'wfb-utm-style',
        plugin_dir_url(__FILE__) . 'assets/css/style.css',
        array(),
        '1.0',
        'all'
    );
}

function wfb_utm_enqueue_admin_styles()
{
    wp_enqueue_style(
        'wfb-utm-admin-style',
        plugin_dir_url(__FILE__) . 'assets/css/admin-style.css',
        array(),
        '1.0'
    );
}

function wfb_utm_create_menu()
{
    add_options_page(
        esc_html__('WhatsApp Floating Button UTM - Settings', 'wfb-utm'),
        esc_html__('WhatsApp Floating Button UTM', 'wfb-utm'),
        'manage_options',
        'wfb-utm-settings',
        'wfb_utm_settings_page'
    );
    add_action('admin_init', 'wfb_utm_settings_init');
}

function wfb_utm_settings_init()
{
    register_setting('wfb-utm-settings-group', 'wfb_phone_number', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('wfb-utm-settings-group', 'wfb_utm_params', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('wfb-utm-settings-group', 'wfb_target_pages', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('wfb-utm-settings-group', 'wfb_text', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('wfb-utm-settings-group', 'wfb_button_position', ['sanitize_callback' => 'sanitize_text_field']);

    add_settings_section('wfb_utm_section', esc_html__('Settings', 'wfb-utm'), null, 'wfb-utm-settings');

    add_settings_field('wfb_phone_number', esc_html__('WhatsApp phone number (with DDD and country code)', 'wfb-utm'), 'wfb_utm_phone_input', 'wfb-utm-settings', 'wfb_utm_section');
    add_settings_field('wfb_text', esc_html__('Text of service (optional)', 'wfb-utm'), 'wfb_utm_text_input', 'wfb-utm-settings', 'wfb_utm_section');
    add_settings_field('wfb_utm_params', esc_html__('Parameters UTM (optional)', 'wfb-utm'), 'wfb_utm_utm_input', 'wfb-utm-settings', 'wfb_utm_section');
    add_settings_field('wfb_target_pages', esc_html__('Not show button on pages (slugs separated by comma, e.g. contact, support) leave empty to appear on all pages', 'wfb-utm'), 'wfb_utm_pages_input', 'wfb-utm-settings', 'wfb_utm_section');
    add_settings_field('wfb_button_position', esc_html__('Position button (left or right)', 'wfb-utm'), 'wfb_utm_button_position_input', 'wfb-utm-settings', 'wfb_utm_section');
}

function wfb_utm_button_position_input()
{
    $value = sanitize_text_field(get_option('wfb_button_position', ''));
    $selectedLeft = $value === 'left' ? 'selected' : '';
    $selectedRight = $value === 'right' ? 'selected' : '';

    echo "<select name='wfb_button_position' class='wfb-button-position'>
        <option value='right' " . esc_html($selectedRight) . ">" . esc_html__('Right', 'wfb-utm') . "</option>
        <option value='left' " . esc_html($selectedLeft) . ">" . esc_html__('Left', 'wfb-utm') . "</option>
    </select>";
}

function wfb_utm_phone_input()
{
    $value = sanitize_text_field(get_option('wfb_phone_number', ''));
    $value = preg_replace('/[()\s\-\+]/', '', $value);
    echo "<input type='text' name='wfb_phone_number' value='" . esc_html($value) . "' placeholder='5511999999999' class='wfb-phone-number' />";
}

function wfb_utm_text_input()
{
    $value = sanitize_text_field(get_option('wfb_text', ''));
    echo "<input type='text' name='wfb_text' value='" . esc_html($value) . "' placeholder='" . esc_html__('Hello, I would like to know more about the product', 'wfb-utm') . "' class='wfb-text' />";
}

function wfb_utm_utm_input()
{
    $value = sanitize_text_field(get_option('wfb_utm_params', ''));
    echo "<textarea name='wfb_utm_params' placeholder='" . esc_html__('&utm_source=wp&utm_medium=button', 'wfb-utm') . "' class='wfb-utm-params'>" . esc_html($value) . "</textarea>";
}

function wfb_utm_pages_input()
{
    $value = sanitize_text_field(get_option('wfb_target_pages', ''));
    echo "<textarea name='wfb_target_pages' placeholder='" . esc_html__('contact,support', 'wfb-utm') . "' class='wfb-target-pages'>" . esc_html($value) . "</textarea>";
}

function wfb_utm_settings_page()
{
    echo '<div class="wrap">';
    echo '<h1>' . esc_html__('WhatsApp Floating Button UTM - Settings', 'wfb-utm') . '</h1>';
    echo '<form method="post" action="options.php">';
    settings_fields('wfb-utm-settings-group');
    do_settings_sections('wfb-utm-settings');
    submit_button();
    echo '</form></div>';
}

function wfb_utm_add_whatsapp_button()
{
    $server_uri = !empty(sanitize_url(wp_unslash($_SERVER['REQUEST_URI']))) ? sanitize_url(wp_unslash($_SERVER['REQUEST_URI'])) : '';
    //$path = parse_url($server_uri, PHP_URL_PATH) ?? '';
    $path =  wp_parse_url($server_uri, PHP_URL_PATH);
    $phone = esc_attr(get_option('wfb_phone_number', ''));
    $text = esc_attr(get_option('wfb_text', ''));
    $utm = trim(get_option('wfb_utm_params', ''));
    $pages = get_option('wfb_target_pages', '');
    $slugs = array_map('trim', explode(',', $pages));
    $button_position = get_option('wfb_button_position', '') ?? 'right';

    if (empty($slugs[0]) || !in_array(basename($path), $slugs)) {
        wfb_utm_show_button($phone, $text, $utm, $button_position);
    }
}

function wfb_utm_show_button($phone, $text, $utm, $button_position)
{
    echo "<!-- WhatsApp Floating Button UTM -->";
    $url = 'https://api.whatsapp.com/send?phone=' . rawurlencode($phone) . "&text=" . rawurlencode($text) . $utm;
    echo '<a href="' . esc_url($url) . '" class="wfb-whatsapp-button wfb-button-' . esc_attr($button_position) . '" target="_blank" rel="noopener noreferrer"><i class="wfb-icon"></i></a>';
    echo "<!-- End WhatsApp Floating Button UTM -->";
}