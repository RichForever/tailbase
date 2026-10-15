<?php

/**
 * @package WordPress
 * @subpackage Tailbase
 * @since Tailbase 1.0.0
 */

header("HTTP/1.1 301 Moved Permanently");
header("Location: " . get_bloginfo("url"));
exit();
?>
