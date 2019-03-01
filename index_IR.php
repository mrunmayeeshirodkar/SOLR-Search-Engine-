<html xmlns="http://www.w3.org/1999/html">



<head>

    <title>PHP Solr Client Example</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <style rel="stylesheet">
        #autocomplete-container {
            margin: 1px auto;

        @include appearance(none);

        &:focus {
         @include background($focus-overlay);
         }

        width: 100%;
        padding: 20px 15px;
        border: $border-color;
        outline: none;
        color: $color;
        font-size: 20px;
        }
        }

        #suggestions {
            display: none;
            margin-top: -100px;
            color: black;

        li {
            padding: 20px 15px;
            border: blue;

        }
        }
    </style>
</head>



<?php
include 'SpellCorrector.php';
include_once('simple_html_dom.php');

ini_set ('memory_limit', '-1');

header('Content-Type:text/html; charset=utf-8');

$limit = 10;

$BoolVar = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;

$rank = array('sort' => 'pageRankFile desc');

$results = false;

$arr = array();



#Creating key value pairs for mapping

$mapping = fopen("URLtoHTML_latimes.csv", "r");

if($mapping == true){

    while($line = fgetcsv($mapping,0,",")){

        $key = $line['0'];

        $value = $line['1'];

        $arr[$key] = $value;

    }

}



fclose($mapping);



if($BoolVar !== false){

    require_once('solr-php-client/Apache/Solr/Service.php');

    $solrServ = new Apache_Solr_Service('localhost',8983,'solr/myexample');

    if(get_magic_quotes_gpc() == 1){

        $BoolVar = stripslashes($BoolVar);

    }

    try{

        if($_REQUEST['sort'] == 'solr'){

            $results = $solrServ -> search($BoolVar,0,$limit);

        } else {

            $results = $solrServ -> search($BoolVar,0,$limit, $rank);

        }

    } catch (Exception $e){

        die("{$e -> __toString()}");

    }

}


?>



<body style="color: #595959">

<script language="JavaScript">

    function addinpp(param) {
        document.getElementById("q").value = param;
        document.getElementById("suggestions").style.display = "none";
    }

    function getAutoRes() {
        var ansss = '';
        var xmlhttp = new XMLHttpRequest();
        var search = document.getElementById("q").value;
        var url = "autocomplete.php?q=" + search;
        var dataList = document.getElementById('suggestions');
        url = encodeURI(url);

        xmlhttp.onreadystatechange = function(){
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200){
                var jsonData = JSON.parse(xmlhttp.responseText);
                var split_text = search.split(" ");
                for (var ind = 0; ind < split_text.length - 1; ind++){
                    ansss += split_text[ind] + " ";
                }
                var val = 0;
                dataList.innerHTML = '';
                for (j in jsonData.suggest.suggest){
                    var numFounds = jsonData.suggest.suggest[j].numFound;
                    val += 1;
                    for (lm in jsonData.suggest.suggest[j].suggestions){
                        console.log(jsonData.suggest.suggest[j].suggestions[lm].term);
                        // var option = document.createElement('option');
                        // Set the value using the item in the JSON array.var myval = ansss + " " + jsonData.suggest.suggest[j].suggestions[lm].term;
                        var myval = ansss + jsonData.suggest.suggest[j].suggestions[lm].term;

                        if (split_text[0] != j.toString()) {
                            dataList.innerHTML += "<li onclick='addinpp(\"" + myval + "\")'>" + ansss + " " + jsonData.suggest.suggest[j].suggestions[lm].term + "</li>";

                        } else{
                            dataList.innerHTML += "<li onclick='addinpp(\"" + jsonData.suggest.suggest[j].suggestions[lm].term + "\")'>" + jsonData.suggest.suggest[j].suggestions[lm].term + "</li>";
                        }
                        // Add the <option> element to the <datalist>.
                        // dataList.appendChild(option);
                        console.log(jsonData.suggest.suggest[j].suggestions[lm].term);
                        val+=1;
                    }
                    console.log(jsonData.suggest.suggest);
                    // dataList.setAttribute("hidden", false);
                    if (val == 1){
                        break;
                    }
                }
                if (numFounds > 0) {
                    dataList.style.display = "block";
                } else {
                    dataList.style.display = "none";
                }

            }
            else{
                dataList.innerHTML = '';
            }
        };


        xmlhttp.open("GET", url, false);
        xmlhttp.send();

    }
</script>

<form accept-charset="utf-8" method="get">

    <div class="container">

        <div style="background: #FFFFFF  !important" class="jumbotron">

            <center>

                <h1 style="font-size: 400%">

                    <label for="q"><label style="color: #4e79a7">S</label><label style="color: #f28e2b">O</label><label style="color: #76b7b2">L</label><label style="color: #edc948">R</label> <label style="color: #4e79a7">S</label><label style="color: #969590">earch</label></label>

                </h1>
                <div id="autocomplete-container">
                <input id="q" name="q" type="text" autocomplete=off placeholder="Enter Your Query Here" onkeyup="getAutoRes()"  value="<?php echo htmlspecialchars($BoolVar, ENT_QUOTES, 'utf-8'); ?>" style="width: 70%; height: 6%; border-radius: 25px; border-color: #969590; border-style: double; text-align: center; font-size: larger; color: #706f6d" list="suggestions"/><br><br>
                <ul style=" display: none; width: 700px; list-style-type: none; margin-top: -30px; text-align: left; border: 1px solid gray; padding-left:10px; padding-top: 10px; padding-bottom: 10px;" id="suggestions">
                </ul>
                </div>
                <input type="radio" name="sort" style="margin-right: 0.5%; width: 2.1%; height: 2.1%;" value="solr" <?php if(!isset($_REQUEST['sort']) || $_REQUEST[ 'sort']=='solr' ) echo "checked"; ?>><label style="font-size: 135%; color: #969590">LUCENE</label>

                <input type="radio" name="sort" style="margin-left: 10%; margin-right: 0.5%; width: 2.1%; height: 2.1%;" value="pageRank" <?php if (!isset($_REQUEST['sort']) || $_REQUEST[ 'sort']=='pageRank' ) echo "checked"; ?>><label style="font-size: 135%; color: #969590">PageRank</label><br><br>

                <input type="submit" value="Search" style="width: 15%; height: 6%; border-radius: 10px; border-style: double; color: #706f6d; font-size: 130%; background-color: #EFEFEF">

            </center>

        </div>

    </div>

</form>



<?php
function removePrepositions($mytext,$query){

    $start_delim="/\b";
    $end_delim="\b/i";
    $query = ltrim($query);
    $query = rtrim($query);
    $propositions = explode(" ", $query);
    $i = 0;
    foreach ($propositions as $proposition){
        $propositions[$i] = $start_delim.$proposition.$end_delim;
        $i++;
    }
    $words = explode(" ", $query);

    if( count($propositions) > 0 ) {
        $j = 0;
        foreach($propositions as $exceptionPhrase) {
            $mytext = preg_replace($exceptionPhrase, "<b>".$words[$j]."</b>", trim($mytext));
            $j++;
        }
        $retval = trim($mytext);

    }
    return $retval;
}

if ($results) {

    $total = (int) $results->response->numFound;

    $start = min(1, $total);

    $end = min($limit, $total);

    $arr_w = split(" ", $BoolVar);
    $ans = '';
    for ($i = 0; $i< sizeof($arr_w) ; $i++){
        $ans += SpellCorrector::correct($arr_w[$i]);
    }

    ?>

    <div style="padding-left: 15%; padding-right: 15%">
	<label style="font-size: 135%; margin-top: -90px; color: red;"> <?php
        $arr_w = split(" ", $BoolVar);
        $myText = "";
        for ($i = 0; $i< sizeof($arr_w) ; $i++){
            $myText .= SpellCorrector::correct($arr_w[$i]) . " ";
        }

        //echo substr($myText,0,strlen($myText)-1);
//        echo substr($BoolVar,0,strlen($BoolVar));
        if (strtolower(trim($BoolVar)) != strtolower(trim(substr($myText,0,strlen($myText)-1)))){
            echo "Did you mean: <a href='index_IR.php?q=". substr($myText,0,strlen($myText)-1) ."&sort=" . $_REQUEST['sort'] . "'><b><i>".substr($myText,0,strlen($myText)-1);
        }
        ?></i></b></a></label><br>

        <label style="font-size: 135%; margin-top: -60px; color: #969590">Total Results: <?php echo $total;?></label><br>

        <label style="font-size: 135%; margin-top: -30px; color: #969590">Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?></label><br>

        <table class="table table-light">

        <?php

        $num = 1;

        foreach ($results->response->docs as $doc) {

            $docId = "N/A";
			$docVal = "N/A";
            $docLink="N/A";
            $docDesc = "N/A";
            $docTitle = "N/A";

            foreach ($doc as $field => $value) {

                if($field== "id" ){

                    $docId=$value;

                }

                if($field == "title"){

                    $docTitle=$value;

                }

                if($field == "og_description"){

                    $docDesc=$value;

                }

				if($field == "og_url"){
					$docVal=$value;
					if(is_array($docVal) == True){
						$docLink=$docVal[0];
					}
					else{
						$docLink = $docVal;
					}

				}

            }

            $file_contents = file_get_html($docId)->plaintext;
            libxml_use_internal_errors(true); //Prevents Warnings, remove if desired
            $dom = new DOMDocument();
            if (strlen(trim($file_contents)) > 0){
                $dom->loadHTML($file_contents);
                $body = "";
                foreach($dom->getElementsByTagName("body")->item(0)->childNodes as $child) {
                    $body .= $dom->saveHTML($child);
                }
                $body = strip_tags($file_contents);
                $sentences = explode(".", $body);
                $words = explode(" ", $BoolVar);
                $snippet = "";
                $text = "/";
                $start_delim="(?=.*?\b";
                $end_delim="\b)";
                foreach($words as $item){
                    $text=$text.$start_delim.$item.$end_delim;
                }
                $text=$text."^.*$/i";
                foreach($sentences as $sentence){
                    $sentence=strip_tags($sentence);
                    if (preg_match($text, $sentence)>0){
                        if (preg_match("(&gt|&lt|\/|{|}|[|]|\|\%|>|<|:)",$sentence)>0){
                            continue;
                        }
                        else{
                            if(strlen($snippet)<160){
                                $snippet = $snippet.$sentence.".";
                            } else {
                                break;
                            }
                        }
                    }
                }
                $words = preg_split('/\s+/', $BoolVar);
//            $snippet = htmlspecialchars($snippet, ENT_NOQUOTES, 'utf-8');
                $snippet = urldecode($snippet);

                if(strlen($snippet) == 0){
                    foreach($words as $item) {
                        $text = "/";
                        $text = $text . $start_delim . $item . $end_delim;
                        $text = $text . "^.*$/i";
                        foreach ($sentences as $sentence) {
                            $sentence = strip_tags($sentence);
                            if (preg_match($text, $sentence) > 0) {
                                if (preg_match("(&gt|&lt|\/|{|}|[|]|\|\%|>|<|:)", $sentence) > 0) {
                                    continue;
                                } else {
                                    if(strlen($snippet)<160){
                                        $snippet = $snippet.$sentence.".";
                                    } else {
                                        break;
                                    }
                                }
                            }
                        }
                        $words = preg_split('/\s+/', $BoolVar);
//                    $snippet = htmlspecialchars_decode($snippet, ENT_NOQUOTES, 'utf-8');
//                    $snippet = htmlspecialchars_decode($snippet);
                    }
                }

//                if(strlen($snippet) == 0) {
//                    $parasent = explode(".",$file_contents);
//                    foreach ($parasent as $sentence){
//                        if (preg_match("(&gt|&lt|\/|{|}|[|]|\|\%|>|<|:)", strip_tags($sentence)) < 1) {
////                    $snippet = htmlspecialchars_decode(strip_tags($sentence), ENT_NOQUOTES, 'utf-8');
//                            $snippet = strip_tags($sentence);
//                        }
//                    }
//                    $snippet = preg_replace('/[^A-Za-z0-9\-]/', ' ', $snippet);
//                }


                else {
                    $FirstWord = false;
                    $LastWord = false;
                    $startW = true;
                    foreach ($words as $items){
                        $items = rtrim($items);
                        $items = ltrim($items);
                        $text = "/";
                        $text = $text . $start_delim . $items . $end_delim;
                        $text = $text . "^.*$/i";
//                echo "<label>".$items."</label>";
//                echo "<label>".preg_match($text,$snippet)."</label>";
                        if (preg_match($text,$snippet) > 0){
                            while (strlen($snippet) > 160){
                                if($startW == true){
                                    $pos = strpos(strtolower($snippet), strtolower($items));
                                    $spacePos = strpos($snippet, " ");
                                    if ($spacePos + 1 <= $pos){
                                        $snippet = substr($snippet,$spacePos+1);
                                    } else {
                                        $snippet = "...".$snippet;
                                        $FirstWord = true;
                                        break;
                                    }
                                } else {
                                    $pos = strripos(strtolower($snippet), strtolower($items));
                                    $spacePos = strripos($snippet, " ");
                                    if ( $pos+strlen($items) <= $spacePos){
                                        $snippet = substr($snippet,0, $spacePos);
                                    } else {
                                        $snippet = $snippet."...";
                                        $LastWord = true;
                                        break;
                                    }
                                }

                                //echo "<label>".$pos." ".$spacePos." ".strlen($snippet)." ".$snippet."</label><br>";

                            }
                        }
                        if($FirstWord == true){
                            $startW = false;
                        }
                        if ($LastWord == true){
                            break;
                        }
                    }

                }

                if (strlen($snippet) > 160){
                    while (strlen($snippet) > 160){
                        $spacePos = strripos($snippet, " ");
                        if ($spacePos == 0){
                            $snippet = substr($snippet,0,160);
                        } else {
                            $snippet = substr($snippet,0,$spacePos);
                        }

                    }
                    $snippet = $snippet."...";
                }

                $snippet = ltrim($snippet);
                $snippet = rtrim($snippet);


                $snippet=removePrepositions($snippet,$BoolVar);

            }

            echo "<tr>";
			if($docLink == "N/A"){
				$docLink = $arr[trim(substr($docId,42))];
			}

            if(is_array($docTitle) == true){

                echo '<a target="_blank" style="color:#434547; font-size:140%" href='.$docLink.'>'.$docTitle[0].'</a><br>';

            }

            else{

                echo '<a target="_blank" style="color:#434547; font-size:140%" href='.$docLink.'>'.$docTitle.'</a><br>';

            }

            echo '<a style="font-size:130%" href='.$docLink.' target="_blank">'.$docLink.'</a><br>';

            echo "<label style='color: #595b5e; font-size: 120%'>".$docId."</label><br>";

            echo "<label style='color: #595b5e; font-size: 120%'>".trim($snippet)."</label><br>";

            echo "</tr><br>";

            $num += 1;} ?>

        </table>

    </div>

<?php } ?>

</body>

</html>
