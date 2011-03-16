<?php
class com_meego_planet_controllers_top
{
    public function __construct(midgardmvc_core_request $request)
    {
        $this->request = $request;
    }

    public function get_items(array $args)
    {
        $this->data['title'] = 'MeeGo Planet: Top Voted Posts';
        midgardmvc_core::get_instance()->head->set_title($this->data['title']);
        
        // FIXME: Query for top items instead
        $q = new midgard_query_select
        (
            new midgard_query_storage('com_meego_planet_item')
        );
        $q->add_order(new midgard_query_property('metadata.score'), SORT_DESC);

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
}
