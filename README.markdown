Social News Aggregator
======================

`com_meego_planet` is a social news aggregation component for Midgard MVC. It operates a bit like [Planet](http://www.planetplanet.org/) by allowing multiple RSS feeds to be aggregated together into one consistent list.

What is a major difference between our social aggregator and Planet is that we have two views - the traditional Planet-like list of new items, and a list of most popular items. This allows busy users to easily see by glance the most important blog posts in the community.

The importance of posts is determined by various [relevancy calculations](http://bergie.iki.fi/blog/calculating_news_item_relevance/), taking into account factors like:

* How many Tweets mention the item
* How many users have Liked or Shared the item on Facebook
* How many upvotes and comments the item has received on Hacker News
* How many times the item was bookmarked on Delicious
* The age of the post

## Setup

You need a working Midgard2 + Midgard MVC installation. On top of this, enable this component in your application manifest `components` section:

    com_meego_planet:
        - {type: github, user: nemein, repository: com_meego_planet, branch: master}
        
You also need a node on your site handled by `com_meego_planet`. Add this to the `nodes` section of your application manifest:

    title: My Planet
    content: <p>Welcome to my Planet</p>
    component: com_meego_planet

Then just `midgardmvc update` and log into your site to add some feeds you want to aggregate.

### Cron jobs

The Social News system comes with three cronjobs that you need to add to your cron configuration.

    php -c php.ini com_meego_planet/bin/update_feeds.php

This command fetches all configured feeds, and imports items from them into the content repository. A good schedule for running this might be every hour.

    php -c php.ini com_meego_planet/bin/update_scores.php

This command updates the Social Web scores of items. Run it couple of times per day.

    php -c php.ini com_meego_planet/bin/update_age.php

This command updates age calculations for all posts. Run it as often as is fit for your environment. For example every half hour.

## Background

This is a Midgard MVC port of [org.maemo.socialnews](http://trac.midgard-project.org/browser/branches/ragnaroek/midcom/org.maemo.socialnews), the Social News aggregator developed for [Maemo News](http://maemo.org/news/). This new version has been developed initially for [Planet MeeGo](http://wiki.meego.com/Web_infrastructure/Planet.meego.com).
