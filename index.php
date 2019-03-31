<?php

use EXIF_Remover\Classes\StorageManager;
use EXIF_Remover\Models\AbstractFile;

require_once('vendor/autoload.php');

session_start();

$storageManager = new StorageManager();
$files = $storageManager->getFiles();
?>


<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="assets/plugins/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.2.0/css/all.css" integrity="sha384-hWVjflwFxL6sNzntih27bfxkr27PmbbK/iSvJ+a4+0owXq79v+lsFkW54bOGbiDQ" crossorigin="anonymous">

    <!-- Custom styles -->
    <link href="assets/plugins/dm-uploader/css/jquery.dm-uploader.min.css" rel="stylesheet">
    <link href="assets/plugins/jquery-cropper/cropper.min.css" rel="stylesheet">

    <link href="assets/css/styles.css" rel="stylesheet">

    <title>Uploader</title>
</head>
<body>
    <main role="main" class="container">

        <h1>EXIF_Remover</h1>

        <div class="row">
            <div class="col-md-12">

                <div id="drag-and-drop-zone" class="dm-uploader p-5">
                    <h3 class="mb-5 mt-5 text-muted">Drag &amp; drop files here</h3>

                    <div class="btn btn-primary btn-block mb-5">
                        <span>Open the file Browser</span>
                        <input type="file" title="Click to add Files"/>
                    </div>
                </div>

            </div>
            <div class="col-md-12">
                <div class="card h-100">
                    <div class="card-header">
                        File List
                    </div>

                    <ul class="list-group list-group-flush" id="file-list">
                        <?php if (!empty($files)): ?>

                            <?php /** @var AbstractFile $file */
                            foreach ($files as $file):?>
                                <li class="list-group-item" id="uploaderFile<?php echo $file->getId(); ?>">
                                    <div class="media">
                                        <div class="preview mr-3">
                                            <img class="img-fluid" src="<?php echo $file->getDownloadPath(); ?>"/>
                                        </div>
                                        <div class="media-body mb-1 align-self-center">
                                            <p class="mb-2">
                                                <strong class="file-name"><?php echo $file->getFileName(); ?></strong> -
                                                Status:
                                                <span class="status text-success">Saved</span>
                                            </p>
                                            <div class="progress mb-2">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="ml-3 align-self-center">
                                            <a href="<?php echo $file->getDownloadPath(); ?>" download="<?php echo $file->getFileName(); ?>" class="btn btn-outline-success btn-sm"  role="button">
                                                download
                                            </a>
                                            <button data-id="<?php echo $file->getId(); ?>" data-file-info="<?php echo htmlentities(json_encode($file->toViewArray())) ?>" class="btn btn-outline-primary btn-sm edit-image-btn" data-toggle="modal" data-target="#edit-modal" role="button">
                                                edit
                                            </button>
                                            <button data-id="<?php echo $file->getId(); ?>" data-file-id="<?php echo $file->getId(); ?>" class="btn btn-outline-danger btn-sm delete-image-btn" role="button">
                                                delete
                                            </button>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                            <li class="list-group-item text-muted text-center empty" style="display: none;">No files
                                uploaded.
                            </li>
                        <?php else: ?>
                            <li class="list-group-item text-muted text-center empty">No files uploaded.</li>
                        <?php endif; ?>


                    </ul>

                </div>
            </div>
        </div>

    </main>

    <!-- Libraries -->
    <script src="//code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="//stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>

    <!-- Plugins -->
    <script src="assets/plugins/jquery-cropper/cropper.min.js"></script>
    <script src="assets/plugins/jquery-cropper/jquery-cropper.min.js"></script>
    <script src="assets/plugins/dm-uploader/js/jquery.dm-uploader.min.js"></script>

    <!-- App -->
    <script src="assets/js/main.js"></script>

    <script type="text/javascript">
        $(function () {
            App.init();
        });
    </script>

    <div class="modal modal-fullscreen fade" id="edit-modal" tabindex="-1" role="dialog" aria-labelledby="edit-modal" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title file-name" id="exampleModalLabel"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="toolbar">
                                    <div class="btn-group" role="group" aria-label="Basic example">
                                        <button type="button" class="btn btn-secondary control" data-action="rotate" data-option="-90" title="Rotate (L)">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                        <button type="button" class="btn btn-secondary control" data-action="rotate" data-option="90" title="Rotate (R)">
                                            <i class="fas fa-redo"></i>
                                        </button>
                                        <button type="button" class="btn btn-secondary control" data-action="flip-h" data-option="1" title="Flip (H)">
                                            <i class="fas fa-arrows-alt-h"></i>
                                        </button>
                                        <button type="button" class="btn btn-secondary control" data-action="flip-v" data-option="1" title="Flip (V)">
                                            <i class="fas fa-arrows-alt-v"></i>
                                        </button>
                                        <button type="button" class="btn btn-secondary control" data-action="reset" title="Reset">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </div>
                                    <div class="btn-group" role="group" aria-label="Basic example">
                                        <button type="button" class="btn btn-success control" data-action="crop" title="Crop">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger control" data-action="clear" title="Clear">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="image-holder">
                                    <div class="loader">
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </div>
                                    <img class="image-editor">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <table class="table table-sm table-borderless">
                                    <tbody>
                                    <tr>
                                        <td>File name</td>
                                        <td class="file-name"></td>
                                    </tr>
                                    <tr>
                                        <td>File type</td>
                                        <td class="file-type"></td>
                                    </tr>
                                    <tr>
                                        <td>Uploaded on</td>
                                        <td class="file-date"></td>
                                    </tr>
                                    <tr>
                                        <td>File size</td>
                                        <td class="file-size"></td>
                                    </tr>
                                    <tr>
                                        <td>Dimensions</td>
                                        <td class="file-dimensions"></td>
                                    </tr>
                                    </tbody>
                                </table>

                                <h5>Removed EXIF data</h5>

                                <div class="exif-data-table-holder">
                                    <table class="table table-sm table-borderless exif-data-table">
                                        <tbody>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary save-image">Crop & save image</button>
                </div>
            </div>
        </div>
    </div>

    <script type="text/html" id="files-template">
        <li class="list-group-item">
            <div class="media">
                <div class="preview mr-3">
                    <img class="img-fluid"/>
                </div>
                <div class="media-body mb-1 align-self-center">
                    <p class="mb-2">
                        <strong class="file-name">%%filename%%</strong> - Status:
                        <span class="text-muted">Waiting</span>
                    </p>
                    <div class="progress mb-2">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                </div>
                <div class="ml-3 align-self-center">
                    <a class="btn btn-outline-success btn-sm download" role="button">
                        download
                    </a>
                    <button class="btn btn-outline-primary btn-sm edit-image-btn disabled" data-toggle="modal" data-target="#edit-modal" role="button">
                        edit
                    </button>
                    <button class="btn btn-outline-danger btn-sm delete-image-btn disabled" role="button">delete
                    </button>
                </div>
            </div>
        </li>
    </script>
</body>
</html>

