<?php
/**
 * Script Beta de création de la release directement sur github
 * Améliorations à mettre en place
 *  - factorisation appels curls
 *  - Traduction script en anglais
 *  - 
 */
require_once dirname(__FILE__).'/config.php';

$baseApiUrl = 'https://api.github.com/repos/nenes25/eicaptcha/';

//On vérifie qu'un numéro de release est passé au script
if ($argc < 2) {
    exit("Merci de saisir un numéro de release \n");
}

//On vérifie que le tag match bien le pattern
$release = $argv[1];
if (!preg_match('#^([0-2]{1})\.[0-9]{1}\.[0-9]{1,}$#', $release,$version)) {
    exit("Le numero de release doit matcher le pattern ^[0-2]{1}\.[0-9]{1}\.[0-9]{1,}$# \n");
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
        exit("Le pattern de version ne correspond pas à une branche");
        break;
}
echo "Vérification de l'existance de la release \n";

/**
 * On vérifie que la release existe
 */
$ch                = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseApiUrl.'releases/tags/'.$release);
$curlGlobalOptions = array(
    CURLOPT_USERAGENT => $github_user,
    CURLOPT_USERNAME => $github_user,
    CURLOPT_PASSWORD => $github_password,
    CURLOPT_RETURNTRANSFER => true, //Renvoie la réponse dans une variable
);

//Définition des options curl
curl_setopt_array($ch, $curlGlobalOptions);

$content = curl_exec($ch);
$info    = curl_getinfo($ch);
curl_close($ch);

//Le tag existe déjà fin du script
if ($info['http_code'] == 200) {
    echo "le tag existe déjà \n";
    exit('fin du script');
}

//Le tag n'existe pas encore
if ($info['http_code'] == 404) {
    echo "le tag n'existe pas encore \n";
}

//@Todo La version du tag ne doit pas être inférieure à la dernière release
//Création de la release en mode brouillon
echo "Création de la release \n";

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
    echo "Release créé avec succès \n";
} else {
    exit("Erreur dans la création de la release \n");
}
curl_close($curlDraft);

//Traitement de la réponse
$draftResponse  = json_decode($draftExec);
$assetUploadUrl = str_replace('{?name,label}', '', $draftResponse->upload_url);

// Création de l'archive
echo "Création de l'archive \n";

//Vérification de l'existance du dossier vendor
if (!is_dir(dirname(__FILE__).'/../vendor')) {
    echo "Attention le dossier vendor n'existe pas : arrêt du script \n";
}

$zip = new ZipArchive();
$ret = $zip->open('eicaptcha.zip', ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE);
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
            $zip->addEmptyDir(str_replace($source.'/', '', $file.'/'));
        } else if (is_file($file) === true) {
            $zip->addFromString(str_replace($source.'/', '', $file),
                file_get_contents($file));
        }
    }
}
$zip->close();

echo "archive créée \n";

echo "Ajout de la pièce jointe à la relase \n";
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

echo "La release est publiée \n";


