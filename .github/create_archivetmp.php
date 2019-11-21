<?php
include_once 'config.php';
// Création de l'archive
$archiveName = 'eicaptcha.zip';
echo "Creation of zip archive \n";

//Vérification de l'existance du dossier vendor
if (!is_dir(dirname(__FILE__).'/../vendor')) {
    echo "Error : the vendor directory doesn't exist : end of the script \n";
}

//Suppression de l'archive si elle existe déjà
if ( file_exists($archiveName)) {
    unlink($archiveName);
}

$zip = new ZipArchive();
$ret = $zip->open($archiveName, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE);
if ($ret !== TRUE) {
    printf("Error unable to open archive %d", $ret);
} else {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($baseDir),
        RecursiveIteratorIterator::SELF_FIRST
    );

    $source = realpath($baseDir);
    foreach ($files as $file) {
        $file         = realpath($file);
        $relativePath = str_replace($source.'/', '', $file.'/')."\n";

        //Exclude git and local directories
        if (preg_match('#^(\.git|/mnt|/var|/hdd)#', $relativePath)) {
            continue;
        }
        if (is_dir($file) === true) {
            $zip->addEmptyDir(str_replace($source.'/', '', 'eicaptcha/'.$file.'/'));
        } else if (is_file($file) === true) {
            $zip->addFromString(str_replace($source.'/', '', 'eicaptcha/'.$file),
                file_get_contents($file));
        }
    }
}
$zip->close();