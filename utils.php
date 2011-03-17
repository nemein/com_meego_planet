<?php
class com_meego_planet_utils
{
    public static function get_items($cb_execution_start = null, $type = 'com_meego_planet_item_with_author')
    {
        $q = new midgard_query_select
        (
            new midgard_query_storage($type)
        );
        
        if ($cb_execution_start)
        {
            $q->connect('execution-start', $cb_execution_start);
        }

        $q->execute();
        return $q->list_objects();
    }
    
    public static function get_items_for_feed(com_meego_planet_feed $feed)
    {
        return com_meego_planet_utils::get_items
        (
            function($q) use ($feed)
            {
                $q->set_constraint
                (
                    new midgard_query_constraint
                    (
                        new midgard_query_property('feed'),
                        '=',
                        new midgard_query_value($feed->id)
                    )
                );
            },
            'com_meego_planet_item'
        );
    }
    
    public static function page_by_args(midgard_query_select $q, array $args)
    {
    }
    
    public static function prepare_item_for_display($item)
    {
        // TODO: Add avatar
        return $item;
    }
}
