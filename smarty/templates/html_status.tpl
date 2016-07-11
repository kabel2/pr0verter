<div class="container-fluid">
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <center><h1>Pr0verter</h1></center>
            <br><br>
            <center><h2>konvertiere</h2></center>
            <br><br>
            <div class="progress">
                <div id="bar" class="progress-bar progress-bar-danger progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0;">
                    0%
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    {literal}
    $(function () {
        var interval = setInterval(get_progress, 1000);
        function get_progress() {
            var action = 'duration',
                    method = 'POST',
                    data = {duration: '{/literal}{$duration}{literal}', file_name: '{/literal}{$file_name}{literal}'}
            $.ajax({
                url: action,
                type: method,
                data: data
            }).done(function (data) {
                console.log(data);
                if (data === 'error') {
                    document.location.href = '{/literal}{$base_url}{literal}error';
                } else {
                    $('#bar').width(data + '%').html(data + '%');
                    if (data === '100') {
                        document.location.href = '{/literal}{$base_url}{literal}show/{/literal}{$file_name}{literal}';
                                            }
                                        }
                                    });
                                }

                            });
    {/literal}
</script>
