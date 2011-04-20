<?php
class com_meego_planet_controllers_people {
    public function __construct(midgardmvc_core_request $request) {
        $this->request = $request;
    }

    public function get_search(array $args) {
        $term = '%';
        if (isset($_GET['term'])) {
            $term = "{$_GET['term']}%";
        }

        $q = new midgard_query_select
        (
            new midgard_query_storage('midgard_person')
        );
        $q->set_constraint
        (
            new midgard_query_constraint
            (
                new midgard_query_property('firstname'),
                'LIKE',
                new midgard_query_value($term)
            )
        );
        
        $q->execute();
        $this->data = array_map(
                  function($person) {
                      return array(
                                   'value' => $person->id,
                                   'label' => $person->firstname
                                   );
                  },
                  $q->list_objects()
                                );
    }
}