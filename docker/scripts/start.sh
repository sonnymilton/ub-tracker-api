#!/bin/bash

docker exec ubtracker_php composer install

scriptdir=$(dirname "$(realpath $0)")

${scriptdir}/migrations.sh
${scriptdir}/clear-cache.sh

if [[ "$*" == '--load-fixtures' ]]
then
    docker exec ubtracker_php bin/console doctrine:fixtures:load
fi
