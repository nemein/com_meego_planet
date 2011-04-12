?php
class com_meego_planet_utils
{
    public static function get_item($identifier)
    {
        if (mgd_is_guid($identifier))
        {
            return self::get_item_by_guid($identifier);
        }
        return self::get_item_by_url($identifier);
    }

    private static function get_item_by_guid($guid)
    {
        if (!mgd_is_guid($guid))
        {
            throw new InvalidArgumentException("Argument must be a valid GUID");
        }

        try
        {
            $item = new com_meego_planet_item($guid);
        }
        catch (midgard_error_exception $e)
        {
            throw new midgardmvc_exception_notfound($e->getMessage());
        }
        return $item;
    }

    private static function get_item_by_url($url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED))
        {
            throw new InvalidArgumentException("Argument must be a valid URL");
        }
        
        $q = new midgard_query_select
        (
            new midgard_query_storage('com_meego_planet_item')
        );
        $q->set_constraint
        (
            new midgard_query_constraint
            (
                new midgard_query_property('url'),
                '=',
                new midgard_query_value($url)
            )
        );
        
        $q->execute();
        if ($q->resultscount == 0)
        {
            throw new midgardmvc_exception_notfound("Item {$url} not found");
        }
        
        $objects = $q->list_objects();
        return $objects[0];
    }

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
    
    private static function get_user_by_author($author)
    {
        static $authors = array();
        if (isset($authors[$author]))
        {
            return $authors[$author];
        }
        
        // Query matching user so we get the username
        $person = new midgard_person($author);
        $authors[$author] = $person->firstname;
        return $authors[$author];
    }
    
    public static function prepare_item_for_display($item)
    {
        $username = self::get_user_by_author($item->author);
        $item->avatar = '';
        $item->avatarurl = '';
        if ($username)
        {
            try
            {
                $item->avatar = midgardmvc_core::get_instance()->dispatcher->generate_url('meego_avatar', array('username' => $username), '/');
                $item->avatarurl = "http://meego.com/users/{$username}";
            }
            catch (Exception $e)
            {
                midgardmvc_core::get_instance()->context->delete();
            }
        }
        
        return $item;
    }
}
