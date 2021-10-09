function Fetch(id,imdb,i) {
$.ajax(
  'fetchimdb.php?&id=' + id,
  {
      success: function(data) {

      $.ajax(
      'savetodb.php?&image=' + data + '&imdb=' + imdb,
      {
        success: function(data) {

         $("#poster"+i).attr("src",data);
         $("#load"+i).hide();

      },
        error: function() {
           alert('There was some error saving Poster to DB!');
        }
      }
      );

      },
      error: function() {
        alert('There was some error performing the AJAX call!');
      }
   }
 );
}
