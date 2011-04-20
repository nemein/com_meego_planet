<?php
class com_meego_planet_fetch
{
    public static function get_feeds()
    {
        $q = new midgard_query_select
        (
            new midgard_query_storage('com_meego_planet_feed')
        );
        $q->execute();
        return $q->list_objects();
    }

    public static function fetch_feed(com_meego_planet_feed $feed, $index, $item_callback)
    {
        if (!is_callable($item_callback))
        {
            throw InvalidArgumentException('Item processing callback must be a valid function');
        }

        midgardmvc_core::get_instance()->component->load_library('Feed');
        try
        {
            $fetched = ezcFeed::parse($feed->feed);
        }
        catch (Exception $e)
        {
            midgardmvc_core::get_instance()->log(__CLASS__, $e->getMessage(), 'info');
            return false;
        }
        $items = $fetched->item;
        array_walk($items, $item_callback, $feed);
        self::remove_missing($items, $feed);
    }

    public static function remove_missing(array $items, com_meego_planet_feed $feed)
    {
        $urls = array_map
        (
            'com_meego_planet_fetch::get_item_link',
            $items
        );
        array_walk
        (
            com_meego_planet_utils::get_items_for_feed($feed),
            function ($item) use ($urls)
            {
                if (!in_array($item->url, $urls))
                {
                    $item->delete();
                }
            }
        );
    }

    public static function get_item_link(ezcFeedEntryElement $item)
    {
        return $item->link[0]->href;
    }

    public static function import_item(ezcFeedEntryElement $feed_item, $index, com_meego_planet_feed $feed)
    {
        if (!self::get_item_link($feed_item))
        {
            return;
        }   
        $item = self::get_item_by_url(self::get_item_link($feed_item));
        $item->feed = $feed->id;

        $dirty = false;
        if (self::set_item_value($item, 'title', $feed_item->title->text))
        {
            $dirty = true;
        }

        if (isset($feed_item->description)) {
            if (self::set_item_value($item, 'content', $feed_item->description->text))
            {
                $dirty = true;
            }
        }

        if (!is_null($feed_item->published))
        {
            if (self::set_item_value($item->metadata, 'published', $feed_item->published->date))
            {
                $dirty = true;
            }
        }
        elseif (!$item->guid)
        {
            if (self::set_item_value($item->metadata, 'published', new DateTime()))
            {
                $dirty = true;
            }
        }

        if (!$dirty)
        {
            return;
        }

        if (strlen($item->url))
        {
            if (!$item->guid)
            {
                // New item
                $item->create();
                return;
            }

            $item->update();
        }
    }

    private static function get_item_by_url($url)
    {
        $q = new midgard_query_select
        (
            new midgard_query_storage('com_meego_planet_item')
        );
        $q->add_order(new midgard_query_property('metadata.score'), SORT_DESC);
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
            // New item
            $item = new com_meego_planet_item();
            $item->url = $url;
            return $item;
        }

        $list_of_items = $q->list_objects();
        return $list_of_items[0];
    }

    private static function set_item_value($item, $property, $value)
    {
        if ($item->$property instanceof midgard_datetime)
        {
            if ($item->$property->getTimestamp() == $value->getTimestamp())
            {
                return false;
            }
            $item->$property->setTimestamp($value->getTimestamp());
            return true;
        }

        if ($item->$property == $value)
        {
            return false;
        }
        $item->$property = $value;
        return true;
    }
}
