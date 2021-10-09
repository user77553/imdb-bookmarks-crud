<?php

$Image = $_GET['image'];
$IMDb = $_GET['imdb'];

mysqli_real_escape_string($Image);

$link = mysqli_connect("localhost", "root", "password", "bookmarks");
 
if (!$link) {
   echo "Error: Unable to connect to MySQL." . PHP_EOL;
   echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
   echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
   exit;
}

$query = "UPDATE bookmarks SET Poster = '".$Image."' WHERE IMDb LIKE '%".$IMDb."%' AND (Poster IS NULL OR Poster = '')";
$result = mysqli_query($link, $query);
echo $Image;

mysqli_close($link);
?>
