<?php

class Pair
{
    public $key;
    public $val;
    
    public function __construct($k, $v)
    {
        $this->key = $k;
        $this->val = $v;
    }
}

abstract class PageEvaluator
{
    abstract public function evaluateHits($html, $base_url);
    //abstract public function evaluateNextPages();
}

class NewsPageEvaluator extends PageEvaluator
{
    public function evaluateHits($links, $base_url)
    {
        $titles = array();
        $keys = array();
        $i=0;
        foreach($links as $link)
        {
            $keys[$i++] = $link->getAttribute('href');
        }
        $filled = array_fill_keys($keys, FALSE);
        foreach ($links as $link)
        {
            $url_link = $link->getAttribute('href');
            if(!$filled[$url_link])
            {
                $headline = $link->nodeValue;
                $filled[$url_link]=TRUE;
                $headline_length = strlen($headline);
                $found_trump = preg_match('/Trump/', $headline);
                if(isset($url_link[0]))
                { 
                    if($headline_length > 29 && $found_trump)
                    {
                        $absolute_url = $url_link[0] == '/' ? ($base_url . $url_link) : $url_link;
                        array_push($titles, new Pair($absolute_url, $headline));
                    }
                }
            }
        }
        return $titles;
    }
    
    
    public function evaluateNextPages($links, $base_url, $visited_queue)
    {
        $result = array();
        foreach ($links as $link)
        {
            $url_link = $link->getAttribute('href');
            if(isset($url_link[0]))
            {
                $absolute_url = $url_link[0] == '/' ? ($base_url . $url_link) : $url_link;
                //if(!isset($visited_queue[$absolute_url]))
                //{
                array_push($result, $absolute_url);
                    //echo count($url_queue) . "<br>";
                //}
            }
        }
        return $result;
    }
}

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
    
    /* Make a new crawler
     * @param $url : The url to begin crawling
     */
    public function __construct($url)
    {
        $this->base_url = $url;
    }
    
    abstract public function findData();
    abstract public function formatData($data);
    
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
    
    public function sayHi()
    {
        return "HI";
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
            $html = file_get_contents($url);
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
