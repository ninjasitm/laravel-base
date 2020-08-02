#!/bin/bash

read -e -p "WARNING: This script will reset your database! Do you want to continue? [y/n]:" CONTINUE

if [ $CONTINUE != 'y' ]; then
    echo -e "\nExiting post_create.sh"
    exit;
fi

echo -e "\n0 - Setting up dev configuration"
echo -e "APP_ENV=dev" > .env
if [ ! -f config/dev/.dbinited ]; then 
    while [ -z $DB_ENGINE ]
    do
        read -e -p "Which DB do you want to use ? [pgsql, mysql, ...etc], then press [ENTER]: " DB_ENGINE
    done

    sed -i -r "s/DB_ENGINE/"$DB_ENGINE"/" config/dev/database.php

    if [ $DB_ENGINE != 'sqlite' ]; then

        while [ -z $DB_HOST ]
        do
            read -e -p "What is the database host?, then press [ENTER]: " DB_HOST
        done

        sed -i -r "s/DB_HOST/"$DB_HOST"/" config/dev/database.php

        while [ -z $DB_HOST ]
        do
            read -e -p "What is the database host?, then press [ENTER]: " DB_HOST
        done

        sed -i -r "s/DB_HOST/"$DB_HOST"/" config/dev/database.php

        read -e -p "What is the database port?, then press [ENTER]: " DB_PORT

        if [ -z $DB_PORT ]; then 
            case "$DB_ENGINE" in
                pgsql)
                    DB_PORT=5432
                    ;;
                sqlsrv)
                    DB_PORT=1433
                    ;;
                sqlite)
                    DB_PORT=5432
                    ;;
                *)
                    DB_PORT=3306
                    ;;
            esac
        fi
        sed -i -r "s/DB_PORT/"$DB_PORT"/" config/dev/database.php

        while [ -z $DB_USER ]
        do
            read -e -p "What is the database user?, then press [ENTER]: " DB_USER
        done

        sed -i -r "s/DB_USER/"$DB_USER"/" config/dev/database.php

        while [ -z $DB_PASSWORD ]
        do
            read -e -p "What is the database password?, then press [ENTER]: " DB_PASSWORD
        done

        sed -i -r "s/DB_PASSWORD/"$DB_PASSWORD"/" config/dev/database.php
    fi

    while [ -z $DB_DATABASE ]
    do
        read -e -p "What is the database name?, then press [ENTER]: " DB_DATABASE
    done

    sed -i -r "s/DB_DATABASE/"$DB_DATABASE"/" config/dev/database.php

    touch config/dev/.dbinited
fi



echo -e "\n1 - Creating storage directories"
mkdir -p storage/cms/{combiner,twig,cache}
mkdir -p storage/framework/{cache,sessions}
mkdir -p storage/{app,temp,logs}

# Link NITM plugins
echo -e "\n2 - Linking NITM plugins and copying scripts"
mkdir -p plugins
if [ ! -L 'plugins/nitm' ]; then 
    ln -s -f `pwd`/vendor/nitm/octobercms-base/src plugins/nitm
fi
mkdir -p scripts
if [ ! -L 'scripts' ]; then 
    cp -fr `pwd`/vendor/nitm/octobercms-base/scripts ./
fi

echo -e "\n4 - Initializing OctoberCMS"
php artisan october:up
php artisan october:fresh

# Installing base plugins
echo -e "\n4 - Installing core plugins"
php artisan plugin:install rainlab.user
php artisan plugin:install rainlab.builder
php artisan plugin:install october.drivers
php artisan plugin:install indikator.backend

echo -e "\nCreate Done!"
exit