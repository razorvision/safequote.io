/**
 * NHTSA API Integration Module
 * Handles communication with NHTSA Safety Ratings API
 */

(function() {
    'use strict';

    const API_BASE = 'https://api.nhtsa.gov/SafetyRatings';
    const cache = new Map();
    const CACHE_DURATION = 3600000; // 1 hour in milliseconds

    /**
     * Initialize NHTSA API module
     */
    function init() {
        console.log('NHTSA API module initialized');
    }

    /**
     * Get vehicle years
     */
    async function getYears() {
        const cacheKey = 'years';
        const cached = getFromCache(cacheKey);

        if (cached) {
            return cached;
        }

        try {
            const currentYear = new Date().getFullYear();
            const years = [];

            // NHTSA typically has data from 2010 onwards
            for (let year = currentYear + 1; year >= 2010; year--) {
                years.push(year);
            }

            saveToCache(cacheKey, years);
            return years;
        } catch (error) {
            console.error('Error getting years:', error);
            throw error;
        }
    }

    /**
     * Get makes for a specific year
     */
    async function getMakes(year) {
        const cacheKey = `makes-${year}`;
        const cached = getFromCache(cacheKey);

        if (cached) {
            return cached;
        }

        try {
            const response = await fetch(`${API_BASE}/modelyear/${year}?format=json`);
            const data = await response.json();

            if (data.Results && data.Count > 0) {
                const makes = data.Results.map(item => ({
                    id: item.MakeId,
                    name: item.Make
                }));

                saveToCache(cacheKey, makes);
                return makes;
            } else {
                return [];
            }
        } catch (error) {
            console.error('Error getting makes:', error);
            throw error;
        }
    }

    /**
     * Get models for a specific year and make
     */
    async function getModels(year, make) {
        const cacheKey = `models-${year}-${make}`;
        const cached = getFromCache(cacheKey);

        if (cached) {
            return cached;
        }

        try {
            const response = await fetch(`${API_BASE}/modelyear/${year}/make/${encodeURIComponent(make)}?format=json`);
            const data = await response.json();

            if (data.Results && data.Count > 0) {
                const models = data.Results.map(item => ({
                    id: item.ModelId,
                    name: item.Model
                }));

                saveToCache(cacheKey, models);
                return models;
            } else {
                return [];
            }
        } catch (error) {
            console.error('Error getting models:', error);
            throw error;
        }
    }

    /**
     * Get vehicle safety ratings
     */
    async function getVehicleSafetyRating(year, make, model) {
        const cacheKey = `rating-${year}-${make}-${model}`;
        const cached = getFromCache(cacheKey);

        if (cached) {
            return cached;
        }

        try {
            const response = await fetch(
                `${API_BASE}/modelyear/${year}/make/${encodeURIComponent(make)}/model/${encodeURIComponent(model)}?format=json`
            );
            const data = await response.json();

            if (data.Results && data.Results.length > 0) {
                const vehicle = data.Results[0];
                const rating = processVehicleRating(vehicle);

                saveToCache(cacheKey, rating);
                return rating;
            } else {
                return null;
            }
        } catch (error) {
            console.error('Error getting vehicle safety rating:', error);
            throw error;
        }
    }

    /**
     * Get detailed vehicle information by VehicleId
     */
    async function getVehicleDetails(vehicleId) {
        const cacheKey = `details-${vehicleId}`;
        const cached = getFromCache(cacheKey);

        if (cached) {
            return cached;
        }

        try {
            const response = await fetch(`${API_BASE}/VehicleId/${vehicleId}?format=json`);
            const data = await response.json();

            if (data.Results && data.Results.length > 0) {
                const details = processVehicleDetails(data.Results[0]);
                saveToCache(cacheKey, details);
                return details;
            } else {
                return null;
            }
        } catch (error) {
            console.error('Error getting vehicle details:', error);
            throw error;
        }
    }

    /**
     * Search vehicles with filters
     */
    async function searchVehicles(filters) {
        const { year, make, model } = filters;

        if (!year) {
            throw new Error('Year is required for vehicle search');
        }

        let url = `${API_BASE}/modelyear/${year}`;

        if (make) {
            url += `/make/${encodeURIComponent(make)}`;
        }

        if (model) {
            url += `/model/${encodeURIComponent(model)}`;
        }

        url += '?format=json';

        try {
            const response = await fetch(url);
            const data = await response.json();

            if (data.Results && data.Results.length > 0) {
                const vehicles = data.Results.map(processVehicleData);
                return filterVehicles(vehicles, filters);
            } else {
                return [];
            }
        } catch (error) {
            console.error('Error searching vehicles:', error);
            throw error;
        }
    }

    /**
     * Process raw vehicle data from API
     */
    function processVehicleData(vehicle) {
        return {
            id: vehicle.VehicleId,
            year: vehicle.ModelYear,
            make: vehicle.Make,
            model: vehicle.Model,
            vehicleDescription: vehicle.VehicleDescription,
            vehicleClass: vehicle.VehicleClass,
            overallRating: vehicle.OverallRating || 0,
            overallFrontCrashRating: vehicle.OverallFrontCrashRating || 0,
            frontCrashDriversideRating: vehicle.FrontCrashDriversideRating || 0,
            frontCrashPassengersideRating: vehicle.FrontCrashPassengersideRating || 0,
            overallSideCrashRating: vehicle.OverallSideCrashRating || 0,
            sideBarrierRating: vehicle.SideBarrierRating || 0,
            rolloverRating: vehicle.RolloverRating || 0,
            rolloverPossibility: vehicle.RolloverPossibility || 0,
            complaintCount: vehicle.ComplaintCount || 0,
            recallCount: vehicle.RecallCount || 0,
            investigationCount: vehicle.InvestigationCount || 0,
            vehiclePicture: vehicle.VehiclePicture || null
        };
    }

    /**
     * Process vehicle rating data
     */
    function processVehicleRating(vehicle) {
        const ratings = {
            overall: parseRating(vehicle.OverallRating),
            frontCrash: parseRating(vehicle.OverallFrontCrashRating),
            sideCrash: parseRating(vehicle.OverallSideCrashRating),
            rollover: parseRating(vehicle.RolloverRating),
            sideBarrier: parseRating(vehicle.SideBarrierRating)
        };

        // Calculate average if overall is not provided
        if (!ratings.overall) {
            const validRatings = Object.values(ratings).filter(r => r > 0);
            if (validRatings.length > 0) {
                ratings.overall = Math.round(
                    validRatings.reduce((sum, rating) => sum + rating, 0) / validRatings.length
                );
            }
        }

        return ratings;
    }

    /**
     * Process detailed vehicle information
     */
    function processVehicleDetails(vehicle) {
        return {
            ...processVehicleData(vehicle),
            ratings: processVehicleRating(vehicle),
            safetyFeatures: extractSafetyFeatures(vehicle),
            ncsaBody: vehicle.NCSABody,
            ncsaMake: vehicle.NCSAMake,
            ncsaModel: vehicle.NCSAModel,
            doors: vehicle.Doors,
            seats: vehicle.Seats,
            wheelBaseShort: vehicle.WheelBaseShort,
            wheelBaseLong: vehicle.WheelBaseLong
        };
    }

    /**
     * Extract safety features from vehicle data
     */
    function extractSafetyFeatures(vehicle) {
        const features = [];

        if (vehicle.ElectronicStabilityControl === 'Standard') {
            features.push('Electronic Stability Control');
        }
        if (vehicle.ForwardCollisionWarning === 'Standard') {
            features.push('Forward Collision Warning');
        }
        if (vehicle.LaneDepartureWarning === 'Standard') {
            features.push('Lane Departure Warning');
        }
        if (vehicle.BlindSpotWarning === 'Standard') {
            features.push('Blind Spot Warning');
        }
        if (vehicle.AdaptiveCruiseControl === 'Standard') {
            features.push('Adaptive Cruise Control');
        }
        if (vehicle.AdaptiveHeadlights === 'Standard') {
            features.push('Adaptive Headlights');
        }
        if (vehicle.BackupCamera === 'Standard') {
            features.push('Backup Camera');
        }
        if (vehicle.ParkingAssist === 'Standard') {
            features.push('Parking Assist');
        }

        return features;
    }

    /**
     * Parse rating value
     */
    function parseRating(value) {
        if (typeof value === 'string') {
            if (value.toLowerCase() === 'not rated') {
                return 0;
            }
            const parsed = parseInt(value);
            return isNaN(parsed) ? 0 : parsed;
        }
        return value || 0;
    }

    /**
     * Filter vehicles based on additional criteria
     */
    function filterVehicles(vehicles, filters) {
        let filtered = [...vehicles];

        // Filter by minimum safety rating
        if (filters.minSafetyRating) {
            filtered = filtered.filter(v => v.overallRating >= filters.minSafetyRating);
        }

        // Filter by vehicle class
        if (filters.vehicleClass) {
            filtered = filtered.filter(v =>
                v.vehicleClass && v.vehicleClass.toLowerCase().includes(filters.vehicleClass.toLowerCase())
            );
        }

        // Sort by rating (highest first)
        filtered.sort((a, b) => (b.overallRating || 0) - (a.overallRating || 0));

        return filtered;
    }

    /**
     * Get recall information for a vehicle
     */
    async function getRecalls(year, make, model) {
        const cacheKey = `recalls-${year}-${make}-${model}`;
        const cached = getFromCache(cacheKey);

        if (cached) {
            return cached;
        }

        try {
            const response = await fetch(
                `https://api.nhtsa.gov/recalls/recallsByVehicle?make=${encodeURIComponent(make)}&model=${encodeURIComponent(model)}&modelYear=${year}&format=json`
            );
            const data = await response.json();

            if (data.results) {
                saveToCache(cacheKey, data.results);
                return data.results;
            } else {
                return [];
            }
        } catch (error) {
            console.error('Error getting recalls:', error);
            throw error;
        }
    }

    /**
     * Get complaints for a vehicle
     */
    async function getComplaints(year, make, model) {
        const cacheKey = `complaints-${year}-${make}-${model}`;
        const cached = getFromCache(cacheKey);

        if (cached) {
            return cached;
        }

        try {
            const response = await fetch(
                `https://api.nhtsa.gov/complaints/complaintsByVehicle?make=${encodeURIComponent(make)}&model=${encodeURIComponent(model)}&modelYear=${year}&format=json`
            );
            const data = await response.json();

            if (data.results) {
                saveToCache(cacheKey, data.results);
                return data.results;
            } else {
                return [];
            }
        } catch (error) {
            console.error('Error getting complaints:', error);
            throw error;
        }
    }

    /**
     * Cache management functions
     */
    function getFromCache(key) {
        const cached = cache.get(key);
        if (cached && Date.now() - cached.timestamp < CACHE_DURATION) {
            return cached.data;
        }
        return null;
    }

    function saveToCache(key, data) {
        cache.set(key, {
            data: data,
            timestamp: Date.now()
        });
    }

    function clearCache() {
        cache.clear();
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expose API
    window.SafeQuoteNHTSA = {
        init: init,
        getYears: getYears,
        getMakes: getMakes,
        getModels: getModels,
        getVehicleSafetyRating: getVehicleSafetyRating,
        getVehicleDetails: getVehicleDetails,
        searchVehicles: searchVehicles,
        getRecalls: getRecalls,
        getComplaints: getComplaints,
        clearCache: clearCache
    };

})();