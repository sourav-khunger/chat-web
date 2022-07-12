<?php
$dom = new DOMDocument();
libxml_use_internal_errors(true);
if(isset($_REQUEST['file']))
{
    $file=$_REQUEST['file'];
}
else{
    $file='chat';
}

$a='html/'.$file.'.html';
$dom->loadHTMLFile($a);

libxml_clear_errors();

$domx = new DOMXPath($dom);   
$entries = $domx->evaluate("//td");
$dateArray = $domx->evaluate("//p");
for($i=1;$i<2;$i++)
{
    $date=$dateArray[$i]->firstChild->nodeValue;
}

$arr = array();
foreach ($entries as $key => $entry) {
    
      $arr[$key]['message_position']=($key%2==0)?'left':'right';
      
      $tag=($entry->firstChild->firstChild->childElementCount>0)?$entry->firstChild->firstChild->firstChild->tagName:"";
      $data=($entry->firstChild->firstChild->childElementCount>0)?$entry->firstChild->firstChild->firstChild:$entry->firstChild->firstChild;
      $tag1=$entry->firstChild->lastChild->tagName;
      $arr[$key]['footnote_id']=($tag1=='sup')?$entry->firstChild->lastChild->nodeValue:'';
      $arr[$key]['footnote_value']='';

      $arr[$key]['message']=($tag=='img')?$data->getAttribute("src"):$data->nodeValue;
      $arr[$key]['message_type']=($tag=='img')?'image':'text';
}
$footnotes = $domx->evaluate("//div");
foreach($footnotes as $key=> $note)
{
    $footnoteArray[$key]['footnote_id']=$note->firstChild->firstChild->nodeValue;
    $footnoteArray[$key]['footnote_value']=$note->firstChild->firstChild->nextSibling->nodeValue;
}

$filteredArray = $arr;
array_walk($footnoteArray, function($be) use (&$filteredArray) {
    $done = false;
    array_walk($filteredArray, function(&$ce) use($be, &$done) {
        if ($ce['footnote_id'] == $be['footnote_id']) {
            $ce['footnote_value'] = $be['footnote_value'];
            $done = true;
        }
    });
    if ( ! $done) {
        array_push($filteredArray, $be);
    }
});
?>
<html>
<head>
<meta content="width=device-width, initial-scale=1" name="viewport" />
<link rel="stylesheet" type="text/css" href="assets/css/style.css">
<link rel="stylesheet" href="//code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
<div id="chat_container" style="display:none;">
<div class="inner-container message-container">
<div class="date"><?php echo $date; ?></div>
  <?php foreach($filteredArray as $array) { 
      $footnote_id='';
  if(!empty($array['message'])){
      $footnote_id = str_replace(array('[',']'),'',$array['footnote_id']); 
  ?>

<div class="message <?php echo $array['message_position'] ?>">
    <div class="message-text" id="message_<?php echo "$footnote_id"; ?>">
  <?php echo ($array['message_type']=='image')?"<img src='images/$file/$array[message]' />":"<p>$array[message]</p>";
  
  echo ($array['footnote_value']!='')?"<a href='#footnote_$footnote_id' class='footnote_targeting inner-message' target-section='footnote_$footnote_id'>$array[footnote_id]</a>":'';

?>
 </div>
 </div>

<?php } 
} ?>
</div>
<div id="footnote_container" style="display:none;">
    <div id="handle" class="ui-resizable-handle ui-resizable-n"></div>
    <h1 class="subtitle">Footnotes</h1>
    <?php  foreach($filteredArray as $array) {  
    if($array['footnote_value']!=""){
        $footnote_id = str_replace(array('[',']'),'',$array['footnote_id']);
    echo "<p id='footnote_$footnote_id' class='footnote_text' target-section='message_$footnote_id'>$array[footnote_value]</p>";
    }
    }
    ?>
</div>
</div>



<div id="loader"><div class="loader-gif"></div></div>
</body>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://code.jquery.com/ui/1.13.1/jquery-ui.js"></script>
  <script>
    jQuery(document).ready(function($){
        
    $("#footnote_container").resizable({
    autoHide: true,
    handles: "n",
    resize: function( event, ui ) {
            // parent
        var parent = ui.element.parent();
        var parent_height = parent.height();

        // Get the min value of height or 70% of parent height
        // Get the max value of height or 30% of parent height
        var height = Math.min(ui.size.height, parent_height * 0.7);
        height = Math.max(height, parent_height * 0.3);

        // Set height for resizable element, and do not change top position.
        // Instead the previous element - content container - will be adjusted.
        ui.size.height = height;
        ui.position.top = 0;

        // make the content container's height 100% of parent, less .resizable
        ui.element.prev('.message-container').height( parent_height - height );
    }
});
        
    addClassToToolTip();
    function addClassToToolTip(){
        $('.footnote_targeting').each(function(e){
          var sibling=$(this).siblings('img').prop("tagName");
          if(sibling=='IMG')
          {
             $(this).addClass('tooltip-image');
          }
        })
    }
     
   
 
        function hideLoader(){
        $("#chat_container").show();
        $("#loader").hide();
        $("#footnote_container").show();
        }
        
        const myTimeout = setTimeout(hideLoader, 2500);

        function myStopFunction() {
          clearTimeout(myTimeout);
        }
    
        
     $(".message-container").scroll(function() {
         var $this=$(window);
        var container = $('#footnote_container');
        $(this).find(".message-text").each(function(e){
            
        var distance = $(this).offset().top;
        
        if ( $this.scrollTop() >= distance ) {
         var target= $(this).children('.inner-message').attr('target-section'); 
         $(".footnote_text").removeClass('active');
         $("#"+target).addClass('active');
          
    var scrollTo = $("#"+target);
  
        // Calculating new position of scrollbar
        var position = scrollTo.offset().top 
                - container.offset().top 
                + container.scrollTop();
  
        // Setting the value of scrollbar
        
        container.scrollTop(position,500);
        } 
        
        });

    });
   
   
    // $("#footnote_container").scroll(function() {
    //      var $this=$("#footnote_container");
    //      var container = $('.message-container');
    //      $(this).find(".footnote_text").each(function(e){
            
    //      var distance = $(this).offset().top;
    //     if ( $this.scrollTop() >= distance ) {
    //      var target= $(this).attr('target-section'); 
    //      $(".message-text").removeClass('active');
    //      $("#"+target).addClass('active');
          
    //     var scrollTo = $("#"+target);
  
    //     // Calculating new position of scrollbar
    //     var position = scrollTo.offset().top 
    //             - container.offset().top 
    //             + container.scrollTop();
  
    //     // Setting the value of scrollbar
    //     container.scrollTop(position);
    
    //     } 
        
    //     });

    // });
     
    

    })
</script>
</html>

