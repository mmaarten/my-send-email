<?php

namespace My\SendEmail;

class App
{
    protected static $page_hook = null;

    public static function init()
    {
        add_action('init', [__CLASS__, 'loadTextdomain']);
        add_action('admin_menu', [__CLASS__, 'addAdminPage']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueueScripts']);
        add_action('wp_ajax_' . self::prefix('process'), [__CLASS__, 'process']);
    }

    public static function loadTextdomain()
    {
        load_plugin_textdomain('my-send-email', false, dirname(plugin_basename(MY_SEND_EMAIL_PLUGIN_FILE)) . '/languages');
    }

    public static function adminNotice($message, $type = 'info')
    {
        return sprintf(
            '<div class="notice notice-%1$s"><p>%2$s</p></div>',
            esc_attr($type),
            $message
        );
    }

    public static function enqueueScripts()
    {
        if (! self::isAdminPage()) {
            return;
        }

        wp_enqueue_script('my-send-email', plugins_url('script.js', MY_SEND_EMAIL_PLUGIN_FILE), ['jquery'], false, true);
        wp_enqueue_style('my-send-email', plugins_url('style.css', MY_SEND_EMAIL_PLUGIN_FILE));
    }

    public static function isAdminPage()
    {
        $screen = get_current_screen();

        return self::$page_hook && self::$page_hook === $screen->id;
    }

    public static function addAdminPage()
    {
        self::$page_hook = add_submenu_page(
            'tools.php',
            __('Send Email', 'my-send-email'),
            __('Send Email', 'my-send-email'),
            'manage_options',
            'my-send-email',
            [__CLASS__, 'renderAdminPage']
        );
    }

    public static function prefix($value)
    {
        return 'my-send-email-' . $value;
    }

    /**
     * @link https://developer.wordpress.org/reference/functions/wp_mail/
     */
    public static function getFromEmail()
    {
        $sitename = wp_parse_url(network_home_url(), PHP_URL_HOST);
        if ('www.' === substr($sitename, 0, 4)) {
            $sitename = substr($sitename, 4);
        }

        $from_name = 'wordpress@' . $sitename;

        return apply_filters('wp_mail_from', $from_name);
    }

    public static function getFromName()
    {
        return apply_filters('wp_mail_from_name', 'WordPress');
    }

    public static function renderAdminPage()
    {
        $from    = sprintf('%1$s <%2$s>', self::getFromName(), self::getFromEmail());
        $to      = wp_get_current_user()->user_email;
        $subject = '';
        $message = '';

        ?>

        <div id="<?php echo esc_attr(self::prefix('wrap')); ?>" class="wrap">

            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <div class="<?php echo esc_attr(self::prefix('row')); ?>">

                <div class="<?php echo esc_attr(self::prefix('col')); ?>">

                    <form method="post">

                        <?php wp_nonce_field('form', MY_SEND_EMAIL_NONCE_NAME); ?>

                        <input type="hidden" name="action" value="<?php echo esc_attr(self::prefix('process')); ?>">

                        <p>
                            <label for="<?php echo esc_attr(self::prefix('from')); ?>"><?php esc_html_e('Sender', 'my-send-email'); ?></label><br>
                            <input type="text" id="<?php echo esc_attr(self::prefix('from')); ?>" class="regular-text" name="from" value="<?php echo esc_attr($from); ?>" disabled>
                        </p>

                        <p>
                            <label for="<?php echo esc_attr(self::prefix('to')); ?>"><?php esc_html_e('Recipient', 'my-send-email'); ?></label><br>
                            <input type="text" id="<?php echo esc_attr(self::prefix('to')); ?>" class="regular-text" name="to" value="<?php echo esc_attr($to); ?>">
                        </p>

                        <p>
                            <label for="<?php echo esc_attr(self::prefix('subject')); ?>"><?php esc_html_e('Subject', 'my-send-email'); ?></label><br>
                            <input type="text" id="<?php echo esc_attr(self::prefix('subject')); ?>" class="regular-text" name="subject" value="<?php echo esc_attr($subject); ?>">
                        </p>

                        <p>
                            <label for="<?php echo esc_attr(self::prefix('message')); ?>"><?php esc_html_e('Message', 'my-send-email'); ?></label><br>
                            <textarea id="<?php echo esc_attr(self::prefix('message')); ?>" class="large-text" name="message" rows="10"><?php echo esc_textarea($message); ?></textarea>
                        </p>

                        <?php submit_button(__('Send', 'my-send-email')); ?>

                        <div class="<?php echo esc_attr(self::prefix('message')); ?>"></div>

                    </form>

                </div>

                <div class="<?php echo esc_attr(self::prefix('col')); ?>">

                    <iframe src="<?php echo esc_url(plugins_url('preview.php', MY_SEND_EMAIL_PLUGIN_FILE)); ?>" width="100%" height="250px" style="border: none;"></iframe>

                </div>

            </div>

        </div><!-- .wrap -->

        <?php
    }

    public static function process()
    {
        if (! wp_doing_ajax()) {
            return;
        }

        check_admin_referer('form', MY_SEND_EMAIL_NONCE_NAME);

        global $phpmailer;

        $to      = isset($_POST['to']) ? $_POST['to'] : '';
        $subject = isset($_POST['subject']) ? $_POST['subject'] : '';
        $message = isset($_POST['message']) ? $_POST['message'] : '';

        $success = wp_mail($to, $subject, $message);

        if ($success) {
            $response = self::adminNotice(__('Mail sent.', 'my-send-email'), 'success');
        } else {
            $response = self::adminNotice(
                sprintf(__('Mail not sent: %s', 'my-send-email'), $phpmailer->ErrorInfo),
                'error'
            );
        }

        wp_send_json($response);
    }
}
