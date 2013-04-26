<?php
require_once 'inc/lib.php';

session_start();
if (!$_SESSION['user'] || !$user = user_info($_SESSION['user'])) {
    // Not logged in, redirect to login page
    header('Location: .');
    exit('Not Authorized');
}
?><!doctype html>
<html>
    <head>
        <title>File Manager | Mine-Panel</title>
        <link rel="stylesheet" href="<?php echo KT_THEME_DIRECTORY; ?>/css/bootstrap.css">
        <link rel="stylesheet" href="<?php echo KT_THEME_DIRECTORY; ?>/css/bootstrap.min.css">
        <link rel="stylesheet" href="<?php echo KT_THEME_DIRECTORY; ?>/css/bootstrap-responsive.min.css">
        <link rel="stylesheet" href="<?php echo KT_THEME_DIRECTORY; ?>/css/docs.css">
        <meta name="author" content="">
        <script src="<?php echo KT_THEME_DIRECTORY; ?>/js/jquery-1.7.2.min.js"></script>
        <script src="<?php echo KT_THEME_DIRECTORY; ?>/js/bootstrap.min.js"></script>
        <script type="text/javascript">

            $(document).ready(function() {

                // Directory tree item click
                $('#dirtree').on('click', 'a', function() {

                    // Adjust styles
                    $('#dirtree a').parents('li').removeClass('active');
                    $('#dirtree a i').addClass('icon-folder-close').removeClass('icon-folder-open');

                    $(this).parents('li').addClass('active');
                    $(this).children('i').removeClass('icon-folder-close').addClass('icon-folder-open');

                    // Load directory
                    loaddir($(this).attr('href'));

                    // Prevent navigation
                    return false;
                });

                // File list item click
                $('#filelist').on('click', 'a', function(e) {

                    // If not holding Ctrl or Shift, clear selection
                    if (!e.ctrlKey && !e.shiftKey)
                        $('#filelist a').parents('li').removeClass('active');

                    // Add or remove from selection
                    $(this).parents('li').toggleClass('active');

                    // Enable/disable Edti and Delete buttons
                    if ($('#filelist li.active').length == 1) {
                        $('#btn-delete,#btn-edit,#btn-rename,#btn-dl,#btn-view').prop('disabled', false);
                    } else if ($('#filelist li.active').length > 1) {
                        $('#btn-delete').prop('disabled', false);
                        $('#btn-edit,#btn-rename,#btn-dl,#btn-view').prop('disabled', true);
                    } else {
                        $('#btn-delete,#btn-edit,#btn-rename,#btn-dl,#btn-view').prop('disabled', true);
                    }

                    // Prevent navigation
                    return false;
                });

                // File list item double click
                $('#filelist').on('dblclick', 'a', function() {

                    // Load directory
                    if ($(this).data('type') == 'dir')
                        loaddir($(this).attr('href'));

                    // Open file
                    if ($(this).data('type') == 'file')
                        window.location = 'edit.php?file=' + encodeURIComponent($(this).attr('href'));

                });

                // Clear selection on Esc
                $(document).on('keyup', function(e) {
                    if (e.which == 27 || e.keyCode == 27 || e.charCode == 27) {
                        $('#filelist li.active').removeClass('active');
                        $('#btn-delete,#btn-edit,#btn-rename,#btn-dl,#btn-view').prop('disabled', true);
                    }
                });

                // Add delete button handler
                $('#btn-delete').click(function() {
                    // Get files
                    window.selectedfiles = [];
                    $('#filelist li.active').each(function() {
                        window.selectedfiles.push($(this).children('a').attr('href'));
                    });

                    // Delete if confirmed
                    if (confirm('Are you sure you want to delete the selected files?')) {
                        $.post('ajax.php', {
                            req: 'delete',
                            files: window.selectedfiles
                        }, function(data) {
                            loaddir(window.lastdir);
                        }).error(function() {
                            alert('There was an error deleting your files.');
                        });
                    }
                });

                // Add edit button handler
                $('#btn-edit').click(function() {
                    window.location = 'edit.php?file=' + encodeURIComponent($('#filelist li.active a').attr('href'));
                });

                // Add rename button handler
                $('#btn-rename').click(function() {
                    if (newname = prompt('Enter a new name for the file:', basename($('#filelist li.active a').attr('href')))) {
                        $.post('ajax.php', {
                            req: 'rename',
                            path: $('#filelist li.active a').attr('href'),
                            newname: newname
                        }, function(data) {
                            loaddir(window.lastdir);
                        }).error(function() {
                            alert('There was an error deleting your files.');
                        });
                    }
                });

                // Add view button handler
                $('#btn-view').click(function() {
                    window.open('download.php?dl=0&file=' + encodeURIComponent($('#filelist li.active a').attr('href')));
                });

                // Add download button handler
                $('#btn-dl').click(function() {
                    window.open('download.php?dl=1&file=' + encodeURIComponent($('#filelist li.active a').attr('href')));
                });

                // Add upload button handler
                $('#btn-upload').click(function() {
                    $('#modal-upload').modal('show');
                });

                // Add directory context menu
                $('#dirtree').on('contextmenu', 'li:not(#home):not(.divider)', function(e) {
                    window.seldir = $(this).children('a').attr('href');
                    console.log('#dircontext: ' + seldir);
                    $('#dircontext').css({
                        top: e.pageY + 'px',
                        left: e.pageX + 'px'
                    }).show();
                    return false;
                });
                $('#dircontext').on('contextmenu', function() {
                    return false;
                });
                $(document).click(function() {
                    $('#dircontext').hide();
                    window.seldir = undefined;
                });

                // Directory context menu buttons
                $('#dirc-rename').click(function() {
                    if (newname = prompt('Rename directory ' + window.seldir, basename(window.seldir))) {
                        $.post('ajax.php', {
                            req: 'rename-dir',
                            dir: window.seldir,
                            new : newname
                        }, function(data) {
                            loaddir(window.lastdir);
                        });
                    }
                    return false;
                });
                $('#dirc-delete').click(function() {
                    if (confirm('Are you sure you want to delete this directory?\n' + window.seldir)) {
                        $.post('ajax.php', {
                            req: 'delete-dir',
                            dir: window.seldir
                        }, function(data) {
                            loaddir(window.lastdir);
                        })
                    }
                    return false;
                });
                $('#dircontext li a').click(function() {
                    $('#dircontext').hide();
                    window.seldir = undefined;
                    return false;
                });

                // Generate button tooltips
                $('button.ht').tooltip();

                // Load requested directory
                loaddir('<?php echo $_GET['dir'] ? $_GET['dir'] : '/'; ?>');

            });

            function loaddir(dir) {
                window.lastdir = dir;

                // Clear the file list
                $('#filelist').empty().addClass('loading');
                $('#btn-delete,#btn-edit,#btn-rename,#btn-dl,#btn-view').prop('disabled', true);
                $('#dirtree li:gt(2)').remove();

                // Load the directory contents
                $.post('ajax.php', {
                    req: 'dir',
                    dir: dir
                }, function(data) {

                    // Calculate path components
                    var lvl_array = window.lastdir.replace(/\/$/, '').split('/');

                    // Add the header breadcrumbs
                    $('#path').empty();
                    var lvl_current = '/';
                    for (var i = 0; i < lvl_array.length; i++) {
                        if (i) {
                            lvl_current += lvl_array[i] + '/';
                            $('#path').append('<button type="button" class="btn" onclick="loaddir(\'' + lvl_current + '\')">' + lvl_array[i] + '</button>');
                        } else
                            $('#path').append('<button type="button" class="btn" onclick="loaddir(\'/\')"><i class="icon-home"></i></button>');
                    }

                    // Add directory tree nodes
                    var dirtree = '';

                    // Add items to the directory tree and file list
                    var filelist = '';
                    for (var d in data.dirs) {
                        dirtree += '<li><a href="' + window.lastdir.replace(/\/$/, '') + '/' + data.dirs[d] + '"><i class="icon-folder-close"></i> ' + data.dirs[d] + '</a></li>';
                    }
                    for (var f in data.files) {
                        filelist += '<li><a href="' + window.lastdir.replace(/\/$/, '') + '/' + data.files[f] + '" data-type="file"><i class="icon-file"></i> ' + data.files[f] + ' <small class="pull-right">' + size_format(data.sizes[f]) + '</small><div class="clearfix"></div></a></li>';
                    }

                    // Add directory contents to document
                    $('#dirtree').append(dirtree);
                    $('#filelist').removeClass('loading').html(filelist);

                    // Select current directory
                    $('#dirtree li.active').removeClass('active');
                    if (window.lastdir == '/')
                        $('#home').addClass('active');

                    // Change upload directory
                    $('#iframe-upload').attr('src', 'uploader.php?dir=' + encodeURIComponent(window.lastdir));

                }, 'json').error(function() {
                    try {
                        console.log('Error loading directory "' + window.lastdir + '"')
                    } catch (ex) {
                    }
                });
            }

            function size_format(s) {
                if (s >= 1073741824)
                    s = Math.round(s / 1073741824 * 100) / 100 + ' GB';
                else
                if (s >= 1048576)
                    s = Math.round(s / 1048576 * 100) / 100 + ' MB';
                else
                if (s >= 1024)
                    s = Math.round(s / 1024 * 100) / 100 + ' KB';
                else
                    s = s + ' bytes';
                return s;
            }
            ;

            function basename(path, suffix) {
                var b = path.replace(/^.*[\/\\]/g, '');
                if (typeof(suffix) == 'string' && b.substr(b.length - suffix.length) == suffix)
                    b = b.substr(0, b.length - suffix.length);
                return b;
            }
        </script>
    </head>

    <body style="background-image: url('<?php echo KT_THEME_DIRECTORY; ?>/img/squares.png');">
        <div class="navbar navbar-inverse navbar-fixed-top">
            <div class="navbar-inner">
                <div class="container">
                    <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="brand" href="/" style="float: left; padding-right: 20px; color: #fff; margin-left: 0px;">Mine-Panel</a>
                    <div class="nav-collapse collapse">
                        <ul class="nav">
                            <li class="">
                                <a href="./dashboard.php">Dashboard</a>
                            </li>
                            <li class="">
                                <a href="./console.php">Console</a>
                            </li>
                            <li class="active">
                                <a href="./files.php">File Manager</a>
                            </li>
                            <?php if ($_SESSION['is_admin'] || $user['role'] == 'admin') { ?> <li class=""> <a href="admin.php">Administration</a> </li><?php } ?>
                        </ul>
                        <?php if ($user['ram']) { ?>
                            <div class="btn-group" style="float: right;">
                                <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                                    <img src="https://minotar.net/helm/<?php echo urlencode($user['user']); ?>/60.png" alt="<?php echo $user['user']; ?>" align="right" style="height: 20px; width: 20px; float: left; padding-right: 5px;"> <?php echo $user['user']; ?>
                                    <span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a href="index.php?logout">Logout</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container" style="padding-top: 40px;">
                <h3>File Manager</h3>
                <div class="row-fluid">
                    <div class="span3">
                        <div class="well sidebar-nav">
                            <ul class="nav nav-list" id="dirtree">
                                <li class="nav-header">Directories</li>
                                <li class="active" id="home"><a href="/"><span class="icon-home"></span> Home</a></li>
                                <li class="divider"></li>
                                <!--<li><a href="/sub-item"><i class="icon-folder-close"></i> sub-directory</a></li>
                                <li><a href="/sub-item/3rd-level item">&emsp;&ensp;<i class="icon-folder-close"></i> 3rd-level item</a></li>
                                <li><a href="/second sub-item"><i class="icon-folder-close"></i> second sub-directory</a></li>-->
                            </ul>
                        </div>
                    </div>
                    <div class="span9">
                        <div class="well">
                            <div class="row-fluid">
                                <p class="span6 btn-group" id="path"></p>
                                <div class="span6 btn-toolbar" style="margin-top:0;text-align:right;">
                                    <div class="btn-group">
                                        <button id="btn-delete" type="button" class="btn ht" title="Delete" disabled><i class="icon-trash"></i></button>
                                        <button id="btn-edit" type="button" class="btn ht" title="Edit" disabled><i class="icon-edit"></i></button>
                                        <button id="btn-rename" type="button" class="btn ht" title="Rename" disabled><i class="icon-repeat"></i></button>
                                    </div>
                                    <div class="btn-group">
                                        <button id="btn-view" type="button" class="btn ht" title="View" disabled><i class="icon-picture"></i></button>
                                        <button id="btn-dl" type="button" class="btn ht" title="Download" disabled><i class="icon-download"></i></button>
                                    </div>
                                    <button id="btn-upload" type="button" class="btn btn-primary"><i class="icon-upload icon-white"></i> Upload</button>
                                </div>
                            </div>
                            <ul class="nav nav-list" id="filelist">
                                    <!--<li><a href="subdir" data-type="dir"><i class="icon-folder-close"></i> subdir</a></li>
                                    <li><a href="file1" data-type="file"><i class="icon-file"></i> file1</a></li>-->
                            </ul>
                        </div>
                    </div>
                </div>
                <ul class="dropdown-menu" id="dircontext" role="menu" style="display:none;position:absolute;">
                    <li><a tabindex="-1" href="#" id="dirc-rename"><i class="icon-repeat"></i> Rename</a></li>
                    <li><a tabindex="-1" href="#" id="dirc-delete"><i class="icon-trash"></i> Delete</a></li>
                    <li class="divider"></li>
                    <li><a href="#" id="dirc-cancel">Cancel</a></li>
                </ul>
                <div class="modal fade hide" id="modal-upload" tabindex="-1" role="dialog" aria-labelledby="lbl-upload" aria-hidden="true">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h3 id="lbl-upload">Upload Files</h3>
                    </div>
                    <div class="modal-body">
                        <noframes>Your browser sucks. Upgrade it. Seriously, frames have been supported since &#39;95.</noframes>
                        <iframe src="uploader.php" id="iframe-upload" border="0" frameborder="0" style="width:100%;height:125px;" allowtransparency="true"></iframe>
                    </div>
                </div>
                <?php
            }
            else
                echo '
			<p class="alert alert-info">Your account does not have a server.</p>
			<footer>
                </br>
                <p style="margin-left: 0px;">Copyright T.J.s Web Development & T.J. Youschak</p>
            </footer>
';
            ?>
        </div>
    </body>
</html>