<?php

require("../neo4jphp.phar");
use Everyman\Neo4j\Client,
    Everyman\Neo4j\Index\NodeIndex,
    Everyman\Neo4j\Index\RelationshipIndex,
    Everyman\Neo4j\Index\NodeFulltextIndex,
    Everyman\Neo4j\Node,
    Everyman\Neo4j\Batch,
    Everyman\Neo4j\Query\Row,
    Everyman\Neo4j\Cypher\Query;
    

$client = new Client();
$FBUsers = new NodeIndex($client, 'FBUsers');
$FBSports = new NodeIndex($client, 'FBSports');
$FBEnt = new NodeIndex($client, 'FBEnt');
$FBPolitics = new NodeIndex($client, 'FBPolitics');


function displaynews($newstitle, $newslink, $imagelink, $site_name, $newssnippet, $container, $doc){
            //echo gettype($description);
           /*preg_match('@src="([^"]+)"@', $description, $match);
            $parts = explode('<font size="-1">', $description);*/
         
            $new_item = $doc->createElement('div');
            $new_item->setAttribute('class', 'news-item');
            $new_image = $doc->createElement('div');
            $new_image->setAttribute('id', 'news-image');
            $image = $doc->createElement('img');
            if($imagelink != 'NA'){
              $image->setAttribute('src', $imagelink);
            }
            else{
              $image->setAttribute('src', '../PhotoNotAvailable.jpg'); 
            }
            $new_item->appendChild($new_image);
            $new_content = $doc->createElement('div');
            $new_content->setAttribute('id', 'news-content');
            $new_item->appendChild($new_content);
            $title = $doc->createElement('span');
            $title->setAttribute('id', 'title');
            $link = $doc->createElement('a', htmlspecialchars($newstitle, ENT_QUOTES));
            $link->setAttribute('href', $newslink);
            $link->setAttribute('target', '_blank');
            $source = $doc->createElement('span', htmlspecialchars($site_name, ENT_QUOTES));
            $source->setAttribute('id', 'source');
            $snippet = $doc->createElement('span', $newssnippet);
            $snippet->setAttribute('id', 'snippet');
            $title->appendChild($link);
            $new_content->appendChild($title);
            $new_content->appendChild($source);
            $new_content->appendChild($snippet);
            $new_image->appendChild($image);
            
            $container->appendChild($new_item);
            //echo $doc->saveHTML();

}

ob_start();
require_once('facebook.php');

  $config = array(
    'appId' => '472817279454904',
    'secret' => 'e6fc38f08a86eb01358f52a16d9ea8a9',
  );

  $facebook = new Facebook($config);
  $user_id = $facebook->getUser();
//  $facebook->destroySession();

/*<html>
  <head>
  	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
	<title>Facebook API Demo</title>
  </head>
  <body>*/
  
    if($user_id) {
      // We have a user ID, so probably a logged in user.
      $user_data = $facebook->api('/me','GET');
      
      $user = $FBUsers->findOne('fbid', $user_data['id']);
      if($user){    //user is present in the database. this is not the first time he is signing into the app. so use the details in DB.

          $queryString = "START curr_user=node:FBUsers(fbid = '". $user->getProperty('fbid') . "')
              MATCH (curr_user) -[:LIKES]-> (like_list)
              RETURN like_list";
          //echo $queryString;
          try{
            $query = new Query($client, $queryString);
          }
          catch(Exception $e){
            echo $e;
          }
          $result = $query->getResultSet();
          
          $doc = new DOMDocument;
          $doc->validateOnParse = true;
          $doc->loadHTMLFile("newsdisplay.html");
          $container = $doc->getElementById('news-container');
          //echo gettype($container);
          //print_r($result);
          //echo "<br><br>";
              foreach($result as $row) {
                //echo $row['fuid']->getProperty('fuid');
                //echo gettype($row);
                //remember, row is a node now. use it later to get to the news nodes
                //$item_news = $row->getRelationships(array('hasNEWS'));
                //"START x = node:node_auto_index(pageid={$row['pageid']->getProperty('pageid')})"

              $queryString = "START curr_page=node:". $row['pageid']->getProperty('category') ."(pageid = '". $row['pageid']->getProperty('pageid') . "')
              MATCH (curr_page) -[:hasNEWS]-> (news_list)
              RETURN news_list";

              try{
              $query = new Query($client, $queryString);
                }
          catch(Exception $e){
            echo $e;
          }
            $result_news = $query->getResultSet();
            foreach ($result_news as $n) {
              # code...
              //echo $n['fuid']->getProperty('title');
              //echo $n['fuid']->getProperty('site_title');
              //echo "<br>";
              displaynews($n['pageid']->getProperty('title'), $n['pageid']->getProperty('url'), $n['pageid']->getProperty('image'), $n['pageid']->getProperty('site_title')
              , $n['pageid']->getProperty('snippet'), $container, $doc);
            }
                  
    //            }
          //}
          
          }
          echo $doc->saveHTML();



      }
      else{         //user is signing in for the first time in the application.

        try {

        //make the user node first
        $new_user = $client->makeNode()->setProperty('fbid', $user_data['id'])->save();  
        $new_user->setProperty('name', $user_data['name'])->save();
        $FBUsers->add($new_user, 'fbid', $new_user->getProperty('fbid'));

        //now find the relevant likes
        $user_likes = $facebook->api('/me/likes','GET');
        
        $sports = array("Sports", "Athlete", "Sports league");
        $entertainment = array("Musician/band", "Actor/director", "Tv show", "Movie", "Music");
        $politics = array("Politician", "Political party");
        
        $doc = new DOMDocument;
        $doc->validateOnParse = true;
        $doc->loadHTMLFile("newsdisplay.html");
        $container = $doc->getElementById('news-container');
      	
        foreach($user_likes['data'] as $entry){
    		//echo "Name: ". $entry['name'] . "\tCategory:" . $entry['category'] . "<br/>";
        if(in_array($entry['category'], $sports)){

          $queryString = "START root=node:FBUsers(fbid = '". $new_user->getProperty('fbid') . "')
          CREATE UNIQUE root-[:LIKES]->(new_page{pageid:'" . $entry['id'] . "', name: '" . htmlspecialchars($entry['name'], ENT_QUOTES) . "', category:'FBSports'})
          RETURN new_page";
          //echo $queryString;
          try{
            $query = new Query($client, $queryString);
          }
          catch(Exception $e){
            echo $e;
          }
          $result = $query->getResultSet();
          foreach($result as $row) {
                //echo " ".$row['new_page']->getProperty('pageid'). "\n";
                $FBSports->add($row['new_page'], 'pageid', $row['new_page']->getProperty('pageid'));
          
          $curr_page = $FBSports->findOne('pageid', $row['new_page']->getProperty('pageid'));

          //echo "Name: " . $entry['name'] . "<br/>";
          $news = simplexml_load_file('http://news.google.com/news/search?q=' . $entry['name'] . '&output=rss');

          $j = 0; 
          //$feeds = array();
         foreach ($news->channel->item as $item) 
         {

            preg_match('@src="([^"]+)"@', $item->description, $match);
            $parts = explode('<font size="-1">', $item->description);
 
            $new_news = $client->makeNode()->setProperty('title', htmlspecialchars($item->title), ENT_QUOTES)->save();
            $new_news->setProperty('url', (string)$item->link)->save();
            $new_news->setProperty('site_title', strip_tags($parts[1]))->save();
            $new_news->setProperty('snippet', strip_tags($parts[2]))->save();
            if(isset($match[1])){
            //  $tmp = $match[1];
              $new_news->setProperty('image', 'http:' . $match[1])->save();
              displaynews((string)$item->title, (string)$item->link, 'http:' . $match[1], strip_tags($parts[1]), strip_tags($parts[2]), $container, $doc);
              }
            else{
              //$tmp = 'NA';
              $new_news->setProperty('image', 'NA')->save(); 
              displaynews((string)$item->title, (string)$item->link, 'NA', strip_tags($parts[1]), strip_tags($parts[2]), $container, $doc);
            }
            $curr_page->relateTo($new_news, 'hasNEWS')->save();   //new node made for the news and saved           
            $j++;
            if ($j == 1) {
              # code...
              break;
            }
        }
      }  
        /*echo '<pre>';
      print_r($feeds);
    echo '</pre>';*/
      


    }
    elseif(in_array($entry['category'], $entertainment)){

      /*$new_ent = $client->makeNode()->setProperty('pageid', $entry['id'])->save();
      $new_ent->setProperty('name', $entry['name'])->save();
      $FBEnt->add($new_ent, 'pageid', $new_ent->getProperty('pageid'));*/
        $queryString = "START root=node:FBUsers(fbid = '". $new_user->getProperty('fbid') . "')
          CREATE UNIQUE root-[:LIKES]->(new_page{pageid:'" . $entry['id'] . "', name: '" . htmlspecialchars($entry['name'], ENT_QUOTES) . "', category:'FBEnt'})
          RETURN new_page";
          //echo $queryString;
          try{
            $query = new Query($client, $queryString);
          }
          catch(Exception $e){
            echo $e;
          }
          $result = $query->getResultSet();
          foreach($result as $row) {
                //echo " ".$row['new_page']->getProperty('pageid'). "\n";
                $FBEnt->add($row['new_page'], 'pageid', $row['new_page']->getProperty('pageid'));

          $curr_page = $FBEnt->findOne('pageid', $row['new_page']->getProperty('pageid'));

          $news = simplexml_load_file('http://news.google.com/news/search?q=' . $entry['name'] . '&output=rss');

          $j = 0; 
          //$feeds = array();
         foreach ($news->channel->item as $item) 
         {

            preg_match('@src="([^"]+)"@', $item->description, $match);
            $parts = explode('<font size="-1">', $item->description);
 
            $new_news = $client->makeNode()->setProperty('title', htmlspecialchars($item->title), ENT_QUOTES)->save();
            $new_news->setProperty('url', (string)$item->link)->save();
            $new_news->setProperty('site_title', strip_tags($parts[1]))->save();
            $new_news->setProperty('snippet', strip_tags($parts[2]))->save();
            if(isset($match[1])){
            //  $tmp = $match[1];
              $new_news->setProperty('image', 'http:' . $match[1])->save();
              displaynews((string)$item->title, (string)$item->link, 'http:' . $match[1], strip_tags($parts[1]), strip_tags($parts[2]), $container, $doc);
              }
            else{
              //$tmp = 'NA';
              $new_news->setProperty('image', 'NA')->save(); 
              displaynews((string)$item->title, (string)$item->link, 'NA', strip_tags($parts[1]), strip_tags($parts[2]), $container, $doc);
            }
            $curr_page->relateTo($new_news, 'hasNEWS')->save();   //new node made for the news and saved           
            $j++;
            if ($j == 1) {
              # code...
              break;
            }
        }

      }

    }
    elseif(in_array($entry['category'], $politics)){ 

          /*$queryString = "START root=node:FBUsers(fbid = '". $new_user->getProperty('fbid') . "')
          CREATE UNIQUE root-[:LIKES]->(new_page{pageid:'" . $entry['id'] . "', name: '" . htmlspecialchars($entry['name'], ENT_QUOTES) . "', category:'FBPolitics'})
          RETURN new_page";
          //echo $queryString;
          try{
            $query = new Query($client, $queryString);
          }
          catch(Exception $e){
            echo $e;
          }
          $result = $query->getResultSet();
          foreach($result as $row) {
                //echo " ".$row['new_page']->getProperty('pageid'). "\n";
                $FBPolitics->add($row['new_page'], 'pageid', $row['new_page']->getProperty('pageid'));   
            
          $curr_page = $FBPolitics->findOne('pageid', $row['new_page']->getProperty('pageid'));
          $news = simplexml_load_file('http://news.google.com/news/search?q=' . $entry['name'] . '&output=rss');

          $j = 0; 
          //$feeds = array();
         foreach ($news->channel->item as $item) 
         {

            preg_match('@src="([^"]+)"@', $item->description, $match);
            $parts = explode('<font size="-1">', $item->description);
 
            $new_news = $client->makeNode()->setProperty('title', htmlspecialchars($item->title), ENT_QUOTES)->save();
            $new_news->setProperty('url', (string)$item->link)->save();
            $new_news->setProperty('site_title', strip_tags($parts[1]))->save();
            $new_news->setProperty('snippet', strip_tags($parts[2]))->save();
            if(isset($match[1])){
            //  $tmp = $match[1];
              $new_news->setProperty('image', 'http:' . $match[1])->save();
              displaynews((string)$item->title, (string)$item->link, 'http:' . $match[1], strip_tags($parts[1]), strip_tags($parts[2]), $container, $doc);
              }
            else{
              //$tmp = 'NA';
              $new_news->setProperty('image', 'NA')->save(); 
              displaynews((string)$item->title, (string)$item->link, 'NA', strip_tags($parts[1]), strip_tags($parts[2]), $container, $doc);
            }
            $curr_page->relateTo($new_news, 'hasNEWS')->save();   //new node made for the news and saved           
            $j++;
            if ($j == 1) {
              # code...
              break;
            }
        }
      }*/
    }
    else{
      //do nothing
      ;
  	}
//    echo $doc->saveHTML();
  }
    echo $doc->saveHTML();
    $logout_url = $facebook->getLogoutUrl(array(
    'next' => 'http://localhost/pn/facebook/logout.php',
    ));
	  echo "Please <a href='" . $logout_url . "'>Logout</a><br/>";
}       catch(FacebookApiException $e) {
        // If the user is logged out, you can have a 
        // user ID even though the access token is invalid.
        // In this case, we'll get an exception, so we'll
        // just ask the user to login again here.
        $login_url = $facebook->getLoginUrl(array(
		'scope' => 'user_likes',
		'next' => 'http://localhost/pn/facebook/login.php',
		'redirect_uri' => 'http://localhost/pn/facebook/index.php',
	)); 
        //echo 'Please <a href="' . $login_url . '">login.</a>';
 	header('Location: ' + $login_url);
        error_log($e->getType());
        error_log($e->getMessage());
      }
     }  
    } else {

      // No user, print a link for the user to login
      $login_url = $facebook->getLoginUrl(array(
		'scope' => 'user_likes',
		'next' => 'http://localhost/pn/facebook/login.php',
		'redirect_uri' => 'http://localhost/pn/facebook/index.php',
	)); 
      echo 'Please <a href="' . $login_url . '">login.</a>';
//	echo "Location: " . $login_url;
echo "<br/>";
	header('Location: ' . $login_url);

    }

  
?>
