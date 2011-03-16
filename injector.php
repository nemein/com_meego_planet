<?php
class com_meego_planet_injector
{
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
    }
}
