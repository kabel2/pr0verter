$(function () {
    var bar = $('#upload_bar');
    var status = $('#status');
    
    $('#support').on('click', function (event) {
        document.getElementById("supportadd").style.display = "flex";
        window.scrollTo(0,document.body.scrollHeight);
    });
    
    $('#upload_form').ajaxForm({
        beforeSend: function () {
            status.empty();
            var file = $('#file').val();
            var url = $('#url').val();
            var limit = parseInt($('#limit').val());
            if (file !== '' || url !== '') {
                if(file !== ''){
                    if(!is_supported(file)){
                        alert ("Format nicht unterstützt :/");
                        return;
                    }
                } else {
                    if(!is_supported(url)){
                        alert ("Format nicht unterstützt :/");
                        return;
                    }
                }
                
                if (Math.floor(limit) == limit && $.isNumeric(limit)) {
                        $('#full').fadeIn();
                    } else {
                        return false;
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

// c:\\fakepath\test.mp4
// http://test.de/test.mp4
function is_supported(path) {
    if (path.includes('.')) {
        var url_array = path.split('.');
        var format = url_array[url_array.length - 1];
        var supported_formats = ["webm", "mp4", "mkv", "mov", "avi", "wmv", "flv", "3gp", "gif", "gifv"];
        var supported = (supported_formats.indexOf(format.toLowerCase()) > -1);
        return supported;
    }
    return false;
}