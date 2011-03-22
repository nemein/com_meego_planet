<?php
class com_meego_planet_controllers_vote
{
    public function __construct(midgardmvc_core_request $request)
    {
        $this->request = $request;
    }

    public function post_item(array $args)
    {
        midgardmvc_core::get_instance()->authorization->require_user();

        if (!isset($_POST['vote']))
        {
            throw new midgardmvc_exception_notfound("You did not specify your vote");
        }
        $item = $this->load_item($args);
   
        $vote_obj = com_meego_planet_votes::vote
        (
            $item,
            (int) $_POST['vote']
        );

        $this->data['votes'] = com_meego_planet_votes::get($item);
    }
    
    public function get_votes(array $args)
    {
        $item = $this->load_item($args);
        $this->data['votes'] = com_meego_planet_votes::get($item);
    }

    private function load_item(array $args)
    {
        try
        {
            $item = new com_meego_planet_item($args['item']);
        }
        catch (midgard_error_exception $e)
        {
            throw new midgardmvc_exception_notfound($e->getMessage());
        }
        return $item;
    }
}
