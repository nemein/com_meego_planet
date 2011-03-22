<?php
class com_meego_planet_tests_calculate extends midgardmvc_core_tests_testcase
{
    private $urls = array
    (
        'http://bergie.iki.fi/blog/on_cross-project_collaboration/',
        'http://www.allaboutmeego.com/news/item/12684_Linpus_demos_MeeGo_based_table.php',
        'http://pvanhoof.be/blog/index.php/2011/03/09/a-replace-extension-for-trackers-sparqls-update',
    );
    
    public function assert_each_url($url, $index, $callback)
    {
        $score = call_user_func($callback, $url);
        $this->assertType('float', $score);
    }
    
    private function assert_urls($callback)
    {
        array_walk
        (
            // For each URL
            $this->urls,
            // Run assertions
            array($this, 'assert_each_url'),
            // With calculation
            $callback
        );
    }
    
    public function test_delicious()
    {
        $this->assert_urls('com_meego_planet_calculate::delicious');

        // Test also with an URL we know has bookmarks
        $score = com_meego_planet_calculate::delicious('http://rdfabout.com/intro/');
        $this->assertGreaterThanOrEqual(100, $score);
    }
    
    public function test_twitter()
    {
        $this->assert_urls('com_meego_planet_calculate::twitter');
    }
    
    public function test_facebook()
    {
        $this->assert_urls('com_meego_planet_calculate::facebook');
        
        // Test also with an URL we know has likes/shares
        $score = com_meego_planet_calculate::facebook('http://bergie.iki.fi/blog/ten_years_of_nemein/');
        $this->assertGreaterThanOrEqual(1, $score);
    }

    public function test_hackernews()
    {
        $this->assert_urls('com_meego_planet_calculate::hackernews');
        
        // Test also with an URL we know has been posted
        $score = com_meego_planet_calculate::hackernews('http://bergie.iki.fi/blog/introducing_the_midgard_create_user_interface/');
        $this->assertGreaterThanOrEqual(100, $score);
    }
    
    public function test_buzz()
    {
        $this->assert_urls('com_meego_planet_calculate::buzz');

        // Test also with an URL we know has been posted
        $score = com_meego_planet_calculate::hackernews('http://bergie.iki.fi/blog/introducing_the_midgard_create_user_interface/');
        $this->assertGreaterThanOrEqual(100, $score);
    }

    public function test_reddit()
    {
        $this->assert_urls('com_meego_planet_calculate::reddit');

        // Test also with an URL we know has been posted
        $score = com_meego_planet_calculate::reddit('http://www.reddit.com/');
        $this->assertGreaterThanOrEqual(10, $score);
    }

    public function test_age()
    {
        $penalty = com_meego_planet_calculate::age(new DateTime(), 1);
        $this->assertType('float', $penalty);
        $this->assertEquals(0.0, $penalty);
        
        $date = new DateTime();
        $date->setTimestamp(time() - 3600);
        $penalty = com_meego_planet_calculate::age($date, 0.25);
        $this->assertType('float', $penalty);
        $this->assertEquals(-0.25, $penalty);
    }
    
    public function test_all()
    {
        $this->assert_urls('com_meego_planet_calculate::all');
    }
}
