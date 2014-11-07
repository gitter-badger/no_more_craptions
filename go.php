<?php
require_once("inc/functions.php");
require_once("inc/youtube.class.php");

if(!check_var($_GET['id'])) {
    redirect("index.php?error=No+Video+ID");
}

$id = $_GET['id'];
if(!youtube_validate_id($id)) {
    redirect("index.php?error=Invalid+Video+ID");
}

$edit = check_var($_GET['edit']);

$video = new Youtube($id);
$info = $video->retrieve_info();
if($info == False) {
    redirect("index.php?error=Video+Doesn%27t+Exist");
}
$stream = $video->get_best_stream();

$caption_url = sprintf("ajax/get_caption.php?id=%s&type=vtt%s", $id, $edit ? "&edit=True" : "");

$caption = "<span class=\"text-success\"><b>Caption</b></span>";
$craption = "<span class=\"text-danger\"><b>Craption</b></span>";

$caption_type = $edit ? $caption : $craption;

?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-US">
    <?php require_once("inc/head.php"); ?>

    <body>
        <div class="wrapper">
            <?php require_once("inc/header.php"); ?>
            <div class="row">
                <div class="col-md-8 text-center">
                       <span class="<?php echo $edit ? "text-success" : "text-danger"; ?>"><big><b><?php echo $info['title']; ?></b></big></span>
                </div>
                <div class="col-md-4 text-center">
                        <?php echo "<span>ID: <b>".$id."</b> | ".$caption_type."</span>"; ?>
                </div>
            </div>
            <div class="row main">
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-12" id="video-container">
                            <video id="player" autoplay="true" controls preload="metadata">
                            <?php
                        		echo "<source src=\"".$stream['url']."\">\n";
                        		echo "<track id=\"player-track\" kind=\"captions\" src=\"".$caption_url."\" default></track>\n";
                        		
                             ?>
                        	</video>
                    	</div>
                	</div>
                    <div class="row">
                        <div class="col-md-offset-4 col-md-1">
                            <button type="submit" id="play-rate-down" class="form-control input-sm" data-toggle="tooltip" data-placement="bottom" title="Decrease speed rate"><span class="glyphicon glyphicon-minus-sign"></span></button>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" id="play-rate-reset" class="form-control input-sm">Reset</button>
                        </div>
                        <div class="col-md-1">
                            <button type="submit" id="play-rate-up" class="form-control input-sm" data-toggle="tooltip" data-placement="bottom" title="Increase speed rate"><span class="glyphicon glyphicon-plus-sign"></span></button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <hr class="low-margin"/>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <?php
                            echo "<b>".$caption_type."</b>: <b>".$id."</b><br/>";
                            echo "<big>Download this ".$caption_type." as:</big>";
                            $i = 0;
                            foreach($caption_format as $type) {
                                $i++;
                                echo '<a href="ajax/get_caption.php?id='.$id.($edit ? "&edit=True" : "").'&type='.$type.'&dl=True"><b>'.strtoupper($type).'</b></a>'.($i == sizeof($caption_format) ? "." : ", ");
                            }
                            ?>             
                        </div>
                    </div>
                </div>

                <div class="col-md-4 caption-editor-wrapper">
                    <div class="row">
                        <div class="col-md-4">
                            <a href="go.php?id=<?php echo $id; ?>" id="caption" class="btn btn-default btn-block btn-danger" data-toggle="tooltip" data-placement="bottom" title="Orginial Craption">Craption</a>
                        </div>
                        <div class="col-md-4">
                            <a href="go.php?id=<?php echo $id; ?>&edit=True" id="caption" class="btn btn-default btn-block btn-success" data-toggle="tooltip" data-placement="bottom" title="Correct Caption">Caption</a>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" id="settings" class="btn btn-default btn-block" data-toggle="modal" data-target="#settings-modal"><span class="glyphicon glyphicon-cog"></span> Settings</button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-11">
                            <div id="edit_area">
                                <div class="row">
                                    <div class="col-md-12">
                                        <span><b>Previous <?php echo $caption_type; ?>:</b></span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php echo '<textarea id="edit-before" class="form-control" cols="50" '.($edit ? "readonly=\"readonly\"" : "disabled").'></textarea>'; ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-5">
                                        <span><b>Current <?php echo $caption_type; ?>:</b></span>
                                    </div>
                                    <div class="col-md-2">
                                        <span id="edit-current-start">00.00</span>
                                    </div>
                                    <div class="col-md-1">
                                        <span><b>TO</b></span>
                                    </div>
                                    <div class="col-md-2">
                                        <span id="edit-current-end">00.00</span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php echo '<textarea id="edit-current" class="form-control"'.($edit ? "" : " disabled").'></textarea>'; ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <span><b>Next <?php echo $caption_type; ?>:</b></span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php echo '<textarea id="edit-after" class="form-control" '.($edit ? "readonly=\"readonly\"" : "disabled").'></textarea>'; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <hr class="low-margin"/>
                </div>
            </div>
            <div class="row">
                <div class="col-md-offset-1 col-md-3">
                    <button type="submit" id="help" class="btn btn-default" data-toggle="modal" data-target="#help-modal"><span class="glyphicon glyphicon-info-sign"></span> How does it works?</button>
                </div>
                <div class="col-md-offset-1 col-md-3">
                    <button type="submit" id="googleform" class="btn btn-default" data-toggle="modal" data-target="#googleform-modal"><span class="glyphicon glyphicon-pencil"></span> Report your user viewpoint</button>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <hr>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="settings-modal" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title">Settings</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-offset-3 col-md-6">
                            <label for="settings-jump-back-delay">Jump back delay when caption get saved:</label>
                            <input type="text" id="settings-jump-back-delay" name="settings-jump-back-delay" class="form-control"/>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-offset-3 col-md-6">
                            <label for="settings-jump-delay">Jump delay by using Shift + Left/Right:</label>
                            <input type="text" id="settings-jump-delay" name="settings-jump-delay" class="form-control"/>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-offset-3 col-md-6">
                            <label for="settings-caption-position">Caption poisition (not yet working):</label>
                            <select id="settings-caption-position" name="settings-caption-position" class="form-control">
                                <option value="top">Top</option>
                                <option value="bottom">Bottom</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" id="settings-modal-save" class="btn btn-primary" data-dismiss="modal">Save</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="googleform-modal" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title">Settings</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <iframe src="https://docs.google.com/forms/d/1dEFzKEM1CuhFkckDLB3D8Lonyl_jKfjwesBeY6ZU9lU/viewform?embedded=true" frameborder="0" border="1" marginheight="0" marginwidth="0" style="width: 100%; height: 400px;">Loading...</iframe>
                        </div>
                    </div>
            </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="help-modal" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title">Help</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <big><b>Here are some tips that will make it easier for you</b></big>
                            <ul>
                                <li>Press Shift + Enter in the caption editing area to save and update.</li>
                                <li>Press Shift + Space to toggle play and pause.</li>
                                <li>Press Shift + Left to go backward some seconds in time</li>
                                <li>Press Shift + Right to go forward some seconds in time</li>
                                <li>Press Shift + Bottom to go back to the beginning</li>
                                <li>Press Shift + Top to go to the end</li>
                            </ul>
                        </div>
                    </div>
            </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
        <?php require_once("inc/js.php"); ?>
    </body>

</html>
