<?php
class com_meego_planet_controllers_feeds extends midgardmvc_core_controllers_baseclasses_crud
{
    public function load_object(array $args)
    {
        $this->object = new com_meego_planet_feed($args['feed']);

        midgardmvc_core::get_instance()->head->set_title($this->object->title);
    }
    
    public function prepare_new_object(array $args)
    {
        $this->object = new com_meego_planet_feed();
    }
    
    public function get_url_read()
    {
        return midgardmvc_core::get_instance()->dispatcher->generate_url
        (
            'feeds', array(), $this->request
        );
    }
    
    public function get_url_update()
    {
        return midgardmvc_core::get_instance()->dispatcher->generate_url
        (
            'feed_update', array
            (
                'feed' => $this->object->guid
            ),
            $this->request
        );
    }
}
