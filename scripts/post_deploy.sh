#!/bin/bash

# Get the script arguments
options=$@

# An array with all the arguments
arguments=($options)

# Loop index
index=0

for argument in $options
	do
	# Incrementing index
	index=`expr $index + 1`

	# The conditions
	case $argument in
		--env) ENV=${arguments[index]};;
		--dirGroup) dirGroup=${arguments[index]};;
		--dirOwner) dirOwner=${arguments[index]};;
	esac
done

if [ -z ${ENV} ]; then
	case ${BRANCH_NAME} in
		'master')
			ENV="staging";;
		'staging')
			ENV="staging";;
		'beta')
			ENV="beta";;
		*)
			ENV="dev";;
	esac
fi

echo -e "\n1 - Deploying to environment: ${ENV}"
mkdir -p .backup
echo -e "APP_ENV=$ENV" > .env
if [ ${ENV} = 'dev' ]; then
	echo -e "\n2a - Skipping creating backup since we're in dev environment"
else
	echo -e "\n1a - Backing up current application"
	tar  --exclude="artifacts.tar.gz" --exclude=".backup" -zcpvf app.backup.tar.gz ./*
	mv app.backup.tar.gz .backup/
fi

echo -e "\n1b - Unzipping application and creating file list"
if [ ${ENV} = 'dev' ]; then
	echo -e "\n\t > Skipping unzipping application since we're in dev environment. Showing mock cleanup"
	find .  \( -wholename './artifacts.tar.gz' -o -path ./storage/app -o -path ./.backup -o -path ./.git -o -path ./storage \) -prune -o -print
else
	echo -e "\n\t > Removing old app and extracting new one"
	find .  \( -wholename './artifacts.tar.gz' -o -path ./storage/app -o -path ./.backup -o -path ./.git -o -path ./storage \) -prune -o -exec rm -rf {} \;
	tar -tzpf artifacts.tar.gz > artifacts.list
	tar -zxpf artifacts.tar.gz ./
	rm artifacts.tar.gz
fi

echo -e "\n1c - Creating storage directories"
mkdir -p storage/cms/{combiner,twig,cache}
mkdir -p storage/framework/{cache,sessions}
mkdir -p storage/{app,temp,logs}

# Link NITM plugins
echo -e "\n1d - Linking NITM plugins and copying scripts"
mkdir -p plugins
rm plugins/nitm
if [ ! -L 'plugins/nitm' ]; then
    ln -s -f `pwd`/vendor/nitm/octobercms-base/src plugins/nitm
fi
mkdir -p scripts
if [ ! -L 'scripts' ]; then
    cp -fr `pwd`/vendor/nitm/octobercms-base/scripts ./
fi

# Going into maintenance mode
echo -e "\n2 - Going into maintenance mode"
php artisan down

# Applying migrations and optimizing application
echo -e "\n3 - Applying migrations and optimizing application"
php artisan cache:clear
php artisan route:clear
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan october:util compile assets
php artisan october:util compile lang
php artisan october:util compile js
php artisan october:util compile less
php artisan optimize
php artisan october:mirror public

# Lets build the app
if [ -f 'scripts/build_js_app.sh' ]; then
	echo -e "\n3a - Building javascript application"
	bash scripts/build_js_app.sh {$ENV}
fi

# Now lets fix permissions
echo -e "\n4 - Fixing permissions";
bash scripts/fix_permissions.sh ${dirOwner} ${dirGroup} > /dev/null

echo -e "\n5 - Exiting maintenance mode"
php artisan up

echo -e "\nDeploy Done!"
exit
