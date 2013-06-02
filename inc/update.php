        <div id="update" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="doUpdate" aria-hidden="true">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h3 id="myModalLabel">Update your server...</h3>
            </div>
            <div class="modal-body">
                <p>You can get a more detailed list of available updates from <a href="./updates.php">here</a>.</p></br>
                <div align="center">
                    <a href="./update.php?s=minecraft_server" class="btn btn-success">Update Minecraft</a></br></br>
                    <a href="./update.php?s=craftbukkit" class="btn btn-success">Update Craftbukkit</a>
                </div>

            </div>
            <div class="modal-footer">
                <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
            </div>
        </div> 

        <div id="updateCont" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="doUpdateCont" aria-hidden="true">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h3 id="myModalLabel">Continue Updating Your Server...</h3>
            </div>
            <div class="modal-body">
                <?php if($_POST['action']=='update' || $_GET['c']) { ?>
				            <p>Your server has been updated.</p>
			            <div class="modal-footer">
				            <a class="btn btn-primary" href="dashboard.php">Continue</a>
			            </div>
            <?php } else { ?>
		            <form action="update.php" method="post" id="frm">
			            <input type="hidden" name="action" value="update">
			            <input type="hidden" name="source" id="source" value="<?php echo $_GET['s']; ?>">
				            <p>Are you sure you want to update your server to the latest version?</p>
				            <p>Selected update: <?php echo $_GET['s']; ?></p>
			            <div class="modal-footer">
				            <a class="btn" href="dashboard.php">Cancel</a>
				            <button class="btn btn-primary" type="submit">Update</button>
			            </div>
		            </form>
            <?php } ?>

            </div>
        </div> 