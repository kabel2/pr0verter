<div class="container-fluid">
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <center><h1>Pr0verter</h1> </center>
            Der Pr0verter wandelt deine Videos ins Mp4 Format um.<br>
            Videos dürfen max. 50MB groß sein. Videos, die länger als 120 Sekunden sind, werden gekürzt.<br>
            Das Konvertieren kann je nach Videolänge bis zu 10 Minuten dauern, also Geduld. ¯\_(ツ)_/¯<br><br>
            <form action="{$base_url}upload" method="POST" id="upload_form" enctype="multipart/form-data">
                <hr>
                <div class="form-group">
                    <h2>Datei:</h2>
                    <label class="btn btn-default btn-file">
                        wählen <input type="file" class="form-control" name="file" id="file" style="display: none;" />
                    </label>
                </div>
                <br>
                <h3>ODER remote Upload</h3>
                <br>
                <div class="form-group">
                    <h2>URL:</h2>
                    <input type="text" class="form-control" size=30 name="url" id="url" />
                </div>
                <br>
                <hr>
                <br>
                <h2>Größe die das Video haben soll in MB:</h2>
                <div class="input-group">
                    <div class="input-group-addon">1 - 30</div>
                    <input type="number" id="limit" name="limit" min="1" max="30" value="4" class="form-control" />
                    <div class="input-group-addon">MB</div>
                </div>
                <hr>
                <h2>Mit Ton:</h2>
                <div class="checkbox">
                    <label>
                        <input name="sound" type="checkbox"> JA!
                    </label>
                </div>
                <hr>
                <br>
                <center><input class="btn btn-danger" type="submit" value="Konvertieren"></center>
                <br>
                <br>
                Kontakt: pr0verter@gmail.com
                <br>
            </form>
        </div>
    </div>
</div>

<div class="container-fluid" id="full">
    <div class="row">
        <div class="col-md-6 col-md-offset-3" id="progress">
            <center> <h2>lade hoch ...</h2></center>
            <br>
            <div class="progress">
                <div id="upload_bar" class="progress-bar progress-bar-danger progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0;">
                    0%
                </div>
            </div>
        </div>
    </div>
</div>

<div id="status" style="display: none;"></div>