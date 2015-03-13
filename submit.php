<?php
require_once('ljs-includes.php');

include('ljs-template/header.part.php');
?>
<div class="content submitGif">
    <h2>Proposer un gif</h2>
    <p>Avant de proposer un gif, veuillez vous assurer que celui-ci est conforme aux règles
        d'utilisation du service.</p>
    <br />

    <input type="text" id="catchPhraseInput" name="catchPhrase" placeholder="Titre" />
    <ul id="warnings"></ul>

    <div class="uploadMethods">
        <div>
            <h3>Envoyer un fichier</h3>
            <input type="file" />
        </div>
        <div>
            <h3>Télécharger un fichier</h3>
            <input type="text" placeholder="URL vers le fichier .gif" />
        </div>
    </div>

    <input type="submit" value="Proposer" />
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
                warningsList[warningsList.length] = 'Le titre ne devrait pas terminer par un point';

            if (text.length > 120)
                warningsList[warningsList.length] = 'Le titre ne doit pas être trop long';

            var warnings = $('#warnings');
            warnings.html('');
            for (var i=0; i<warningsList.length; i++)
                warnings.append('<li>' + warningsList[i] + '</li>');
        });
    });
</script>

<?php include('ljs-template/footer.part.php'); ?>
