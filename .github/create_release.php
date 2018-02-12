<?php
/**
 * Php part of script to relase module on github
 * Improvments needed
 *  - curls call factorisation
 */
require_once dirname(__FILE__).'/config.php';

$baseApiUrl = 'https://api.github.com/repos/nenes25/eicaptcha/';

//On vérifie qu'un numéro de release est passé au script
if ($argc < 2) {
    exit("Please give a release number \n");
}

//On vérifie que le tag match bien le pattern
$release = $argv[1];
if (!preg_match('#^([0-2]{1})\.[0-9]{1}\.[0-9]{1,}$#', $release,$version)) {
    exit("Release number should match pattern : ^[0-2]{1}\.[0-9]{1}\.[0-9]{1,}$# \n");
}

//Définition de la branche en fonction de la version
switch ( $version[1]){
    case 0:
        $gitBranch='master';
        $psVersion = ' Ps version under 1.7';
        break;
    case 2:
        $gitBranch='17';
        $psVersion = ' PS 1.7';
        break;
    default:
        exit("This pattern doesn't match with a branch");
        break;
}
echo "Check if the release exists \n";

$ch                = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseApiUrl.'releases/tags/'.$release);
$curlGlobalOptions = array(
    CURLOPT_USERAGENT => $github_user,
    CURLOPT_USERNAME => $github_user,
    CURLOPT_PASSWORD => $github_password,
    CURLOPT_RETURNTRANSFER => true, //Response in variable
);

//Curl options
curl_setopt_array($ch, $curlGlobalOptions);

$content = curl_exec($ch);
$info    = curl_getinfo($ch);
curl_close($ch);

//It the tag already exists, end of the script
if ($info['http_code'] == 200) {
    echo "this tag already exists \n";
    exit('end of the script');
}

/*If the tag doesn't exist
if ($info['http_code'] == 404) {
    echo "The tag doesn't exist \n";
}*/

//@Todo La version du tag ne doit pas être inférieure à la dernière release

echo "Creation of the release \n";

$releaseDatas = array(
    "tag_name" => $release,
    "target_commit" => $gitBranch,
    "name" => $release,
    "body" => "Release of version ".$release." for ".$psVersion." see changelog.txt for details",
    //Passer à true pour debug
    "draft" => false,
    "prerelease" => false,
);

$curlDraft = curl_init();
curl_setopt_array($curlDraft, $curlGlobalOptions);
curl_setopt($curlDraft, CURLOPT_URL, $baseApiUrl.'releases');
curl_setopt($curlDraft, CURLOPT_POSTFIELDS, json_encode($releaseDatas));

$draftExec = curl_exec($curlDraft);
$draftInfo = curl_getinfo($curlDraft);

if ($draftInfo['http_code'] == '201') {
    echo "Release created with success \n";
} else {
    exit("Error during the creation of the release \n");
}
curl_close($curlDraft);

//Traitement de la réponse
$draftResponse  = json_decode($draftExec);
$assetUploadUrl = str_replace('{?name,label}', '', $draftResponse->upload_url);

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
        if (preg_match('#^(\.git|/mnt)#', $relativePath)) {
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

echo "Add zip archive to release \n";
//Ajout de la pièce jointe à la release
$curlUpload = curl_init();
curl_setopt_array($curlUpload, $curlGlobalOptions);
curl_setopt($curlUpload, CURLOPT_URL,
    $assetUploadUrl.'?name='.urlencode('eicaptcha.zip'));
curl_setopt($curlUpload, CURLOPT_HTTPHEADER,
    array(
    'Content-Type: application/zip'
    )
);
curl_setopt($curlUpload, CURLOPT_POSTFIELDS, file_get_contents('eicaptcha.zip'));
$uploadExec = curl_exec($curlUpload);
$uploadInfo = curl_getinfo($curlUpload);
curl_close($curlUpload);

echo "The relase is published on github \n";


