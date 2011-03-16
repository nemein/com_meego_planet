<?php
$filepath = ini_get('midgard.configuration_file');
$config = new midgard_config();
$config->read_file_at_path($filepath);
$mgd = midgard_connection::get_instance();
$mgd->open_config($config); 

$basedir = dirname(__FILE__) . '/../..';
require("{$basedir}/midgardmvc_core/framework.php");
$mvc = midgardmvc_core::get_instance("{$basedir}/application.yml");

array_walk
(
    // Get a list of feeds from the database
    com_meego_planet_fetch::get_feeds(),
    // Fetch each feed
    'com_meego_planet_fetch::fetch_feed',
    // Import each item in a feed
    'com_meego_planet_fetch::import_item'
);
