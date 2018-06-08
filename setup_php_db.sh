#!/bin/bash
echo 'installing depedencies'
docker-compose exec php composer install;
echo 'init database with test data'
docker-compose exec php php init_database.php;