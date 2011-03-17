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
    // Get all items
    com_meego_planet_utils::get_items
    (
        /*function($q)
        {
            $q->toggle_readonly(false);
        },*/
        null,
        'com_meego_planet_item_with_score'
    ),
    function($item)
    {
        $score = com_meego_planet_calculate::all($item->url);
        if ($item->score != $score)
        {
            // FIXME: This is here until we can get QuerySelect out of read-only state
            $item = new com_meego_planet_item_with_score($item->guid);
            
            $transaction = new midgard_transaction();
            $transaction->begin();
            echo "Updating score of {$item->title} to {$score}\n";
            $item->score = $score;
            $item->update();
            $transaction->commit();
        }
    }
);
