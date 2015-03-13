<?php
require_once('ljs-includes.php');

// TODO set submittedBy in cookie

if (isset($_POST['catchPhrase']) && isset($_POST['submittedBy'])
    && (isset($_POST['source_upload']) || isset($_POST['source_download']))) {
    $submittedBy = $_POST['submittedBy'];
    $catchPhrase = $_POST['catchPhrase'];

    if (!checkCatchPhrase($catchPhrase)) {
        // TODO handle errors
    }

    if ($_POST['source_download'] != '') {
        require_once('ljs-helper/downloadHandler.php');

        $source = $_POST['source_download'];
        if (!str_endsWith($source, '.gif')) {
            // TODO handle errors
        }

        // Generate filename
        $fileName = generateRandomFileName('uploads/', RANDOM_FILE_NAME_LENGTH, '.gif');

        // Download this file
        downloadFile($source, 'uploads/'.$fileName);

        // TODO check mime type?

        // Add this gif
        $gif = new Gif();
        $gif->catchPhrase = $catchPhrase;
        $gif->fileName = $fileName;
        $gif->gifStatus = GifState::SUBMITTED;
        $gif->permalink = getUrlReadyPermalink($catchPhrase);
        $gif->submissionDate = new DateTime();
        $gif->submittedBy = $submittedBy;
        insertGif($gif);
    } else if ($_POST['source_upload'] != '') { // TODO check if has file
        require_once('ljs-helper/uploadHandler.php');

    } else {
        // TODO handle errors
    }
}

include('ljs-template/header.part.php');
?>
<div class="content submitGif">
    <h2>Proposer un gif</h2>
    <p>Avant de proposer un gif, veuillez vous assurer que celui-ci est conforme aux règles
        d'utilisation du service.</p>
    <br />

    <form method="post" action="submit.php">
        <input type="text" name="submittedBy" placeholder="Proposé par" />
        <input type="text" id="catchPhraseInput" name="catchPhrase" placeholder="Titre" />
        <ul id="warnings"></ul>

        <div class="uploadMethods">
            <div>
                <h3>Envoyer un fichier</h3>
                <input type="file" name="source_upload" />
            </div>
            <div>
                <h3>Télécharger un fichier</h3>
                <input type="text" name="source_download" placeholder="URL vers le fichier .gif" />
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
    });
</script>

<?php include('ljs-template/footer.part.php');

function checkCatchPhrase($catchPhrase) {
    if (strlen($catchPhrase) < 10)
        return false;

    if (!str_startsWith($catchPhrase, 'Quand'))
        return false;

    return true;
}
