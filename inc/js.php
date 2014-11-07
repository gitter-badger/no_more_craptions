<?php 
if($page == "go.php") {
    echo '<input type="hidden" id="youtube-id" value="'.$id.'"/>';
    if($edit) {
        echo '<input type="hidden" id="caption-edit" value="True"/>';
    }
}
?>

<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<?php if($page == "index.php") { ?>
<script src="assets/js/index.js"></script>
<?php } elseif($page == "go.php") { ?>
<script src="assets/js/script.js"></script>
<?php } ?>
