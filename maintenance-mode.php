<?php
/**
 * Plugin Name: Maintenance Mode
 * Plugin URI: https://begatifydev.github.io/html-resume/
 * Description: Enables maintenance mode with countdown, logo upload, social media links, email subscriptions, and visitor analytics (IP + location).
 * Version: 1.4.1
 * Author: OLUWAFEMI OLUWATOBI BEST
 * Author URI: https://begatifydev.github.io/html-resume/
 * Text Domain: maintenance-mode
 */


if (!defined('ABSPATH')) exit;

function mm_enqueue_admin_scripts($hook) {
    if ($hook == 'settings_page_maintenance-mode-settings') {
        wp_enqueue_media();
    }
}
add_action('admin_enqueue_scripts', 'mm_enqueue_admin_scripts');

// 🔧 Create tables on activation
function mm_create_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $subscribers_table = $wpdb->prefix . 'mm_subscribers';
    $sql1 = "CREATE TABLE IF NOT EXISTS $subscribers_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        email varchar(255) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY email (email)
    ) $charset_collate;";

    $visitors_table = $wpdb->prefix . 'mm_visitors';
    $sql2 = "CREATE TABLE IF NOT EXISTS $visitors_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        ip_address varchar(100) NOT NULL,
        country varchar(100) DEFAULT '',
        city varchar(100) DEFAULT '',
        visited_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql1);
    dbDelta($sql2);
}
register_activation_hook(__FILE__, 'mm_create_tables');

// ✉️ Process email subscriptions
function mm_process_subscription() {
    if (isset($_POST['mm_subscribe_email']) && is_email($_POST['mm_subscribe_email'])) {
        global $wpdb;
        $email = sanitize_email($_POST['mm_subscribe_email']);
        $wpdb->insert($wpdb->prefix . 'mm_subscribers', ['email' => $email], ['%s']);
        wp_redirect(add_query_arg('subscribed', '1', $_SERVER['REQUEST_URI']));
        exit;
    }
}
add_action('init', 'mm_process_subscription');

// 📧 Notify subscribers when maintenance mode is disabled
function mm_notify_subscribers_on_disable($old_value, $value, $option) {
    if ($option == 'mm_enabled' && intval($old_value) === 1 && (!$value || intval($value) === 0)) {
        global $wpdb;
        $subscribers = $wpdb->get_results("SELECT email FROM {$wpdb->prefix}mm_subscribers");
        foreach ($subscribers as $subscriber) {
            wp_mail(
                $subscriber->email,
                'We are back online!',
                "Hello,\n\nOur website is now live again. Thank you for staying connected with us!\n\nVisit us at " . home_url(),
                ['Content-Type: text/plain; charset=UTF-8']
            );
        }
    }
}
add_action('update_option_mm_enabled', 'mm_notify_subscribers_on_disable', 10, 3);

// 🚧 Activate maintenance mode
function mm_activate_maintenance_mode() {
    if (get_option('mm_enabled')) {
        if (!current_user_can('manage_options') && !is_user_logged_in()) {
            header('HTTP/1.1 503 Service Temporarily Unavailable');
            header('Retry-After: 3600');

            global $wpdb;
            $ip_address = $_SERVER['REMOTE_ADDR'];

            // Get location via ip-api
            $response = wp_remote_get("http://ip-api.com/json/$ip_address");
            $country = $city = '';
            if (!is_wp_error($response)) {
                $data = json_decode(wp_remote_retrieve_body($response));
                if ($data && $data->status == 'success') {
                    $country = sanitize_text_field($data->country);
                    $city = sanitize_text_field($data->city);
                }
            }

            $wpdb->insert($wpdb->prefix . 'mm_visitors', [
                'ip_address' => $ip_address,
                'country' => $country,
                'city' => $city
            ]);

            include plugin_dir_path(__FILE__) . 'maintenance-template.php';
            exit;
        }
    }
}
add_action('template_redirect', 'mm_activate_maintenance_mode');

// 📝 Settings page
include plugin_dir_path(__FILE__) . 'settings-page.php';

// ✅ Subscribers & Visitors admin pages
function mm_add_admin_pages() {
    add_submenu_page('options-general.php', 'Maintenance Subscribers', 'Maintenance Subscribers', 'manage_options', 'mm-subscribers', 'mm_render_subscribers_page');
    add_submenu_page('options-general.php', 'Maintenance Visitors', 'Maintenance Visitors', 'manage_options', 'mm-visitors', 'mm_render_visitors_page');
}
add_action('admin_menu', 'mm_add_admin_pages');

function mm_render_subscribers_page() {
    global $wpdb;
    $subscribers = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mm_subscribers ORDER BY created_at DESC");
    ?>
    <div class="wrap">
        <h1>Maintenance Subscribers</h1>
        <table class="widefat">
            <thead><tr><th>ID</th><th>Email</th><th>Subscribed At</th></tr></thead>
            <tbody>
                <?php foreach ($subscribers as $s): ?>
                    <tr>
                        <td><?php echo esc_html($s->id); ?></td>
                        <td><?php echo esc_html($s->email); ?></td>
                        <td><?php echo esc_html($s->created_at); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php }

function mm_render_visitors_page() {
    global $wpdb;
    $visitors = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mm_visitors ORDER BY visited_at DESC");
    ?>
    <div class="wrap">
        <h1>Maintenance Visitors</h1>
        <table class="widefat">
            <thead><tr><th>ID</th><th>IP Address</th><th>Country</th><th>City</th><th>Visited At</th></tr></thead>
            <tbody>
                <?php foreach ($visitors as $v): ?>
                    <tr>
                        <td><?php echo esc_html($v->id); ?></td>
                        <td><?php echo esc_html($v->ip_address); ?></td>
                        <td><?php echo esc_html($v->country); ?></td>
                        <td><?php echo esc_html($v->city); ?></td>
                        <td><?php echo esc_html($v->visited_at); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php }
