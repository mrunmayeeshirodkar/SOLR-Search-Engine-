<?php
//get the q parameter from URL
$q=$_GET["q"];
$q = strtolower($q);
$q1=split (" ", $q);
$q_prefix="";
$count=count($q1);
for($y=0;$y<$count-1;$y++){
    $q_prefix=$q_prefix.$q1[$y]." ";
}
$url="http://localhost:8983/solr/myexample/suggest?wt=json&indent=true&q=".$q1[$count-1];
$json=file_get_contents($url);
echo $json;
?>