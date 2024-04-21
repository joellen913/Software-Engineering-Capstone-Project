#!/bin/bash

# Client: Professor Michael Oudshoorn 
# Group Name: Byte Me               
# Contractors: Joellen A., Kyle B., Hanna Z., Alex H., Joshua T., JR V. 
# Date: 04/16/2024
# File Name: stop.sh


# Description: This file is used to stop the docker container.


# Input: N/A


# Output: Stops the docker container.


# Functions: N/A


# Languages Used: Bash

echo "Stopping docker!"

docker compose down

echo "Docker is down, thank you for using this service!"