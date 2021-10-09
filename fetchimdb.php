<?php

$ID = $_GET['id'];

include("imdb.php");

$i = new Imdb();
if($ID) $mArr = $i->getMovieInfoById($ID);

echo $mArr[poster];

?>