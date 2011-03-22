<?php
class com_meego_planet_votes
{
    public static function vote(com_meego_planet_item $item, $vote)
    {
        midgardmvc_core::get_instance()->authorization->require_user();
        $valid_votes = array
        (
            -1,
            1
        );
        
        if (!in_array($vote, $valid_votes))
        {
            throw new InvalidArgumentException("Invalid vote value");
        }
        
        $vote_obj = self::get_user_vote($item);
        if ($vote_obj->vote != $vote)
        {  
            $vote_obj->vote = $vote;
            if ($vote_obj->guid)
            {
                $stat = $vote_obj->update();
            }
            else
            {
                $vote_obj->create();
            }
        }
        
        return $vote_obj;
    }

    public static function get(com_meego_planet_item $item)
    {
        $votes = array
        (
            '1' => 0,
            '-1' => 0,
            'user' => 0,
        );
        
        $q = new midgard_query_select
        (
            new midgard_query_storage('com_meego_planet_item_vote')
        );
        $q->execute();
        $vote_objs = $q->list_objects();

        $votes['1'] = array_reduce
        (
            $vote_objs,
            function ($current, $vote)
            {
                if ($vote->vote == 1)
                {
                    return $current + 1;
                }
                return $current;
            },
            0
        );

        $votes['-1'] = array_reduce
        (
            $vote_objs,
            function ($current, $vote)
            {
                if ($vote->vote == -1)
                {
                    return $current + 1;
                }
                return $current;
            },
            0
        );
        
        return $votes;
    }

    public static function get_user_vote(com_meego_planet_item $item)
    {
        midgardmvc_core::get_instance()->authorization->require_user();
        
        $q = new midgard_query_select
        (
            new midgard_query_storage('com_meego_planet_item_vote')
        );
        $q->set_constraint
        (
            new midgard_query_constraint
            (
                new midgard_query_property('item'),
                '=',
                new midgard_query_value($item->id)
            )
        );
        $q->set_constraint
        (
            new midgard_query_constraint
            (
                new midgard_query_property('user'),
                '=',
                new midgard_query_value(midgardmvc_core::get_instance()->authentication->get_person()->id)
            )
        );
        $q->execute();
        $objects = $q->list_objects();
        if (count($objects) > 0)
        {
            return new com_meego_planet_item_vote($objects[0]->guid);
        }
        
        $vote = new com_meego_planet_item_vote();
        $vote->item = $item->id;
        $vote->user = midgardmvc_core::get_instance()->authentication->get_person()->id;
        return $vote;
    }
}
