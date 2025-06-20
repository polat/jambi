'use strict';

function getFileUpload(rec_table, rec_id, field_name, path) {
    var url = path + 'plugins/gallery/php/?rec_table=' + rec_table + '&rec_id=' + rec_id + '&field_name=' + field_name;
    var dropZone = $('.dropzone.' + field_name);
    var fileUpload = dropZone.first('.fileupload');

    // Initialize the jQuery File Upload widget:
    fileUpload.fileupload({
        url: url,
        dropZone: dropZone,
        dataType: 'json',
        autoUpload: true,
        acceptFileTypes: /(\.|\/)(gif|jpe?g|png|svg)$/i,
        maxFileSize: 5000000, // 5MB
        disableImageResize: /Android(?!.*Chrome)|Opera/
            .test(window.navigator.userAgent),
        previewMaxWidth: 110,
        previewMaxHeight: 110,
        maxNumberOfFiles: 6,
        previewCrop: true
    }).on('fileuploadadd', function (e, data) {
        data.context = $('<li/>').appendTo($('.gallery-div.' + field_name + ' ul.gallery-list'));

        $.each(data.files, function (index, file) {
            var node = $('<p/>');
            node.appendTo(data.context);
        });

    }).on('fileuploadprocessalways', function (e, data) {
        var index = data.index,
            file = data.files[index],
            node = $(data.context.children()[index]);

        if (file.preview) {
            node.prepend(file.preview);
        }

        if (file.error) {
            node.append($('<span class="text-danger"/>').text(file.error)).append('<br>').append($('<span class="name"/>').text(file.name));
        }
    }).on('fileuploadprogressall', function (e, data) {
        var progress = parseInt(data.loaded / data.total * 100, 10);

        dropZone.find('.progress-bar').css('width', progress + '%');
    }).on('fileuploaddone', function (e, data) {
        $.each(data.result.files, function (index, file) {
            if (file.error) {
                var error = $('<span class="text-danger"/>').text(file.error);
                $(data.context.children()[index]).append(error);
            }
        });
    }).on('fileuploadfail', function (e, data) {
        $.each(data.files, function (index, file) {
            var error = $('<span class="text-danger"/>').text('File upload failed.');
            $(data.context.children()[index]).append(error);
        });
    }).prop('disabled', !$.support.fileInput).parent().addClass($.support.fileInput ? undefined : 'disabled');

    // Drag and drop effect
    $(document).bind('dragover', function (e) {
        var foundDropzone,
            timeout = window.dropZoneTimeout;

        if (!timeout) {
            dropZone.addClass('in');
        } else {
            clearTimeout(timeout);
        }
        var found = false,
            node = e.target;

        do {
            if ($(node).hasClass('dropzone')) {
                found = true;
                foundDropzone = $(node);
                break;
            }

            node = node.parentNode;
        } while (node != null);
        dropZone.removeClass('in hover');

        if (found) {
            foundDropzone.addClass('hover');
        }

        window.dropZoneTimeout = setTimeout(function () {
            window.dropZoneTimeout = null;
            dropZone.removeClass('in hover');
        }, 100);
    });

    // Gallery sorting settings
    $('.gallery-div.' + field_name + ' ul.gallery-list').sortable({
        handle: '.handle',
        update: function () {
            var order = $('.gallery-div.' + field_name + ' ul.gallery-list').sortable('serialize');
            $('.infoSort.' + field_name).load(path + "plugins/gallery/handle.php?sort=true&" + order);
        }
    });

    // Delete image from gallery
    $('body').on('click', '.gallery-div.' + field_name + ' .deleteImg', function () {
        if (confirm('Görsel silinecek. Emin misiniz?')) {
            var a = $(this);

            $.ajax({
                type: 'GET',
                url: path + 'plugins/gallery/handle.php',
                data: 'delete=true&rec_table=' + rec_table + '&id=' + $(this).attr('rel') + '&field_name=' + field_name,
                success: function (response) {
                    response = JSON.parse(response);

                    if (response.result == true) {
                        a.parent().parent().hide();
                    } else {
                        alert('Hata!');
                    }
                }
            });
        }
    });
}