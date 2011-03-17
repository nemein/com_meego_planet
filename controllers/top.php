<?php
class com_meego_planet_controllers_top
{
    public function __construct(midgardmvc_core_request $request)
    {
        $this->request = $request;
    }

    public function get_items(array $args)
    {
        $this->data['title'] = 'MeeGo Planet: Popular Posts';
        midgardmvc_core::get_instance()->head->set_title($this->data['title']);
        
        $this->data['items'] = array_map
        (
            // Prepare all resulting items for display
            'com_meego_planet_utils::prepare_item_for_display',
            com_meego_planet_utils::get_items
            (
                function($q)
                {
                    // Primarily order by calculated score tuned by age
                    $q->add_order(new midgard_query_property('agedscore'), SORT_DESC);
                }
            )
        );

        midgardmvc_core::get_instance()->head->add_link
        (
            array
            (
                'rel' => 'alternate',
                'type' => 'application/rss+xml',
                'title' => $this->data['title'],
                'href' => midgardmvc_core::get_instance()->dispatcher->generate_url('index_rss', array(), $this->request)
            )
        );
    }
    
    public function get_feed(array $args)
    {
        // Read items from Content Repository
        $this->get_items($args);
        
        // Set up feed
        midgardmvc_core::get_instance()->component->load_library('Feed');
        $feed = new ezcFeed();
        $feed->title = $this->data['title'];
        $feed->description = '';
        
        $now = new DateTime();
        $feed->published = $now->format(DateTime::RSS);
        
        $link = $feed->add('link');
        $link->href = midgardmvc_core::get_instance()->dispatcher->generate_url('index', array(), $this->request);
        
        array_walk
        (
            $this->data['items'],
            function($item) use ($feed)
            {
                $feeditem = $feed->add('item');
                $feeditem->title = $item->title;
                $feeditem->description = $item->content;
                $feeditem->published = $item->published->format(DateTime::RSS);
                
                $author = $feeditem->add('author');
                $author->name = $item->firstname;
                
                $link = $feeditem->add('link');
                $link->href = $item->url;
            }
        );
        
        $this->data['feed'] = $feed;
    }
}
