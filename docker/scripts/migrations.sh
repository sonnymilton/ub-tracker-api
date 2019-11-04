#!/bin/bash
# TODO: use migrations instead of schema update

docker exec ubtracker_php bin/console doc:schema:upd -f
