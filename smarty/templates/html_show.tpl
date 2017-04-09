<div class="container-fluid">
    <div class="row">
        <div class="col-md-6 col-md-offset-3 text-center">
            <h1>Pr0verter</h1>
            <br><br>
            <video controls width="100%" height="100%">
                <source src="{$base_url}data/{$file}.mp4" type="video/mp4" />
            </video>
            <br><br>
            <form>
                <div class="input-group">
                    <input type="text" class="form-control"
                           value="http://pr0verter.de{$base_url}data/{$file}.mp4" placeholder="Ein Döner bitte" id="copy-input">
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="button" id="copy-button"
                                data-toggle="tooltip" data-placement="button"
                                title="Copy to Clipboard">
                            Copy
                        </button>
                    </span>
                </div>
            </form>
            <br>
            <br>
            <a href="{$base_url}download/{$file}" class="btn btn-danger">download</a>
            <br>


            
        </div>
    </div>
</div>
<script type="text/javascript">
    {literal}
        $(document).ready(function () {
            
            $('#copy-button').tooltip();

            $('#copy-button').bind('click', function () {
                var input = document.querySelector('#copy-input');
                input.setSelectionRange(0, input.value.length + 1);
                try {
                    var success = document.execCommand('copy');
                    if (success) {
                        $('#copy-button').trigger('copied', ['Copied!']);
                    } else {
                        $('#copy-button').trigger('copied', ['Copy with Ctrl-c']);
                    }
                } catch (err) {
                    $('#copy-button').trigger('copied', ['Copy with Ctrl-c']);
                }
            });

            $('#copy-button').bind('copied', function (event, message) {
                $(this).attr('title', message)
                        .tooltip('fixTitle')
                        .tooltip('show')
                        .attr('title', "Copy to Clipboard")
                        .tooltip('fixTitle');
            });
        });
    {/literal}
</script>
