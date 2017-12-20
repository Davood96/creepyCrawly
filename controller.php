<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
    require 'Crawler.php';
    $crawler;
    $num_hits = $_GET["hits"];
    $selection = $_GET["submit"];
    
    if($selection == "CNN")
    {
        $crawler = new NewsCrawler("http://www.cnn.com", $num_hits);
    }
    else
    {
        $crawler = new TwitterCrawler("", $num_hits);
    }
    
    echo $crawler->executeCrawl();
      

?>