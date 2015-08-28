#!/bin/bash

### Script de maj de toutes les conf locales du module
### Permets de saisir les paramètres google et d'activer tous les affichages

moduleDir="/var/www/public/prestashop/global_modules/"
prestashopDir="/var/www/public/prestashop/"

captchaPublicKey="6Le-FwATAAAAAJIQQhDPahuxSFiV8bwRZ6ejzEY9"
captchaPrivateKey="6Le-FwATAAAAADYjH_lPNXLmxYXdbPnWpwNwYX0L"

cd $prestashopDir

#On parcours l'ensemble des dossiers du dossier prestashop qui commencent par prestashop
for i in $(ls -d */ | grep "^prestashop"); 
do 
	php ${moduleDir}prestashop_eiinstallmodulescli/install_module.php ps_version=${i%%} mode="configuration" key=CAPTCHA_PUBLIC_KEY value="$captchaPublicKey"
	php ${moduleDir}prestashop_eiinstallmodulescli/install_module.php ps_version=${i%%} mode="configuration" key=CAPTCHA_PRIVATE_KEY value="$captchaPrivateKey"
	php ${moduleDir}prestashop_eiinstallmodulescli/install_module.php ps_version=${i%%} mode="configuration" key=CAPTCHA_ENABLE_ACCOUNT value="1"
	php ${moduleDir}prestashop_eiinstallmodulescli/install_module.php ps_version=${i%%} mode="configuration" key=CAPTCHA_ENABLE_CONTACT value="1"
done;