<?php
class com_meego_planet_calculate
{
    public static function delicious($url)
    {
        $json = file_get_contents('http://badges.del.icio.us/feeds/json/url/data?hash=' . md5($url));
        if (empty($json))
        {
            return 0;
        }
        
        $item_data = json_decode($json);
        if (!isset($item_data[0]->total_posts))
        {
            return 0;
        }
        
        return $item_data[0]->total_posts;
    }

    public static function twitter($url)
    {
        $json = file_get_contents('http://search.twitter.com/search.json?q=' . urlencode($url));
        if (empty($json))
        {
            return 0;
        }
        
        $item_data = json_decode($json);
        if (!isset($item_data->results))
        {
            return 0;
        }
        
        return count($item_data->results);
    }

    public static function facebook($url)
    {
        $fql = 'SELECT total_count from link_stat where url="' . rawurlencode($url) . '"';
        $json = file_get_contents('http://api.facebook.com/method/fql.query?format=json&query=' . urlencode($fql));
        if (empty($json))
        {
            return 0;
        }
        
        $item_data = json_decode($json);  
        if (   !isset($item_data[0])
            || !isset($item_data[0]->total_count))
        {
            return 0;
        }
        
        return $item_data[0]->total_count;
    }

    public static function hackernews($url)
    {
        $json = file_get_contents('http://api.ihackernews.com/getid?url=' . urlencode($url));
        $score = 0;
        if (empty($json))
        {
            return $score;
        }
        
        $post_ids = json_decode($json);
        if (   empty($post_ids)
            || !is_array($post_ids))
        {
            return $score;
        }
        
        foreach ($post_ids as $post_id)
        {
            $json = file_get_contents('http://api.ihackernews.com/post/' . $post_id);
            if (empty($json))
            {
                continue;
            }
            
            $post = json_decode($json);
            if (isset($post->commentCount))
            {
                $score = $score + $post->commentCount;
            }
            
            if (isset($post->points))
            {
                $score = $score + $post->points;
            }
        }
        
        return $score;
    }

}
