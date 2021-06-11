<?php

if (! defined('ABSPATH')) {
    require_once './../../../wp-load.php';
}

$message = isset($_GET['message']) ? $_GET['message'] : '';
$message = stripcslashes($message);

$args = apply_filters('wp_mail', [
    'to'          => '',
    'subject'     => '',
    'message'     => $message,
    'headers'     => '',
    'attachments' => '',
]);

echo $args['message'];
