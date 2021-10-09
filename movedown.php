<?php 

function sqltime($timestamp = false)
{
    if ($timestamp === false) {
        $timestamp = time();
    }

    return date('Y-m-d H:i:s', $timestamp);
}

$link = mysqli_connect("localhost", "root", "password", "bookmarks");
 
if (!$link) {
   echo "Error: Unable to connect to MySQL." . PHP_EOL;
   echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
   echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
   exit;
}

$IDs = $_POST['iddel'];

$sqltime = sqltime();

for( $i = 0; $i < count($IDs); $i++ ) {
   $query = "UPDATE bookmarks SET Bookmarked= '".$sqltime."' WHERE IMDb='".$IDs[$i]."'";
   $result = mysqli_query($link, $query);
}
   mysqli_close($link);
   header("Location: index.php");
?>
