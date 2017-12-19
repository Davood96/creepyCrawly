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
            $base_url = "http://www.cnn.com";
            $append_url = "http://www.cnn.com";
            
            $url_queue = array();
            array_push($url_queue, $base_url);
            $visited_queue = array();
            $TRUMP_COUNT = 25;
            $count = 0;
            $index = 0;
            $test = [0 => "a", 1 => "b"];
            
            while($count < $TRUMP_COUNT && $index < count($url_queue))
            {
                $url = array_splice($url_queue, 0, 1)[0];
                $visited_queue[$url] = TRUE;
                $html = @file_get_contents($url);
                if($html !== FALSE)
                {
                    $dom = new DOMDocument;
                    libxml_use_internal_errors(true);
                    $dom->loadHTML($html);
                    $xpath = new DOMXPath($dom);
                    $links = $xpath->query('//a');


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
                                $absolute_url = $url_link[0] == '/' ? ($append_url . $url_link) : $url_link;
                                if($headline_length > 29 && $found_trump)
                                {
                                    echo '<a href="' . $absolute_url . '" >' . $headline . '</a> <br>';
                                    $count++;
                                }
                                elseif(!isset($visited_queue[$absolute_url]))
                                {
                                    array_push($url_queue, $absolute_url);
                                    //echo count($url_queue) . "<br>";
                                }
                            }
                            //echo $link->nodeValue . " " . $absolute_url . "<br>";
                        }
                    }
                }
                //echo "<br>";
                
                $index++;
            }
        ?> 
     
    </body>
   
</html>
