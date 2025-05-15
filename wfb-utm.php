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
 * 
 */

 add_action('plugins_loaded', 'wfb_utm_load_textdomain');
// Load plugin textdomain for translations
function wfb_utm_load_textdomain() {
    load_plugin_textdomain(
        'wfb-utm',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages/'
    );
}

// Hook para carregar o CSS
function wfb_enqueue_styles()
{
    // Registra e carrega o CSS
    wp_enqueue_style(
        'wfb-style', // ID único do seu estilo
        plugin_dir_url(__FILE__) . 'assets/css/style.css', // Caminho até o CSS
        array(), // Dependências (se precisar, tipo array('bootstrap'))
        '1.0', // Versão do arquivo
        'all' // Mídia (all, screen, print, etc.)
    );
}

function wfb_enqueue_admin_styles()
{
    wp_enqueue_style(
        'wfb-admin-style',
        plugin_dir_url(__FILE__) . 'assets/css/admin-style.css',
        array(),
        '1.0'
    );
}

function wfb_create_menu()
{
    add_options_page(
        esc_html__('WhatsApp Floating Button UTM - Settings', 'wfb-utm'),
        esc_html__('WhatsApp Floating Button UTM', 'wfb-utm'),
        'manage_options',
        'wfb-utm-settings',
        'wfb_settings_page'
    );

    add_action('admin_init', 'wfb_settings_init');
}


// Hook for loading styles, textdomain, and admin menu
add_action('wp_enqueue_scripts', 'wfb_enqueue_styles');
add_action('admin_enqueue_scripts', 'wfb_enqueue_admin_styles');
//add_action('plugins_loaded', 'wfb_load_textdomain');
add_action('admin_menu', 'wfb_create_menu');




function wfb_settings_init()
{
    register_setting('wfb-settings-group', 'wfb_phone_number', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('wfb-settings-group', 'wfb_utm_params', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('wfb-settings-group', 'wfb_target_pages', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('wfb-settings-group', 'wfb_text', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('wfb-settings-group', 'wfb_button_position', ['sanitize_callback' => 'sanitize_text_field']);


    add_settings_section('wfb_section', esc_html__('Settings', 'wfb-utm'), null, 'wfb-settings');

    add_settings_field(
        'wfb_phone_number',
        esc_html__('WhatsApp phone number (with DDD and country code)', 'wfb-utm'),
        'wfb_phone_input',
        'wfb-settings',
        'wfb_section'
    );

    add_settings_field(
        'wfb_text',
        esc_html__('Text of service (optional)', 'wfb-utm'),
        'wfb_text_input',
        'wfb-settings',
        'wfb_section'
    );

    add_settings_field(
        'wfb_utm_params',
        esc_html__('Parameters UTM (optional)', 'wfb-utm'),
        'wfb_utm_input',
        'wfb-settings',
        'wfb_section'
    );

    add_settings_field(
        'wfb_target_pages',
        esc_html__('Not show button on pages (slugs separated by comma, e.g. contact, support) leave empty to appear on all pages', 'wfb-utm'),
        'wfb_pages_input',
        'wfb-settings',
        'wfb_section'
    );

    add_settings_field(
        'wfb_button_position',
        esc_html__('Position button (left or right)', 'wfb-utm'),
        'wfb_button_position_input',
        'wfb-settings',
        'wfb_section'
    );

}

function wfb_button_position_input()
{
    $value = sanitize_text_field(esc_attr(get_option('wfb_button_position', '')));
    $selectedLeft = $value == 'left' ? 'selected' : '';
    $selectedRight = $value == 'right' ? 'selected' : '';

    echo "<select name='wfb_button_position' class='wfb-button-position'>
    <option value='right' ".esc_html($selectedRight).">" . esc_html__('Right', 'wfb-utm') . "</option>
    <option value='left' ".esc_html($selectedLeft).">" . esc_html__('Left', 'wfb-utm') . "</option>
    </select>";
}

function wfb_phone_input()
{
    $value = sanitize_text_field(esc_attr(get_option('wfb_phone_number', '')));
    $value = preg_replace('/[()\s\-\+]/', '', $value);

    echo "<input type='text' name='wfb_phone_number' value='".esc_html($value)."' placeholder='5511999999999' class='wfb-phone-number' />";
}

function wfb_text_input()
{
    $value = sanitize_text_field(esc_attr(get_option('wfb_text', '')));
    echo "<input type='text' name='wfb_text' value='".esc_html($value)."' placeholder='" . esc_html__('Hello, I would like to know more about the product', 'wfb-utm') . "' class='wfb-text' />";
}

function wfb_utm_input()
{
    $value = sanitize_text_field(esc_attr(get_option('wfb_utm_params', '')));
    $value = $value == '' ? '' : trim($value);
    echo "<textarea  name='wfb_utm_params'  placeholder='" . esc_html__('&utm_source=wp&utm_medium=button', 'wfb-utm') . "' class='wfb-utm-params' >".esc_html($value)."</textarea>";
}

function wfb_pages_input()
{
    $value = sanitize_text_field(esc_attr(get_option('wfb_target_pages', ''))) ?? '';
    $value = $value == '' ? '' : trim($value);
    echo "<textarea  name='wfb_target_pages' placeholder='" . esc_html__('contact,support', 'wfb-utm') . "' class='wfb-target-pages' >".esc_html($value)."</textarea>";
}

function wfb_settings_page()
{
    ?>
    <div class="wrap">
        <h1><?php echo esc_html__('WhatsApp Floating Button UTM - Settings', 'wfb-utm'); ?> </h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('wfb-settings-group');
            do_settings_sections('wfb-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Add the floating button to the footer
add_action('wp_footer', 'wfb_add_whatsapp_button');

function wfb_add_whatsapp_button()
{
    $server_uri =  !empty(sanitize_url( wp_unslash($_SERVER['REQUEST_URI'], []))) ? sanitize_url( wp_unslash($_SERVER['REQUEST_URI'], [])) : '';
    $path = $server_uri ?? '';
    $phone = esc_attr(get_option('wfb_phone_number', ''));
    $text = esc_attr(get_option('wfb_text', ''));
    $utm = trim(get_option('wfb_utm_params', ''));
    $pages = get_option('wfb_target_pages', '');
    $slugs = array_map('trim', explode(',', $pages));
    $button_position = get_option('wfb_button_position', '') ?? 'right';
  

    // if empty, show on all pages
    if (empty($slugs[0])) {
        wfb_show_button($phone, $text, $utm, $button_position);
        return;
    }

    // if path is in list, do not show
    if (in_array(basename($path), $slugs)) {
        return;
    }

    // show on all other pages
    wfb_show_button($phone, $text, $utm, $button_position);

}

function wfb_show_button($phone, $text, $utm, $button_position)
{
    echo "<!-- WhatsApp Floating Button UTM -->";
    $url = 'https://api.whatsapp.com/send?phone=' . $phone . "&text=" . $text . $utm;
    echo '<a href="' . esc_url($url) . '" class="wfb-whatsapp-button wfb-button-' . esc_html($button_position) . '" target="_blank" rel="noopener noreferrer"><i class="wfb-icon"></i></a>';
    echo "<!-- End WhatsApp Floating Button UTM -->";
}
