<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
        <?php
            require 'Crawler.php';
        
            $twitter_crawler = new TwitterCrawler("", 25);
            $tweets = $twitter_crawler->findData();
            //echo count($tweets);
            echo $twitter_crawler->formatData($tweets);
            /*$date_until = date("Y-m-d");
            $time_until = time();
            $time_from = $time_until - 60*60*24;
           
            $count = 0;
            $DESIRED = 25;
            while($count < $DESIRED)
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
                //echo "Found " . $tweets->length . "<br>";
                $done = FALSE;
                for($i=0; $i<$tweets->length && !$done; $i++)
                {
                    $tweet = $tweets->item($i);
                    $count = $count + 1;
                    $done = $count == $DESIRED;
                    echo $tweet->nodeValue . "<br><br>";
                    
                }
                $time_until = $time_from;
                $time_from = $time_from - 60*60*24;
            }*/
        ?>
    </body>
</html>
