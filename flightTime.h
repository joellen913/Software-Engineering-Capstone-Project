/*
    * flightTime.h
    * Description: This file contains the function prototype for the flightTime function.
    * Contractor: Joellen A., Kyle B., Hanna Z., Alex H.,  Joshua T., JR V.  
    * Date: 3/7/2024
    * Group: ByteMe
    * Client: Professor Michael Oudshoorn 
*/
#include <string>
#include <iostream>
#include <cmath>

using namespace std;

#ifndef FLIGHTTIME_H
#define FLIGHTTIME_H

//structs for Airport and Plane
struct Airport {
    char name[50];
    double latitude;
    double longitude;
};

struct Plane {
    char name[50];
    double speed;
};

int main();
double flightTime(Airport destinationAirport, Airport departureAirport, Plane planeType, double distance);

#endif
