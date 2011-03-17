<?php
class com_meego_planet_controllers_latest
{
    public function __construct(midgardmvc_core_request $request)
    {
        $this->request = $request;
    }

    public function get_items(array $args)
    {
        $this->data['title'] = 'Planet MeeGo: Latest blogs';
        
        // Query for latest items
        $q = new midgard_query_select
        (
            new midgard_query_storage('com_meego_planet_item')
        );
        $q->add_order(new midgard_query_property('metadata.published'), SORT_DESC);
        
        // Handle paging
        $this->check_page($args, $q);
        
        $q->execute();
        
        $this->data['items'] = array_map
        (
            function($item)
            {
                // TODO: Get author and avatar
                return $item;
            },
            $q->list_objects()
        );
    }
    
    private function check_page(array $args, midgard_query_select $q)
    {
    }
}
