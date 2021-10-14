<?php 
$total = 0;

function sqltime($timestamp = false) {
    if ($timestamp === false) $timestamp = time();
    return date('Y-m-d H:i:s', $timestamp);
}

function db_string($str) {

   global $link;

   return mysqli_real_escape_string($link, $str);

}

$link = mysqli_connect("localhost", "root", "password", "bookmarks");

if (!$link) {
   echo "Error: Unable to connect to MySQL." . PHP_EOL;
   echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
   echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
   exit;
}

$DelIDs = $_POST['iddel'];

for( $i = 0; $i < count($DelIDs); $i++ ) {
   $query = "DELETE FROM bookmarks WHERE IMDb='".$DelIDs[$i]."'";
   $result = mysqli_query($link, $query);
}

function saveToDb ($csv) {
	global $link, $msg;
   $i = 0;

   include("imdb.php");
   $j = new Imdb();

     foreach($csv as $Key=>$Value) {

        $Title = db_string($Value[0]);
        $Year = db_string($Value[1]);
        $IMDb = db_string($Value[2]);
        $Bookmarked = db_string($Value[3]);

        $ID = str_replace("http://www.imdb.com/title/", "", $IMDb);
        $ID = str_replace("https://www.imdb.com/title/", "", $ID);
        $ID = preg_replace("/\/.*/", "", $ID);

        $query = "SELECT Title FROM bookmarks WHERE IMDb LIKE '%".$ID."%'";
        $result = mysqli_query($link, $query);

        if (mysqli_num_rows($result) == 0) {

             if($ID) $mArr = $j->getMovieInfoById($ID);
              
             $Poster = $mArr[poster];
             mysqli_real_escape_string($Poster);
                
             $query = "INSERT INTO bookmarks (Title, Year, IMDb, Bookmarked, Poster) VALUES
                         ( '$Title' , '$Year', '$IMDb', '$Bookmarked', '$Poster')";
             mysqli_query($link, $query);
             $i++;
             if($i == 20) break;
        }
     }
   
   if($i) $msg = $i . " rows were loaded from file and saved to db. "; 
}

function saveImdbUrlToDb ($loadid) {
	global $link, $msg, $total;

   $ID = str_replace("http://www.imdb.com/title/", "", $loadid);
   $ID = str_replace("https://www.imdb.com/title/", "", $ID);
   $ID = preg_replace("/\/.*/", "", $ID);

   $query = "SELECT Title FROM bookmarks WHERE IMDb LIKE '%".$ID."%'";
   $result = mysqli_query($link, $query);

   if (mysqli_num_rows($result) == 0) {

      include("imdb.php");
      $j = new Imdb();

      if($ID) $mArr = $j->getMovieInfoById($ID);
              
      $Title = db_string($mArr[title]);
      $Year = db_string($mArr[year]);
      $IMDb = db_string($loadid);
      $Bookmarked = db_string(sqltime());
      $Poster = db_string($mArr[poster]);

      $query = "INSERT INTO bookmarks (Title, Year, IMDb, Bookmarked, Poster) VALUES
               ( '$Title' , '$Year', '$IMDb', '$Bookmarked', '$Poster')";
      mysqli_query($link, $query);
      $msg = $Title . "  was loaded from url and saved to db. ";
   }
   else $msg = $loadid . "  already exist. ";
}

// file loaded
$file = $_FILES['file'];
if($file['name'] == 'bookmarks.csv') {
   // read file
   $csv = array_map('str_getcsv', file($file['tmp_name']));
   saveToDb ($csv);
} elseif($file) {
	$msg = 'Wrong file selected!';
}	

// imdb call
$loadid = $_POST['imdb_url'];
if($loadid) {
   saveImdbUrlToDb ($loadid);
}

// search
$searchtitle = trim($_POST['title']);
$order = trim($_POST['order']);

$query = "SELECT * FROM bookmarks";
$total =   mysqli_query($link, $query);

$results_per_page = 48;
$number_of_page = ceil ($total->num_rows / $results_per_page);

if (!isset ($_GET['page']) ) {
   $page = 1;
} else {
   $page = $_GET['page'];
}
$page_first_result = ($page-1) * $results_per_page;

if($searchtitle) {
   $query = "SELECT * FROM bookmarks WHERE Title LIKE '%".$searchtitle."%' ORDER BY Bookmarked $order";
}else {
   $query = "SELECT * FROM bookmarks WHERE IMDb IS NOT NULL ORDER BY Bookmarked $order LIMIT " . $page_first_result . ',' . $results_per_page;
}   
$result = mysqli_query($link, $query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
<link rel="shortcut icon" href="../favicon.ico" />
   <title>Bookmarks</title>
   <link href="bk.css" rel="stylesheet" type="text/css" />
   <link href="../index.css" rel="stylesheet" type="text/css" />
   <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
   <script src="bookmarks.js" type="text/javascript"></script>
</head>

<body>
   <h1>Bookmarks <input type="button" value="Home" id="home"></h1> <?='<span style="color:yellow"> '.$msg.'</span>'?>
      <form id="form_search" action="index.php" method="post">    
         <input type="text" name="title" id="title" value="<?=$searchtitle?>"/>
         <label>Sort by:</label>
         <select id="order" name="order">
            <option value="ASC" <?php if ($order === 'ASC') echo 'selected' ?>>ASC</option>
            <option value="DESC" <?php if ($order === 'DESC') echo 'selected' ?>>DESC</option>
         </select>
         <input class='' type='submit' value='Search' />
         Showing <?=$result->num_rows?> / <?=$total->num_rows?>
      <input type="button" id="toggletools" value="Tools" class="submit" onclick="$('#tools').toggle();" />
      </form>
   <br>
  <div id="tools" style="display:none">   
   <form id="form_load" action="index.php" method="post" enctype="multipart/form-data">
     Load bookmarks.csv: <input type="file" name="file" id="file"> and save to db <input class='submit' type='submit' value='Submit' /><br>
     <hr />
   </form>

   <form id="form_insert" action="index.php" method="post">
     Enter new bookmark by IMDb url: <input type="text" name="imdb_url" id="imdb_url" value=""><input class='submit' type='submit' value='Submit' /><br>
     <hr />
   </form>
  </div>

  <form id="form_main" action="index.php" method="post">

<?php
   $i = 1;
?>   
   <input class='' type='submit' value='Move down' onclick="document.getElementById('form_main').action = 'movedown.php';" />      
   <input class='submit' type='submit' value='Remove' />
   <div class="pagination-box">
<?php for($pageN = 1; $pageN<= $number_of_page; $pageN++) {

         $first_page = ( ($pageN * $results_per_page) - $results_per_page ) + 1;

         if ( $pageN * $results_per_page < $total->num_rows)
            $last_page = ( $pageN * $results_per_page);
         else $last_page = $total->num_rows;

         if ( $pageN == $page) $current = "current-page";
         else $current = "";

         echo '<a class="pagination '. $current .'" href = "index.php?page=' . $pageN . '">' . $first_page . '-' . $last_page . '</a>';
      }
?>
   </div>
   <table class="grid">
   <tr>
<?php

   foreach($result as $bookmark) {

      if($bookmark['IMDb'] != 'IMDb') {
      	
      	$imdbid = str_replace("http://www.imdb.com/title/", "", $bookmark['IMDb']);
      	$imdbid = str_replace("https://www.imdb.com/title/", "", $bookmark['IMDb']);
         $imdbid = preg_replace("/\/.*/", "", $imdbid);
      	$Poster = $bookmark['Poster'];
?>      	
         <td class="bk">
         <table>
          <tr class="poster_unit"><td><input class="remove_unit" type='checkbox' id='iddel<?=$i?>' name='iddel[]' value='<?=$bookmark[IMDb]?>' title='Remove'/>
             <img id='poster<?=$i?>' src='<?=$Poster?>' class='poster' onclick='$("#iddel<?=$i?>").trigger("click");'></td></tr>
          <tr class="title_unit"><td><a target='_new' href='<?=$bookmark['IMDb']?>'><?=$bookmark['Title']?> - <?=$bookmark['Year']?></a>
             <span class="bk_unit"><?=date('d M, y',strtotime($bookmark['Bookmarked']))?></span></td></tr>
<?php    if(!$Poster) { ?>
            <tr class="load_unit"><td><input type='button' id="load<?=$i?>" value='Load poster' onclick='Fetch(`<?=$imdbid?>`,`<?=$bookmark["IMDb"]?>`,`<?=$i?>`);'/></td></tr>
<?php    } ?>
         </table>                          
         </td>
<?php
      }

   	if( $i % 6 === 0 ) echo '</tr><tr id="tr' . $i . '" >';
      $i++;
?>      

<?php          
   }
   echo "</tr></table>";
   echo "Total: " . ($i-1) . " <input type='submit' class='submit' value='Remove' />";   
   echo '</form>';
   
   mysqli_close($link);
?>
</body>
</html>
