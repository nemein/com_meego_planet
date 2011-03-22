<?php
class com_meego_planet_controllers_vote
{
    private $item = null;

    public function __construct(midgardmvc_core_request $request)
    {
        $this->request = $request;
    }

    public function post_item(array $args)
    {
        midgardmvc_core::get_instance()->authorization->require_user();

        // GET will handle loading the item
        $this->get_item($args);
   
        $vote_obj = com_meego_planet_votes::vote
        (
            $this->item,
            (int) $_POST['vote']
        );

        // Refresh votes before sending
        $this->data['votes'] = com_meego_planet_votes::get($this->item);
    }
    
    public function get_item(array $args)
    {
        $this->item = com_meego_planet_utils::get_item($args['item']);
        $this->data['votes'] = com_meego_planet_votes::get($this->item);
    }
}
