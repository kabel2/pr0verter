$(function () {
    var bar = $('#upload_bar');
    var status = $('#status');
    
    

    $('#upload_form').ajaxForm({
        beforeSend: function () {
            status.empty();
            var file = $('#file').val();
            var url = $('#url').val();
            var limit = parseInt($('#limit').val());
            if (file === '' && url === '') {

            } else {
                if (file !== '' && url !== '') {

                } else {
                    if (Math.floor(limit) == limit && $.isNumeric(limit)) {
                        $('#full').fadeIn();
                    } else {
                        return false;
                    }
                }
            }
        },
        uploadProgress: function (event, position, total, percentComplete) {
            var percentVal = percentComplete + '%';
            bar.width(percentVal);
            bar.html(percentVal);
        },
        complete: function (xhr) {
            status.html(xhr.responseText);
        }
    });
}); 