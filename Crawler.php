<?php

    require 'Pair.php';
    require 'PageEvaluator.php';

    /**
     * A Template for a web crawler.
     *
     * @author You're back!
     */
    abstract class Crawler 
    {
        /* The url to begin crawling */
        protected $base_url;
        protected $evaluator;

        /** Make a new crawler
         * @param $url : The url to begin crawling
         */
        public function __construct($url)
        {
            $this->base_url = $url;
        }


        abstract public function findData();
        abstract public function formatData($data);

        /**
         * Executes the crawling session
         * @return all found data in page-presentable format
         */
        public function executeCrawl()
        {
            $data = $this->findData();
            return $this->formatData($data);
        }
    }

    class NewsCrawler extends Crawler
    {
        /* Number of Headline Hits. Default 25 */
        public $hits = 25;

        public function __construct($url, $num_hits)
        {
            parent::__construct($url);
            $this->hits = $num_hits;
            $this->evaluator = new NewsPageEvaluator;
        }


        public function findData()
        {
            //echo $this->hits;
            $result = array();
            $url_queue = array();
            array_push($url_queue, $this->base_url);
            $visited_queue = array();  
            $running_count = 0;

            while($running_count < $this->hits)
            {
                $url = array_shift($url_queue);
                //echo "Next Visit: " . $url . "<br>";
                $html = @file_get_contents($url);
                if($html !== FALSE && !isset($visited_queue[$url]))
                {
                    $visited_queue[$url] = TRUE;
                    $dom = new DOMDocument;
                    libxml_use_internal_errors(true);
                    $dom->loadHTML($html);
                    $xpath = new DOMXPath($dom);
                    $links = $xpath->query('//a');
                    $page_titles = $this->evaluator->evaluateHits($links, $url);
                    $next_urls = $this->evaluator->evaluateNextPages($links, $this->base_url, $visited_queue);
                    $url_queue = array_merge($url_queue, $next_urls);
                    $result = array_merge($result, $page_titles);
                    $running_count = $running_count + count($page_titles);
                }

            }
            //echo "Found : " . count($result) . "items <br>";
            return $result;
        }

        public function formatData($data) 
        {
            $result = '';
            $count = 0;
            foreach($data as $pair)
            {
                $result = $result . '<a href="' . $pair->key . '" >' . $pair->val . '</a> <br>';
                $count++;
                if($count == $this->hits)
                    break;
            }
            return $result;
        }
    }

    class TwitterCrawler extends Crawler
    {
        protected $num_tweets;

        public function __construct($url, $tweet_count)
        {
            parent::__construct($url);
            $this->num_tweets = $tweet_count;
        }

        public function findData() 
        {
            $result = array();
            $date_until = date("Y-m-d");
            $time_until = time();
            $time_from = $time_until - 60*60*24;
            $count = 0;
            while($count < $this->num_tweets)
            {
                $date_from = date("Y-m-d", $time_from);
                $date_until = date("Y-m-d", $time_until);
                $url = "https://twitter.com/search?l=&q=from%3ArealDonaldTrump%20since%3A". $date_from .
                            "%20until%3A" . $date_until . "&src=typd";
                $html = @file_get_contents($url);
                $dom = new DOMDocument;
                libxml_use_internal_errors(true);
                $dom->loadHTML($html);
                $xpath = new DOMXPath($dom);
                $tweets = $xpath->query('//div[@class="js-tweet-text-container"]/p');
                foreach($tweets as $tweet)
                {
                    //echo $tweet->nodeValue;
                    array_push($result, $tweet);
                    $count = $count + 1;
                }
                $time_until = $time_from;
                $time_from = $time_from - 60*60*24;
            }
            return $result;
        }

        public function formatData($data) 
        {
            $count = 0;
            $result = '<ol>';
            foreach($data as $d)
            {
                $result = $result . '<li>' . $d->nodeValue . '</li>';
                $count = $count + 1;
                if($count == $this->num_tweets)
                    break;
            }
            return $result;
        }
    }

?>
