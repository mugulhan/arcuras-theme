<?php
// Temporary flush rewrite rules script
require_once('../../../wp-load.php');

if (class_exists('Gufte_Sitemap_Generator')) {
    $generator = new Gufte_Sitemap_Generator();
    $generator->flush_sitemap_rewrite_rules();
    echo "Rewrite rules flushed successfully!\n";
} else {
    echo "Sitemap generator class not found!\n";
}

flush_rewrite_rules(true);
echo "WordPress rewrite rules flushed!\n";
