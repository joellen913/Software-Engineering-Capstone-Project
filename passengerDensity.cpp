/*
    File: passengerDensity.cpp
    Description: This file contains the function to calculate the number of passengers on any given day from one airport to another

    Contractors: Joellen A., Kyle B., Hanna Z., Alex H.,  Joshua T., JR V.  
    Group: ByteMe
    Client: Professor Michael Oudshoorn 
    Date: 3/7/2024

    To compile: g++ -std=c++11 passengerDensity.cpp -o passengerDensity
    To run: ./passengerDensity
*/

#include <iostream>
#include <vector>
#include <string>
#include <fstream>

// Define a struct for airport information
struct Airport {
    std::string code;
    double population;
};

int main() {
    // Open an output file
    std::ofstream outFile("passengerDensityOutput.txt");

    // Initialize a vector of airports with example metro populations (in millions)
    std::vector<Airport> airports = {
        {"ATL", 6140000}, 
        {"LAX", 12530000},
        {"DFW", 7760000},
        {"DEN", 2963000},
        {"ORD", 8940000},
        {"JFK", 18940000},
        {"LAS", 2890000},
        {"MCO", 2100000},
        {"MIA", 6200000},
        {"CLT", 2200000},
        {"SEA", 3500000},
        {"PHX", 4700000},
        {"SFO", 3300000},
        {"HOU", 6700000},
        {"BOS", 4300000},
        {"MSP", 3000000},
        {"DET", 3500000},
        {"PHL", 5700000},
        {"SLC", 1200000},
        {"DCA", 5500000},
        {"SAN", 3300000},
        {"TPA", 3000000},
        {"IAD", 5500000},
        {"BNA", 1300000},
        {"MDW", 8900000},
        {"CDG", 13000000}
    };
    

    // Calculate total metro population
    double total_metro_population = 0.0;
    for (const auto& airport : airports) {
        total_metro_population += airport.population;
    }

    // Nested loop to calculate AtoB for each pair of airports
    for (const auto& dept_airport : airports) {
        for (const auto& dest_airport : airports) {
            if (dept_airport.code != dest_airport.code) {  // Ensure not calculating for the same airport
                double AtoB = dest_airport.population / (total_metro_population - dept_airport.population);
                double passengerAmount = dept_airport.population * AtoB * .005 * .02;
                passengerAmount = ceil(passengerAmount);
                
                outFile << "Passengers on any given day from " << dept_airport.code << " to " << dest_airport.code << ": " << passengerAmount << std::endl;
            }
        }
    }

    // Close the output file
    outFile.close();

    return 0;
}
