<?php

/*
 * Template for parsing an html doc
 */
abstract class PageEvaluator
{
    /**
     * Finds all intended targets in $html doc
     * @param $links some data pool
     * @param String $base_url  the root url
     */
    abstract public function evaluateHits($links, $base_url);
}

class NewsPageEvaluator extends PageEvaluator
{
    /**
     * Finds all intended targets in $html doc
     * @param DOMNodeList $links a list of all hyperlinks on a page
     * @param String $base_url  the root url
     * @return an array containing all wanted hits
     */
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
    
    /**
     * Finds the Next Pages to Visit
     * @param $links - the list of all links on a page
     * @param $base_url - the root url
     * @param $visited_queue - a queue of all the currently visisted pages
     * @return - an array of the next urls to visit
     */
    public function evaluateNextPages($links, $base_url, $visited_queue)
    {
        $result = array();
        foreach ($links as $link)
        {
            $url_link = $link->getAttribute('href');
            if(isset($url_link[0]))
            {
                $absolute_url = $url_link[0] == '/' ? ($base_url . $url_link) : $url_link;
                array_push($result, $absolute_url);
            }
        }
        return $result;
    }
}


?>

