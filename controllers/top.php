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
    }
}
