#!/usr/bin/bash

docker exec ubtracker_php composer install

scriptdir=$(dirname "$(realpath $0)")

$scriptdir/migrations.sh
$scriptdir/clear-cache.sh

docker exec ubtracker_php bin/console doctrine:fixtures:load
