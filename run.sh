#!/bin/bash

# Client: Professor Michael Oudshoorn 
# Group Name: Byte Me               
# Contractors: Joellen A., Kyle B., Hanna Z., Alex H., Joshua T., JR V. 
# Date: 04/16/2024
# File Name: run.sh


# Description: This file is used to start the docker container 
# and configure it to run the php and apache server.


# Input: N/A


# Output: Starts the docker container and opens the webpage in the default browser.


# Functions: N/A


# Languages Used: Bash

echo "Starting docker!"

open -a "Docker"

#wait until docker is running
while ! docker info &> /dev/null
do
    sleep 1
done

docker compose up -d

echo "Docker is up"

# Enter the container
docker exec -w /var/www/html -it php_apache_container bash -c "
    docker-php-ext-install mysqli && \
    docker-php-ext-enable mysqli && \
    apachectl restart
"

echo "Docker is configured"

# wait untile the server is running
while ! curl http://localhost:8800/index.php &> /dev/null
do
    sleep 2
done

# Open the default browser
open http://localhost:8800/index.php