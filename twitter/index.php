<?php

//require("../phar://neo4jphp.phar");
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
$twitterUsers = new NodeIndex($client, 'twitterUsers');
$twitterFollowing = new NodeIndex($client, 'twitterFollowing');

$ch = curl_init();
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,0); 



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

require 'tmhOAuth.php';
require 'tmhUtilities.php';
$tmhOAuth = new tmhOAuth(array(
  'consumer_key'    => 'Et0Xstcbix42hJMgMY97g',
  'consumer_secret' => 'iW9rnWEK1Yprt8mN4lITu7TXe4dhDMcEPkwRbQ4n2zE',
));

$here = tmhUtilities::php_self();
session_start();

function outputError($tmhOAuth) {
  //echo 'Error: ' . $tmhOAuth->response['response'] . PHP_EOL;
  tmhUtilities::pr($tmhOAuth);
}

// reset request?
if ( isset($_REQUEST['wipe'])) {
  header("Location: http://localhost/pn/");
  session_destroy();
  

// already got some credentials stored?
} elseif ( isset($_SESSION['access_token']) ) {
  $tmhOAuth->config['user_token']  = $_SESSION['access_token']['oauth_token'];
  $tmhOAuth->config['user_secret'] = $_SESSION['access_token']['oauth_token_secret'];

  $code = $tmhOAuth->request('GET', $tmhOAuth->url('1.1/account/verify_credentials'));
  //someone is signed in
  if ($code == 200) { 
    $resp = json_decode($tmhOAuth->response['response']);
    $screen_name = $resp->screen_name;
    //echo "Screen Name: " . $resp->screen_name . "</br>";
    //echo "User-ID: " . $resp->id_str . "</br>";
    $convr = ($resp->verified) ? "true" : "false";
    $user = $twitterUsers->findOne('tuid', $resp->id_str);
    if($user){  //user is present in database. So use the database
        //echo "user is present";
        /*$curr_user = $twitterUsers->findOne('tuid', $resp->id_str);
        //echo $curr_user->getProperty('screen-name');
        $user_follows = $curr_user->getRelationships(array('FOLLOWS'));
        //echo "<pre>" . $user_follows . "</pre>";
        foreach ($user_follows as $entry) {
          # code...
          echo $entry->getProperty('name');
        }*/
      //$match = $
          $queryString = "START curr_user=node:twitterUsers(tuid = '". $user->getProperty('tuid') . "')
              MATCH (curr_user) -[:FOLLOWS]-> (follow_list)
              RETURN follow_list";
          //echo $queryString;
          try{
            $query = new Query($client, $queryString);
          }
          catch(Exception $e){
          //  echo $e;
          }
            $result = $query->getResultSet();
            //print_r($result);
          $doc = new DOMDocument;
          $doc->validateOnParse = true;
          $doc->loadHTMLFile("display.html");
          $container = $doc->getElementById('news-container');
          //echo gettype($container);
          //print_r($result);
          //echo "<br><br>";
              foreach($result as $row) {
                //echo $row['fuid']->getProperty('fuid');
                //echo gettype($row);
                //remember, row is a node now. use it later to get to the news nodes
                //$item_news = $row->getRelationships(array('hasNEWS'));

                $queryString = "START curr_follower=node:twitterFollowing(fuid = '". $row['fuid']->getProperty('fuid') . "')
              MATCH (curr_follower) -[:hasNEWS]-> (news_list)
              RETURN news_list";

              try{
              $query = new Query($client, $queryString);
                }
          catch(Exception $e){
          //  echo $e;
          }
            $result_news = $query->getResultSet();
            foreach ($result_news as $n) {
              # code...
              //echo $n['fuid']->getProperty('title');
              //echo $n['fuid']->getProperty('site_title');
              //echo "<br>";
              displaynews($n['fuid']->getProperty('title'), $n['fuid']->getProperty('url'), $n['fuid']->getProperty('image'), $n['fuid']->getProperty('site_title')
              , $n['fuid']->getProperty('snippet'), $container, $doc);
            }
        //echo "<pre>" . $user_follows . "</pre>";
                //foreach ($item_news as $entry) {
          # code...
                //echo $entry->getProperty('title');
                //echo "<br><br>";

                //echo " ".$row['news_items']->getProperty('news_items')."<br><br>";
                //if(isset($row['news_items'])){
                    //$feeds = $row['news_items']->getProperty('news_items');
                /*    foreach ($feeds as $x) {
                      # code...
                      echo gettype($x);
                      displaynews($x, $container, $doc);
                    }*/
                //}
                //what I will be having is the array of news_results
                //for ($i=0; $i < 2; $i++) { 
                  # code...
                //foreach ($row['news_items'] as $x) {
                  # code...
                //echo $x;
                  //echo $result['news_items'][$i];
                  //this will be a string. pass it to displaynews function
 //                 foreach ($x['news_items'] as $y) {
                    # code...
                    //displaynews($x, $container, $doc);
   //               }
                  
    //            }
          //}
          
          }
          echo $doc->saveHTML();
    }
    else{ //user is not present in the database. create the database
      //echo "user is not present";
        //echo "Verified: " . $convr . "</br>";
    //echo "<h3>You are following users with the following User-ID's:</h3></br>";
    $new_user = $client->makeNode()->setProperty('tuid', $resp->id_str)->save();
    $new_user->setProperty('screen-name', $resp->screen_name)->save();
    $twitterUsers->add($new_user, 'tuid', $new_user->getProperty('tuid'));
    $code = $tmhOAuth->request('GET', $tmhOAuth->url('1.1/friends/ids'), array(
    'stringify_ids'=> "true",
    'screen_name'=> $resp->screen_name, 
    'user_id' => $resp->id_str  
    ));
        $user_ids = json_decode($tmhOAuth->response['response'], true);
//    $list_of_ids =  implode(",", $user_ids['ids']);
//    echo $list_of_ids;
    $chunks = array_chunk($user_ids['ids'], 90);
	//echo count($user_ids['ids']);
  //echo count($follow);
    $no_of_chunks = count($chunks);
    $doc = new DOMDocument;
    $doc->validateOnParse = true;
    $doc->loadHTMLFile("display.html");

    $container = $doc->getElementById('news-container');
    for ($i=0; $i < $no_of_chunks; $i++){ 
      $list_of_ids = implode(",", $chunks[$i]);
      //echo $list_of_ids;
      //echo "<br/>";
      $code = $tmhOAuth->request('GET', $tmhOAuth->url('1.1/users/lookup'), array(
    'user_id' => $list_of_ids //'352145373,1021419306,742143,183230911,41545005' //implode(',', $user_ids['ids'])
  ));
    $follow = json_decode($tmhOAuth->response['response'], true);
    //echo $follow[0]['name'];
    
    //echo $doc->saveHTML();  
    //dom->verifedonparse = true;
    foreach ($follow as $entry) {
      # code...
      if($entry['verified']){
          //echo gettype($entry['id']);
          $queryString = "START root=node:twitterUsers(tuid = '". $new_user->getProperty('tuid') . "')
          CREATE UNIQUE root-[:FOLLOWS]->(new_follower{fuid:'" . strval($entry['id']) . "', name: '" . htmlspecialchars($entry['name'], ENT_QUOTES) . "'})
          RETURN new_follower";
          //echo $queryString;
          try{
            $query = new Query($client, $queryString);
          }
          catch(Exception $e){
          //  echo $e;
          }
            $result = $query->getResultSet();
              foreach($result as $row) {
  //              echo " ".$row['new_follower']->getProperty('fuid')."\n";
                $twitterFollowing->add($row['new_follower'], 'fuid', $row['new_follower']->getProperty('fuid'));
      
        
       //$news_results = array();
       $news = simplexml_load_file('http://news.google.com/news/search?q=' . htmlspecialchars($entry['name']) . '&output=rss');

        //$feeds = array();
        $j = 0; 
        $curr_follower = $twitterFollowing->findOne('fuid', $row['new_follower']->getProperty('fuid'));
        //var_dump($news_results);
        foreach ($news->channel->item as $item) 
        {
          //array_push($news_results, htmlentities($item->description));
 //           echo $item->title . "<br/>";
            //echo $item->description;
            //echo "<br><br/>";
           //$node = $doc->createTextNode($item->description);
          //$container->appendChild($node);
            
            preg_match('@src="([^"]+)"@', $item->description, $match);
            $parts = explode('<font size="-1">', $item->description);
 
            /*$feeds[$i]['title'] = (string) $item->title;
            $feeds[$i]['link'] = (string) $item->link;
            if(isset($match[1]))
              $feeds[$i]['image'] = 'http:' . $match[1];
            $feeds[$i]['site_title'] = strip_tags($parts[1]);
            $feeds[$i]['story'] = strip_tags($parts[2]);*/

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
            $curr_follower->relateTo($new_news, 'hasNEWS')->save();   //new node made for the news and saved
            
            
            $j++;
            if ($j == 2) {
              # code...
              //$curr_follower = $twitterFollowing->findOne('fuid', $row['new_follower']->getProperty('fuid'));
              //echo $curr_follower;
              //$curr_follower->setProperty('news_items', $news_results)->save();
              break;
            }

            //here we have all desc in the news_results array. so add them to the node
         

        }
        //print_r($news_results);
        
        }
    
    
  //} 
} //else gets over here

}

}
echo $doc->saveHTML();
}
}else {
    outputError($tmhOAuth);
  }
// we're being called back by Twitter
} elseif (isset($_REQUEST['oauth_verifier'])) {
  $tmhOAuth->config['user_token']  = $_SESSION['oauth']['oauth_token'];
  $tmhOAuth->config['user_secret'] = $_SESSION['oauth']['oauth_token_secret'];

  $code = $tmhOAuth->request('POST', $tmhOAuth->url('oauth/access_token', ''), array(
    'oauth_verifier' => $_REQUEST['oauth_verifier']
  ));

  if ($code == 200) {
    $_SESSION['access_token'] = $tmhOAuth->extract_params($tmhOAuth->response['response']);
    unset($_SESSION['oauth']);
    header("Location: {$here}");
  } else {
    outputError($tmhOAuth);
  }
// start the OAuth dance
} elseif ( isset($_REQUEST['authenticate']) || isset($_REQUEST['authorize']) ) {
  $callback = isset($_REQUEST['oob']) ? 'oob' : $here;

  $params = array(
    'oauth_callback'     => $callback
  );

  if (isset($_REQUEST['force_write'])) :
    $params['x_auth_access_type'] = 'write';
  elseif (isset($_REQUEST['force_read'])) :
    $params['x_auth_access_type'] = 'read';
  endif;

  $code = $tmhOAuth->request('POST', $tmhOAuth->url('oauth/request_token', ''), $params);

  if ($code == 200) {
    $_SESSION['oauth'] = $tmhOAuth->extract_params($tmhOAuth->response['response']);
    $method = isset($_REQUEST['authenticate']) ? 'authenticate' : 'authorize';
    $force  = isset($_REQUEST['force']) ? '&force_login=1' : '';
    $authurl = $tmhOAuth->url("oauth/{$method}", '') .  "?oauth_token={$_SESSION['oauth']['oauth_token']}{$force}";
    echo '<p>To complete the OAuth flow follow this URL: <a href="'. $authurl . '">' . $authurl . '</a></p>';
    header("Location: {$authurl}");
  } else {
    outputError($tmhOAuth);
  }
}

?>
<ul>
  
  <!--<li><a href="?wipe=1">Start Over and delete stored tokens</a></li>-->
</ul>
