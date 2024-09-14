#!/bin/bash

bash scripts/setup.sh

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

echo -e "\n1 - Building environment: ${ENV}"
echo -e "APP_ENV=$ENV" > .env

echo -e "\n1a - Creating storage directories"
mkdir -vp storage/cms/{combiner,twig,cache}
mkdir -vp storage/framework/{cache,sessions}
mkdir -vp storage/{app,temp,logs}

# Now update composer with new packages
echo -e "\n2 - Installing composer packages"
php composer.phar clear-cache
rm -fr vendor
if [ -f 'composer.lock' ]; then
	rm composer.lock
fi
php composer.phar install

echo -e "\n3 - Zipping app"
if [ -f 'artifacts.tar.gz' ]; then
	rm artifacts.tar.gz
fi

echo -e "\n4 - Creating artifacts"
if [ ${ENV} = 'dev' ]; then
	echo -e "\n4a - Skipping creating real artifacts since we're in dev environment"
else
	shopt -s dotglob
	tar --exclude='./storage' --exclude='./.git' --exclude="artifacts.tar.gz" --exclude=".backup" -zcpvf artifacts.tar.gz ./* > artifacts.creat.log 2>&1
fi

echo -e "\nBuild Done!"
exit
