<?php
function mm_render_settings_page() { ?>
    <div class="wrap">
        <h1>Maintenance Mode Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('mm_settings_group');
            do_settings_sections('mm_settings_group');
            ?>
            <table class="form-table">
                <tr>
                    <th>Enable Maintenance Mode</th>
                    <td><input type="checkbox" name="mm_enabled" value="1" <?php checked(1, get_option('mm_enabled'), true); ?> /></td>
                </tr>
                <tr>
                    <th>Maintenance Title</th>
                    <td><input type="text" name="mm_title" value="<?php echo esc_attr(get_option('mm_title')); ?>" size="50" /></td>
                </tr>
                <tr>
                    <th>Maintenance Message</th>
                    <td><input type="text" name="mm_message" value="<?php echo esc_attr(get_option('mm_message')); ?>" size="50" /></td>
                </tr>
                <tr>
                    <th>Countdown Date</th>
                    <td><input type="text" name="mm_countdown_date" value="<?php echo esc_attr(get_option('mm_countdown_date')); ?>" size="30" /></td>
                </tr>
                <tr>
                    <th>Upload Logo</th>
                    <td>
                        <input type="text" name="mm_logo" id="mm_logo" value="<?php echo esc_attr(get_option('mm_logo')); ?>" size="50" />
                        <input type="button" class="button" id="mm_logo_button" value="Upload Logo" />
                        <p class="description">Upload your brand logo to display on the maintenance page.</p>
                    </td>
                </tr>
                <tr>
                    <th>Facebook URL</th>
                    <td><input type="text" name="mm_facebook" value="<?php echo esc_attr(get_option('mm_facebook')); ?>" size="50" /></td>
                </tr>
                <tr>
                    <th>Instagram URL</th>
                    <td><input type="text" name="mm_instagram" value="<?php echo esc_attr(get_option('mm_instagram')); ?>" size="50" /></td>
                </tr>
                <tr>
                    <th>LinkedIn URL</th>
                    <td><input type="text" name="mm_linkedin" value="<?php echo esc_attr(get_option('mm_linkedin')); ?>" size="50" /></td>
                </tr>
            </table>
            <?php
            global $wpdb;
            $total_visitors = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}mm_visitors");
            ?>
            <h2>Total Visitors Recorded: <?php echo esc_html($total_visitors); ?></h2>

            <script>
            jQuery(document).ready(function($){
                var mediaUploader;
                $('#mm_logo_button').click(function(e){
                    e.preventDefault();
                    if (mediaUploader) {
                        mediaUploader.open();
                        return;
                    }
                    mediaUploader = wp.media.frames.file_frame = wp.media({
                        title: 'Choose Logo',
                        button: { text: 'Choose Logo' },
                        multiple: false
                    });
                    mediaUploader.on('select', function(){
                        var attachment = mediaUploader.state().get('selection').first().toJSON();
                        $('#mm_logo').val(attachment.url);
                    });
                    mediaUploader.open();
                });
            });
            </script>

            <?php submit_button(); ?>
        </form>
    </div>
<?php }

function mm_register_settings() {
    register_setting('mm_settings_group', 'mm_enabled');
    register_setting('mm_settings_group', 'mm_title');
    register_setting('mm_settings_group', 'mm_message');
    register_setting('mm_settings_group', 'mm_countdown_date');
    register_setting('mm_settings_group', 'mm_logo');
    register_setting('mm_settings_group', 'mm_facebook');
    register_setting('mm_settings_group', 'mm_instagram');
    register_setting('mm_settings_group', 'mm_linkedin');
}
add_action('admin_init', 'mm_register_settings');

function mm_add_settings_menu() {
    add_options_page('Maintenance Mode Settings', 'Maintenance Mode', 'manage_options', 'maintenance-mode-settings', 'mm_render_settings_page');
}
add_action('admin_menu', 'mm_add_settings_menu');
