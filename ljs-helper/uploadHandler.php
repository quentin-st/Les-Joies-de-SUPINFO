<?php
/**
 * Handle file upload
 * Main script structure came from http://php.net/manual/bg/features.file-upload.php#114004
 *
 * Since error messages are caught and displayed on the website, they are translated in french.
 */
function handleFileUpload($destFile) {
    // Undefined | Multiple Files | $_FILES Corruption Attack
    // If this request falls under any of them, treat it invalid.
    if (!isset($_FILES['file_upload']['error']) || is_array($_FILES['file_upload']['error'])) {
        throw new RuntimeException('Paramètres invalides.');
    }

    // Check $_FILES['file_upload']['error'] value.
    switch ($_FILES['file_upload']['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            throw new RuntimeException('Aucun fichier envoyé.');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new RuntimeException('Ce fichier est trop lourd.');
        default:
            throw new RuntimeException('Erreur inconnue.');
    }

    // Check filesize
    if ($_FILES['file_upload']['size'] > 1000000) {
        throw new RuntimeException('Ce fichier est trop lourd.');
    }

    // DO NOT TRUST $_FILES['file_upload']['mime'] VALUE !!
    // Check MIME Type by yourself.
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    if (false === $ext = array_search(
            $finfo->file($_FILES['file_upload']['tmp_name']),
            array('gif' => 'image/gif'),
            true
        )) {
        throw new RuntimeException('Ceci n\'est pas un fichier gif.');
    }

    // You should name it uniquely.
    // DO NOT USE $_FILES['file_upload']['name'] WITHOUT ANY VALIDATION !!
    // On this example, obtain safe unique name from its binary data.
    if (!move_uploaded_file($_FILES['file_upload']['tmp_name'], $destFile)) {
        throw new RuntimeException('Erreur lors de la copie du fichier.');
    }
}
