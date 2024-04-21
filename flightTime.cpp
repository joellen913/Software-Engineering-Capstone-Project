/*
    * flightTime.cpp
    * This file contains the function to calculate the flight time between two airports
    * Contractors: Joellen A., Kyle B., Hanna Z., Alex H.,  Joshua T., JR V.
    * Date: 3/7/2024
    * Group: ByteMe
    * Client: Professor Michael Oudshoorn 
    * 
    * FOR TESTING PURPOSES ONLY
    * Compile with: g++ -std=c++11 -o flightTime flightTime.cpp
    * Run with: ./flightTime
*/


// includes
#include "flightTime.h"
#include <string>
#include <iostream>
#include <cmath>

// INPUT:
//  The destinationAirport and departureAirport are structs of type Airport, containing the name, latitude, and longitude of the airport
//  The planeType is a struct of type Plane, containing the name and speed of the plane in mph
//  The distance is a double of the distance between the two airports in miles
// 
// OUTPUT:
//  The function returns a double of the total flight time in hours, including taxi, ascent, cruise, and descent
// 

double calcFlightTime(Airport destinationAirport, Airport departureAirport, Plane planeType, double distance){
    // Constant for taxi time (in hours)
    const double averageTaxiTime = 0.33; // 20 minutes for taxiing
    const double ascentDescentMiles = 120; // 120 miles for ascent and descent

    double theta = asin((destinationAirport.latitude - departureAirport.latitude) / distance);

    double flightTimeDelay;
    if (destinationAirport.longitude > departureAirport.longitude){
        flightTimeDelay = .955 + (.0005 * theta);
    } else {
        flightTimeDelay = 1.045 - (.0005 * theta);
    }

    // Calculate the cruise time
    double cruiseTime = ((distance - ascentDescentMiles) / planeType.speed) * flightTimeDelay;

    // Calculate ascent and descent times as percentages of the cruise time
    double ascentTime = (ascentDescentMiles / (planeType.speed * .8)) * flightTimeDelay;
    double descentTime = (ascentDescentMiles / (planeType.speed * .8)) * flightTimeDelay;


    // Total flight time includes taxi, ascent, cruise, and descent
    double totalFlightTime = averageTaxiTime + ascentTime + cruiseTime + descentTime;
    return totalFlightTime;
}


int main() {
    struct Airport testDest, testDep;
    struct Plane testPlane = {"B6", 566.24};
    double testDist;

    std::cin >> testDep.name >> testDep.latitude >> testDep.longitude;
    std::cin >> testDest.name >> testDest.latitude >> testDest.longitude;
    std::cin >> testDist;

    // testDep = {"JFK", 49.64471402, -73.77970351};
    // testDest = {"CDG", 40.00793709, 2.550975343};
    // testDist = 1703.98;

    double flightTime = calcFlightTime(testDest, testDep, testPlane, testDist);
    printf("Flight time: %f\n", flightTime);

    return 0;
}
