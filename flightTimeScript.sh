#!/bin/bash

# compile the flightTime.cpp file
g++ -std=c++11 -o flightTime flightTime.cpp

# Define the input and output file paths
inputCsv="final_flight_airport_data.csv"
outputCsv="output_flight_times.csv"

# Check if the output file already exists and remove it to start fresh
if [ -f "$outputCsv" ]; then
    rm "$outputCsv"
fi

# Read the input CSV line by line
while IFS=, read -r depName depLat depLon destName destLat destLon distance; do
    # Prepare the input for the flightTime program
    printf "%s\n%s\n%s\n%s\n%s\n%s\n%s" "$depName" "$depLat" "$depLon" "$destName" "$destLat" "$destLon" "$distance" | \
    ./flightTime | \
    while read -r line; do
        # Assume the output is just the flight time; adjust if necessary
        echo "$depName -> $destName, $line" >> "$outputCsv"
    done
done < "$inputCsv"
