#! /bin/bash

if [ -z "$1" ]
  then
    echo "Please provide version number"
fi

version=$1

#Création du tag git et on le pousse en ligne ( vérifier si il n'existe pas )
git tag $version
git push --tags

#Lancement du script php qui se charge des autres tâches
php create_release.php $version

