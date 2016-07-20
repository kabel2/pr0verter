<script type="text/javascript">
    $(function () {
        var count = {$wait_time};
        var counter = setInterval(timer, 1000);

        function timer() {
            count = count - 1;
            if (count === 0) {
                document.location.href = '{$base_url}';
            }
            if (count < 0) {
                clearInterval(counter);
                return;
            }

            $('#timer').html(count + ' Sekunden');
        }
    });
</script>


<div class="container-fluid">
    <div class="row">
        <div class="col-md-6 col-md-offset-3 text-center">
            <h1>Pr0verter</h1>
            <br><br>
            <h2>du musst warten ...</h2>
            <br><br>
            <h3 id="timer"></h3>
        </div>
    </div>
</div>
