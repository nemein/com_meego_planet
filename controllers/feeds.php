<?php
class com_meego_planet_controllers_feeds extends midgardmvc_core_controllers_baseclasses_crud
{
    public function get_list(array $args)
    {
        $this->data['title'] = sprintf('%s: Aggregated feeds', $node = $this->request->get_node()->get_object()->title);
        midgardmvc_core::get_instance()->head->set_title($this->data['title']);
        
        $q = new midgard_query_select
        (
            new midgard_query_storage('com_meego_planet_feed')
        );
        $q->execute();
        $request = $this->request;
        $this->data['feeds'] = array_map
        (
            function($feed) use ($request)
            {
                $feed->editurl = '';
                $feed->deleteurl = '';
                if (   midgardmvc_core::get_instance()->authentication->is_user()
                    && midgardmvc_core::get_instance()->authentication->get_user()->is_admin())
                {
                    $feed->editurl = midgardmvc_core::get_instance()->dispatcher->generate_url
                    (
                        'feed_update', array
                        (
                            'feed' => $feed->guid
                        ),
                        $request
                    );
                    $feed->deleteurl = midgardmvc_core::get_instance()->dispatcher->generate_url
                    (
                        'feed_delete', array
                        (
                            'feed' => $feed->guid
                        ),
                        $request
                    );
                }
                return $feed;
            },
            $q->list_objects()
        );
        
        $this->data['addurl'] = '';
        if (   midgardmvc_core::get_instance()->authentication->is_user()
            && midgardmvc_core::get_instance()->authentication->get_user()->is_admin())
        {
            $this->data['addurl'] = midgardmvc_core::get_instance()->dispatcher->generate_url('feed_create', array(), $this->request);
        }

        midgardmvc_core::get_instance()->head->enable_jquery();
        midgardmvc_core::get_instance()->head->enable_jquery_ui();
        midgardmvc_core::get_instance()->head->add_jsfile(MIDGARDMVC_STATIC_URL . '/com_meego_planet/feeds.js');
    } 

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

    public function load_form()
    {
        $this->form = midgardmvc_helper_forms_mgdschema::create($this->object, false);
    }
}
