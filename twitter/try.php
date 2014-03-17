<?php

/*$doc = new DOMDocument;
$doc->validateOnParse = true;
$doc->loadHTMLFile("display.html");
$container = $doc->getElementById('news-container');
//$elem = doc->createElement();
$new_item = $doc->createElement('div');
$new_item->setAttribute('class', 'news-item');
//$new_item->setAttribute('id', 'abc');
//$desc = '<table><tbody><tr><td>asdhl</td><td>asdhl</td><td>asdhl</td></tr></tbody></table>';
//$new_item->loadHTML('<table><tbody><tr><td>asdhl</td><td>asdhl</td><td>asdhl</td></tr></tbody></table>');

//$new_item->appendChild($node);

//$node->setAttribute('class', 'news-item');
//$container->appendChild('<table><tbody><tr><td>asdhl</td><td>asdhl</td><td>asdhl</td></tr></tbody></table>');
$container->appendChild($new_item);
//$new_item->loadHTML('<table><tbody><tr><td>asdhl</td><td>asdhl</td><td>asdhl</td></tr></tbody></table>');
//echo "<table><tbody><tr><td>asdhl</td><td>asdhl</td><td>asdhl</td></tr></tbody></table>";
/*echo "<script>var x = document.getElementById('abc');
	alert(x);
	x.innerHTML = ".$desc.";
	alert(x.innerHTML);
</script>";*/
//echo $doc->saveHTML();
$news = simplexml_load_file('http://news.google.com/news/search?q=Messi&output=rss');

 
        $j = 0;	
        $arr = array();
        foreach ($news->channel->item as $item) 
        {
            array_push($arr, htmlentities($item->description));
        }
        //print_r($arr);
        //echo "<br><br/>";





?>
