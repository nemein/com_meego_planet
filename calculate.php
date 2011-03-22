<?php
class com_meego_planet_calculate
{
    private static function prepare_return($value, $modifier)
    {
        return (float) $value * $modifier;
    }
    
    public static function delicious($url, $modifier = 1)
    {
        $json = @file_get_contents('http://badges.del.icio.us/feeds/json/url/data?hash=' . md5($url));
        if (empty($json))
        {
            return self::prepare_return(0, $modifier);
        }
        
        $item_data = json_decode($json);
        if (!isset($item_data[0]->total_posts))
        {
            return self::prepare_return(0, $modifier);
        }
        
        return self::prepare_return($item_data[0]->total_posts, $modifier);
    }

    public static function twitter($url, $modifier = 1)
    {
        $json = @file_get_contents('http://search.twitter.com/search.json?q=' . urlencode($url));
        if (empty($json))
        {
            return self::prepare_return(0, $modifier);
        }
        
        $item_data = json_decode($json);
        if (!isset($item_data->results))
        {
            return self::prepare_return(0, $modifier);
        }
        
        return self::prepare_return(count($item_data->results), $modifier);
    }

    public static function facebook($url, $modifier = 1)
    {
        $fql = 'SELECT total_count from link_stat where url="' . rawurlencode($url) . '"';
        $json = @file_get_contents('http://api.facebook.com/method/fql.query?format=json&query=' . urlencode($fql));
        if (empty($json))
        {
            return self::prepare_return(0, $modifier);
        }
        
        $item_data = json_decode($json);  
        if (   !isset($item_data[0])
            || !isset($item_data[0]->total_count))
        {
            return self::prepare_return(0, $modifier);
        }
        
        return self::prepare_return($item_data[0]->total_count, $modifier);
    }

    public static function hackernews($url, $modifier = 1)
    {
        $json = @file_get_contents('http://api.ihackernews.com/getid?url=' . urlencode($url));
        $score = 0;
        if (empty($json))
        {
            return self::prepare_return($score, $modifier);
        }
        
        $post_ids = json_decode($json);
        if (   empty($post_ids)
            || !is_array($post_ids))
        {
            return self::prepare_return($score, $modifier);
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
        
        return self::prepare_return($score, $modifier);
    }

    public static function buzz($url, $modifier = 1)
    {
        $json = @file_get_contents('https://www.googleapis.com/buzz/v1/activities/count?alt=json&url=' . urlencode($url));
        if (empty($json))
        {
            return self::prepare_return(0, $modifier);
        }
        
        $item_data = json_decode($json);
        if (   !isset($item_data->data)
            || !isset($item_data->data->counts->$url))
        {
            return self::prepare_return(0, $modifier);
        }
        
        foreach ($item_data->data->counts->$url as $counts)
        {
            return self::prepare_return($counts->count, $modifier);
        }
    }

    public static function votes_for($url, $modifier = 1)
    {
        if (!class_exists('midgard_query_select'))
        {
            return self::prepare_return(0, $modifier);
        }
        
        try
        {
            $item = com_meego_planet_utils::get_item($url);
        }
        catch (Exception $e)
        {
            return self::prepare_return(0, $modifier);
        }

        $votes = com_meego_planet_votes::get($item);
        return self::prepare_return($votes['1'], $modifier);
    }

    public static function votes_against($url, $modifier = 1)
    {
        if (!class_exists('midgard_query_select'))
        {
            return self::prepare_return(0, $modifier);
        }
        
        try
        {
            $item = com_meego_planet_utils::get_item($url);
        }
        catch (Exception $e)
        {
            return self::prepare_return(0, $modifier);
        }

        $votes = com_meego_planet_votes::get($item);
        return self::prepare_return($votes['-1'], $modifier);
    }
    
    public static function reddit($url, $modifier = 1)
    {
        $json = @file_get_contents('http://www.reddit.com/api/info.json?url='.urlencode($url));

        if (empty($json))
        {
            return self::prepare_return(0, $modifier);
        }
        $item_data = json_decode($json);
        if (!isset($item_data->data))
        {
            return self::prepare_return(0, $modifier);
        }

        $sum = 0;
        foreach ($item_data->data->children as $record) {
            $sum += $record->data->score;
            $sum += $record->data->num_comments;
        }

        return self::prepare_return($sum, $modifier);
    }

    public function age(DateTime $published, $penalty = 0.1)
    {
        $article_age = round((time() - $published->getTimestamp()) / 3600);
        return self::prepare_return(-$article_age, $penalty);
    }
    
    public static function all($url)
    {
        $services = array
        (
            'facebook' => 0.5,
            'delicious' => 0.5,
            'twitter' => 0.6,
            'hackernews' => 0.7,
            'buzz' => 0.6,
            'votes_for' => 0.7,
            'votes_against' => -5,
        );

        return (float) array_reduce
        (
            array_map
            (
                function ($service, $modifier) use ($url)
                {
                    return call_user_func(__CLASS__ . "::{$service}", $url, $modifier);
                },
                array_keys($services),
                $services
            ),
            function ($x, $y)
            {
                return $x + $y;
            },
            0.0
        );
    }
}
