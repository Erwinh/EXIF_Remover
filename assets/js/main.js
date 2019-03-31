var App = function () {

    var handleInitUploader = function () {
        $('#drag-and-drop-zone').dmUploader({
            url: 'upload.php',
            auto: true,
            queue: true,
            allowedTypes: 'image/*|video/*',
            extFilter: ['jpg', 'jpeg', 'png', 'gif', 'wmv'],
            maxFileSize: 30000000,
            onDragEnter: function () {
                this.addClass('active');
            },
            onDragLeave: function () {
                this.removeClass('active');
            },
            onNewFile: function (id, file) {
                addUploadedImage(id, file);
            },
            onBeforeUpload: function (id) {
                updateImageStatus(id, 'uploading', 'Uploading...');
                updateImageProgress(id, 0, '', true);
            },
            onUploadCanceled: function (id) {
                updateImageStatus(id, 'warning', 'Canceled by User');
                updateImageProgress(id, 0, 'warning', false);
            },
            onUploadProgress: function (id, percent) {
                updateImageProgress(id, percent);
            },
            onUploadSuccess: function (id, data) {
                updateImageStatus(id, 'success', 'Upload Complete');
                updateImageProgress(id, 100, 'success', false);

                updateImageComplete(id, data);
            },
            onUploadError: function (id, xhr, status, message) {
                updateImageStatus(id, 'danger', message);
                updateImageProgress(id, 0, 'danger', false);
            },
            onFileTypeError: function (file) {
                console.log('onFileTypeError', file);
            },
            onFileSizeError: function (file) {
                console.log('onFileSizeError', file);
            },
            onFileExtError: function (file) {
                console.log('onFileExtError', file);
            }
        });


        var imageEditor, modal, origionalImage, croppedCanvas = null;

        function createEXIFDataTable(exifData, template) {
            var html = '';
            var tableRowTemplate = (typeof template !== 'undefined') ? template : '<tr><td>{name}</td><td>{value}</td></tr>';

            $.each(exifData, function (name, value) {
                if (typeof value === 'object') {
                    value = '<table class="table table-sm table-borderless">' + createEXIFDataTable(value) + '</table>'
                }

                html += tableRowTemplate.replace('{name}', name).replace('{value}', value);
            });

            return html;
        }

        $('#edit-modal')
            .on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var fileInfo = button.data('file-info');

                modal = $(this);
                imageEditor = modal.find('.image-editor');

                $('.image-holder').addClass('d-none');
                imageEditor.attr('src', fileInfo.path);

                modal.data('image', {
                    id: button.data('id'),
                    fileInfo: fileInfo
                });

                modal.find('.file-name').html(fileInfo.name);
                modal.find('.file-type').html(fileInfo.type);
                modal.find('.file-date').html(fileInfo.date);
                modal.find('.file-size').html(fileInfo.size);
                modal.find('.file-dimensions').html(fileInfo.dimensions);


                var exifDataTable = $('.exif-data-table');
                var tableRows = createEXIFDataTable(fileInfo.exifData);

                exifDataTable.append(tableRows)

                origionalImage = fileInfo.path;
                modal.find('.loader').show();

            })
            .on('shown.bs.modal', function () {
                modal = $(this);

                var image = modal.data('image');

                modal.find('.image-holder').height(modal.find('.modal-body').height() - 30);
                $('.image-holder').removeClass('d-none');

                imageEditor = modal.find('.image-editor');
                imageEditor.off().cropper({
                    viewMode: 0,
                    autoCrop: false,
                    movable: false,
                    zoomable: true,
                    toggleDragModeOnDblclick: false,
                    crop: function (event) {
                        // console.log(event.detail.x);
                        // console.log(event.detail.y);
                        // console.log(event.detail.width);
                        // console.log(event.detail.height);
                        // console.log(event.detail.rotate);
                        // console.log(event.detail.scaleX);
                        // console.log(event.detail.scaleY);

                        croppedCanvas = imageEditor.cropper('getCroppedCanvas');
                    },
                    ready: function () {
                        imageEditor.cropper('zoomTo', 1);
                        modal.find('.loader').hide();
                    }
                });

                modal.find('.toolbar .control').off().on('click', function () {
                    if (typeof imageEditor === 'undefined') {
                        return;
                    }

                    var action = $(this).data('action');
                    var option = $(this).data('option');

                    switch (action) {
                        case 'rotate':
                            imageEditor.cropper('rotate', option);

                            break;
                        case 'flip-h':
                            imageEditor.cropper('scaleX', -option);
                            $(this).data('option', -option);

                            break;
                        case 'flip-v':
                            imageEditor.cropper('scaleY', -option);
                            $(this).data('option', -option);

                            break;
                        case 'reset':
                            imageEditor.cropper('replace', origionalImage).cropper('clear');

                            break;
                        case 'crop':
                            var croppedImage = imageEditor.cropper('getCroppedCanvas').toDataURL();

                            imageEditor.cropper('replace', croppedImage);

                            break;
                        case 'clear':
                            imageEditor.cropper('clear');

                            break;

                        default:
                            return;
                    }
                });

                modal.find('.save-image').off().on('click', function () {

                    if (typeof imageEditor === 'undefined') {
                        return;
                    }

                    if (croppedCanvas === null) {
                        modal.modal('hide');
                    }

                    croppedCanvas.toBlob(function (blob) {

                        var formData = new FormData();

                        formData.append('id', image.fileInfo.id);
                        formData.append('file', blob, image.fileInfo.name);
                        formData.append('action', 'crop');


                        $.ajax('upload.php', {
                            method: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            xhr: function () {
                                var xhr = new XMLHttpRequest();
                                //
                                // xhr.upload.onprogress = function (e) {
                                //     var percent = '0';
                                //     var percentage = '0%';
                                //
                                //     if (e.lengthComputable) {
                                //         percent = Math.round((e.loaded / e.total) * 100);
                                //         percentage = percent + '%';
                                //         // $progressBar.width(percentage).attr('aria-valuenow', percent).text(percentage);
                                //         console.log('PROGRES', percent);
                                //     }
                                //
                                //
                                // };
                                return xhr;
                            },
                            success: function (response) {
                                // $alert.show().addClass('alert-success').text('Upload success');

                                updateUploadedImage(image.id, response);

                                modal.modal('hide');
                            },
                            error: function (ee) {
                                console.log(ee);
                                // avatar.src = initialAvatarURL;
                                //$alert.show().addClass('alert-warning').text('Upload error');

                            },
                            complete: function () {
                                //$progress.hide();
                            },
                        });

                    });
                });
            })
            .on('hidden.bs.modal', function () {
                imageEditor.cropper('destroy');
            });


        $(document).on('click', '.delete-image-btn', function () {

            var $button = $(this);

            $.ajax('upload.php', {
                method: 'POST',
                data: {
                    action: 'delete',
                    id: $button.data('id')
                },
                success: function (response) {
                    console.log('oke');

                    $button.parents('li.list-group-item').fadeOut(350).remove();

                    if ($('#file-list').find('li').length <= 1) {
                        $('#file-list').find('li.empty').fadeIn();
                    }
                },
                error: function () {
                    console.log('niet oke');
                },
                complete: function () {
                    //$progress.hide();
                },
            });
        });
    };

    var addUploadedImage = function (id, file) {
        var template = $('#files-template').text();
        template = template.replace('%%filename%%', file.name);

        template = $(template);
        template.prop('id', 'uploaderFile' + id);
        template.data('file-id', file.id);

        if (typeof FileReader !== "undefined") {
            var reader = new FileReader();
            var img = template.find('img');

            reader.onload = function (e) {
                img.attr('src', e.target.result);
                template.find('.download').attr('href', e.target.result).attr('download', file.name);
            };

            reader.readAsDataURL(file);
        }

        $('#file-list').find('li.empty').fadeOut();
        $('#file-list').prepend(template);
    };

    var updateUploadedImage = function (id, response) {
        var template = $('#uploaderFile' + id);
        console.log(template);

        var img = template.find('img');
        img.attr('src', response.file.path);

        template.find('.file-name').html(response.file.name);

        template.find('.edit-image-btn')
            .data('file-info', response.file);


        template.find('.download').attr('href', response.file.path).attr('download', response.file.name);
    };

    var updateImageStatus = function (id, status, message) {
        $('#uploaderFile' + id).find('span').html(message).prop('class', 'status text-' + status);
    };

    var updateImageProgress = function (id, percent, color, active) {
        color = (typeof color === 'undefined' ? false : color);
        active = (typeof active === 'undefined' ? true : active);

        var bar = $('#uploaderFile' + id).find('div.progress-bar');

        bar.width(percent + '%').attr('aria-valuenow', percent);
        bar.toggleClass('progress-bar-striped progress-bar-animated', active);

        if (percent === 0) {
            bar.html('');
        } else {
            bar.html(percent + '%');
        }

        if (color !== false) {
            bar.removeClass('bg-success bg-info bg-warning bg-danger');
            bar.addClass('bg-' + color);
        }
    };

    var updateImageComplete = function (id, data) {
        var file = $('#uploaderFile' + id);

        file.find('.edit-image-btn')
            .data('id', id)
            .data('file-info', data.file)
            .removeClass('disabled');

        file.find('.delete-image-btn')
            .data('id', data.file.id)
            .removeClass('disabled');

    };


    return {
        init: function () {
            handleInitUploader();
        }
    }
}();