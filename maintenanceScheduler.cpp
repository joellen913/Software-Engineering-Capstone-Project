/*
    File: maintenanceScheduler.cpp
    Description: This file contains the functions to simulate aircraft maintenance scheduling

    Contractors: Joellen A., Kyle B., Hanna Z., Alex H.,  Joshua T., JR V.  
    Group: ByteMe
    Client: Professor Michael Oudshoorn 
    Date: 3/7/2024
*/

#include <iostream>
#include <map>
#include <string>

// Represents an aircraft with its current flight hours and maintenance status
struct Aircraft {
    std::string tailNumber;
    int flightHours;
    bool needsMaintenance;
};

// Function to check if aircraft needs maintenance
bool checkMaintenance(Aircraft &aircraft) {
    const int maintenanceThreshold = 200; // Aircraft requires maintenance after 200 hours of flight
    if (aircraft.flightHours >= maintenanceThreshold) {
        return true;
    }
    return false;
}

// Function to simulate adding flight hours to an aircraft
void addFlightHours(Aircraft &aircraft, int hours) {
    aircraft.flightHours += hours;
    aircraft.needsMaintenance = checkMaintenance(aircraft);
}

// Function to reset flight hours after maintenance
void performMaintenance(Aircraft &aircraft) {
    if (aircraft.needsMaintenance) {
        aircraft.flightHours = 0; // Reset flight hours after maintenance
        aircraft.needsMaintenance = false;
        std::cout << "Maintenance performed on aircraft " << aircraft.tailNumber << std::endl;
    } else {
        std::cout << "No maintenance needed for aircraft " << aircraft.tailNumber << std::endl;
    }
}

int main() {
    // Example aircraft
    Aircraft aircraft1 = {"CA123", 0, false};

    // Simulate flight hours
    addFlightHours(aircraft1, 100); // Add 100 flight hours
    performMaintenance(aircraft1); // Check and perform maintenance if needed

    // Add more flight hours and check again
    addFlightHours(aircraft1, 150); // This should trigger the maintenance requirement
    performMaintenance(aircraft1); // Perform maintenance

    return 0;
}
