<?php
require_once('ljs-includes.php');

if (isset($_POST['catchPhrase']) && isset($_POST['submittedBy'])
    && (isset($_POST['file_upload']) || isset($_POST['file_download']))) {
    $submittedBy = $_POST['submittedBy'];
    $catchPhrase = $_POST['catchPhrase'];
    $source = $_POST['source'];

    // Create cookie with submittedBy value
    setcookie('submittedBy', $submittedBy, time()+60*60*24*30, '/'); // Expire in one month

    if (!checkCatchPhrase($catchPhrase)) {
        // TODO handle errors
    }

    $fileName = '';
    if ($_POST['file_download'] != '') {
        require_once(ROOT_DIR.'/ljs-helper/downloadHandler.php');

        $fileUri = $_POST['file_download'];
        if (!str_endsWith($fileUri, '.gif')) {
            // TODO handle errors
        }

        // Generate filename
        $fileName = generateRandomFileName('uploads/', RANDOM_FILE_NAME_LENGTH, '.gif');

        // Download this file
        downloadFile($fileUri, 'uploads/'.$fileName);

        // TODO check mime type?
    } else if (!empty($_FILES)) {
        require_once(ROOT_DIR.'/ljs-helper/uploadHandler.php');

        $fileName = generateRandomFileName('uploads/', RANDOM_FILE_NAME_LENGTH, '.gif');
        try {
            handleFileUpload('uploads/'.$fileName);
        } catch (RuntimeException $ex) {
            // TODO handle errors
        }
    }

    // Insert this gif in DB
    $gif = new Gif();
    $gif->catchPhrase = $catchPhrase;
    $gif->fileName = $fileName;
    $gif->gifStatus = GifState::SUBMITTED;
    $gif->permalink = getUrlReadyPermalink($catchPhrase);
    $gif->submissionDate = new DateTime();
    $gif->submittedBy = $submittedBy;
    $gif->source = $source;
    insertGif($gif);
}

include(ROOT_DIR.'/ljs-template/header.part.php');
?>
<div class="content submitGif">
    <h2>Proposer un gif</h2>
    <p>Avant de proposer un gif, veuillez vous assurer que celui-ci est conforme <a href="rulesOfTheGame.php">aux règles
        d'utilisation du service</a>.</p>
    <br />

    <form method="post" enctype="multipart/form-data">
        <?php
        $submittedBy = '';
        if (isset($_POST['submittedBy']))
            $submittedBy = $_POST['submittedBy'];
        else if (isset($_COOKIE['submittedBy']))
            $submittedBy = $_COOKIE['submittedBy'];
        ?>
        <input type="text" name="submittedBy" placeholder="Proposé par (votre nom)" value="<?php echo $submittedBy ?>" class="submittedBy" />
        <input type="text" id="source" name="source" placeholder="Source du gif (optionnel)" class="source" />

        <input type="text" id="catchPhraseInput" name="catchPhrase" placeholder="Titre" />
        <ul id="warnings"></ul>

        <input id="giphy_ajax" type="button" value="Besoin d'inspiration ?" /> <img id="ajaxLoading" src="inc/img/ajax-loader.gif" style="visibility: hidden;" />
        <div id="giphyGifs" style="display: none;">
            <ul id="giphyGifsList"></ul>
        </div>

        <div class="uploadMethods">
            <div>
                <h3>Envoyer un fichier</h3>
                <input type="file" name="file_upload" />
            </div>
            <div>
                <h3>Télécharger un fichier</h3>
                <input type="text" id="file_download" name="file_download" placeholder="URL vers le fichier .gif" />
            </div>
        </div>

        <input type="submit" value="Proposer" />
    </form>
</div>

<script type="application/javascript">
    $(document).ready(function() {
        $('#catchPhraseInput').keyup(function() {
            var text = $(this).val();

            var warningsList = [];
            // Rules
            if (text.substring(0, 'Quand'.length) != 'Quand')
                warningsList[warningsList.length] = 'Le titre doit commencer par "Quand"';

            // No point
            if (text.substring(text.length-1) == '.')
                warningsList[warningsList.length] = 'Le titre ne doit pas terminer par un point';

            if (text.length > 120)
                warningsList[warningsList.length] = 'Le titre ne doit pas être trop long';
            else if (text.length < 10)
                warningsList[warningsList.length] = 'Le titre est trop court';

            var warnings = $('#warnings');
            warnings.html('');
            for (var i=0; i<warningsList.length; i++)
                warnings.append('<li>' + warningsList[i] + '</li>');
        });

        $('#giphy_ajax').click(function () {
            $('#ajaxLoading').css('visibility', 'visible');

            $.ajax({
                url: 'ljs-helper/giphyHelper.php',
                method: 'POST',
                data: {
                    action: 'getTrendingGifs'
                },
                success: function(data) {
                    var jsonData = JSON.parse(data);
                    if (jsonData.success) {
                        $('#giphy_ajax').remove();
                        $('#giphyGifs').show();
                        $('.uploadMethods').children().first().css('opacity', '0.2');
                        $('#ajaxLoading').hide();

                        var ul = $('#giphyGifsList');

                        for (var i=0; i<jsonData.gifs.length; i++) {
                            var imageUrl = jsonData.gifs[i]['image'];
                            var sourceUrl = jsonData.gifs[i]['url'];
                            ul.append('<li><img src="' + imageUrl + '" data-source="' + sourceUrl + '" /></li>');
                        }
                    }
                },
                error: function(data) {
                    console.log(data);
                }
            });
        });

        $('#giphyGifsList').on('click', 'img', function() {
            var img = $(this);
            $('#source').val(img.attr('data-source'));
            $('#file_download').val(img.attr('src'));
            $('#giphyGifsList').find('img').removeClass('selected');
            $('#giphyGifsList').find('img').addClass('notSelected');
            $(this).addClass('selected');
        });
    });
</script>

<?php include(ROOT_DIR.'/ljs-template/footer.part.php');

function checkCatchPhrase($catchPhrase) {
    if (strlen($catchPhrase) < 10)
        return false;

    if (!str_startsWith($catchPhrase, 'Quand'))
        return false;

    return true;
}
