<?php
/**
 * Handle file download from one server to this one logic
 */
function downloadFile($remoteUri, $destinationDir) {
    file_put_contents($destinationDir, fopen($remoteUri, 'r'));
}
