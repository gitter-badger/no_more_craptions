<?php
require_once("inc/functions.php");
require_once("inc/youtube.class.php");
$error_message = check_var($_GET['error'], True);
$id = check_var($_GET['youtube-id'], True);
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-US">
    <?php require_once("inc/head.php"); ?>

    <body>
        <div class="wrapper">
            <?php
                require_once("inc/header.php");
                if($error_message) {
                    echo '<div class="row"><div class="bg-danger col-md-offset-3 col-md-6 text-center"><span class="text-danger"><big><b>'.$error_message.'</b></big></span></div></div>';
                    echo '<div class="row"><div class="col-md-12"><hr class="low-margin"/></div></div>';
                }
            ?>
            <div class="row">
                <div class="col-md-offset-4 col-md-4 text-center">
                    <form role="form">
                        <div class="form-group row">
                            <label for="youtube-id"><big>Youtube URL</big></label>
                            <input type="text" class="form-control input-lg" id="youtube-id" name="youtube-id" value="<?php echo $id; ?>">
                        </div>
                    </form>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 text-center" id="youtube-video-button">
                </div>
            </div>
        </div>
    </body>
<?php require_once("inc/js.php"); ?>    
</html>
