<?php
// Ensure these variables are set if included directly
if (!isset($message)) $message = 'Our website is undergoing maintenance. We will be back soon.';
if (!isset($title)) $title = "We'll be back soon!";
if (!isset($countdown_date)) $countdown_date = 'July 10, 2025 12:00:00';
if (!isset($logo)) $logo = get_option('mm_logo');

// Fetch social links
$facebook = get_option('mm_facebook');
$instagram = get_option('mm_instagram');
$linkedin = get_option('mm_linkedin');

// Fetch visitor count
global $wpdb;
$visitor_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}mm_visitors");
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <title><?php echo esc_html($title); ?></title>
    <?php wp_head(); ?>
    <style>
        body { text-align: center; padding: 50px; font-family: Arial, sans-serif; background-color: #f2f2f2; }
        img { max-width: 200px; height: auto; margin-bottom: 20px; }
        h1 { font-size: 50px; margin-bottom: 10px; }
        p { font-size: 20px; }
        #countdown { font-size: 20px; margin-top: 30px; }
        #timer { font-weight: bold; font-size: 25px; }
        #visitor-count { margin-top: 30px; font-size: 18px; }
        form { margin-top: 30px; }
        input[type="email"] { padding: 10px; font-size: 16px; width: 250px; }
        input[type="submit"] { padding: 10px 20px; font-size: 16px; background-color: #333; color: #fff; border: none; cursor: pointer; }
        input[type="submit"]:hover { background-color: #555; }
        .subscribed-msg { color: green; font-weight: bold; margin-top: 20px; }
        #social-icons { margin-top: 30px; }
        #social-icons a { margin: 0 10px; text-decoration: none; font-weight: bold; color: #333; }
        #social-icons a:hover { color: #0073aa; }
    </style>
</head>
<body>

    <?php if ($logo): ?>
        <img src="<?php echo esc_url($logo); ?>" alt="Logo">
    <?php endif; ?>

    <h1><?php echo esc_html($title); ?></h1>
    <p><?php echo esc_html($message); ?></p>

    <div id="countdown">
        <h2>Site will be back in:</h2>
        <div id="timer"></div>
    </div>

    <div id="visitor-count">
        <p><strong>Total visitors so far:</strong> <?php echo esc_html($visitor_count); ?></p>
    </div>

    <form method="post">
        <p>Get notified when we are back:</p>
        <input type="email" name="mm_subscribe_email" placeholder="Enter your email" required>
        <input type="submit" value="Subscribe">
    </form>

    <?php if (isset($_GET['subscribed']) && $_GET['subscribed'] == '1'): ?>
        <div class="subscribed-msg">Thank you for subscribing!</div>
    <?php endif; ?>

    <!-- ✅ Social Media Icons -->
    <div id="social-icons">
        <?php if ($facebook): ?><a href="<?php echo esc_url($facebook); ?>" target="_blank">Facebook</a><?php endif; ?>
        <?php if ($instagram): ?><a href="<?php echo esc_url($instagram); ?>" target="_blank">Instagram</a><?php endif; ?>
        <?php if ($linkedin): ?><a href="<?php echo esc_url($linkedin); ?>" target="_blank">LinkedIn</a><?php endif; ?>
    </div>

    <?php wp_footer(); ?>

    <!-- Countdown Script -->
    <script>
        var countDownDate = new Date("<?php echo esc_js($countdown_date); ?>").getTime();
        var x = setInterval(function() {
            var now = new Date().getTime();
            var distance = countDownDate - now;

            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);

            document.getElementById("timer").innerHTML = days + "d " + hours + "h "
            + minutes + "m " + seconds + "s ";

            if (distance < 0) {
                clearInterval(x);
                document.getElementById("timer").innerHTML = "We are back online!";
            }
        }, 1000);
    </script>
</body>
</html>
