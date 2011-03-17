<?php
class com_meego_planet_injector
{
    public function inject_process(midgardmvc_core_request $request)
    {
        static $connected = false;
        if ($connected)
        {
            return;
        }
        
        // Subscribe to content changed signals from Midgard
        midgard_object_class::connect_default
        (
            'com_meego_planet_feed',
            'action-delete-hook',
            function($feed)
            {
                array_walk
                (
                    com_meego_planet_utils::get_items_for_feed($feed),
                    function ($item)
                    {
                        $item->delete();
                    }
                );
            }
        );
        $connected = true;
    }

    /**
     * Add our own stuff to the templates
     */
    public function inject_template(midgardmvc_core_request $request)
    {
        // Ensure our elements are available also for other components
        $request->add_component_to_chain(midgardmvc_core::get_instance()->component->get('com_meego_planet'));

        // Replace the default MeeGo sidebar with our own
        $route = $request->get_route();
        $route->template_aliases['content-sidebar'] = 'cmp-show-sidebar';
        $route->template_aliases['main-menu'] = 'cmp-show-main_menu';

        midgardmvc_core::get_instance()->head->add_link
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDGARDMVC_STATIC_URL . '/com_meego_planet/planet.css'
            )
        );
    }
}
