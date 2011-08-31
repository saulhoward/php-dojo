<?php

/**
 * Journey Plotter
 *
 * db.php
 *
 * Handles the organisational logic
 */

class plotterDb
{
    protected $db;
    protected $handle;

    public function __construct()
    {
        $this->db = $_SERVER['DOCUMENT_ROOT']."/../plotter.db"; 
        $this->handle = sqlite_open($this->db) or die("Could not open database"); 

    }

    public function getJourneys()
    {
        $query = "SELECT * FROM journeys"; 
        $result = sqlite_query($this->handle, $query) or die("Error in query: 
            ".sqlite_error_string(sqlite_last_error($this->handle))); 

        $journeys = array();

        if (sqlite_num_rows($result) > 0) { 
            while($row = sqlite_fetch_array($result)) { 
                $journeys[] = $row;
            } 
        } 
        return $journeys;
    }

    public function
        getJourneysForLatLngBounds(
            $swLat,
            $swLng,
            $neLat,
            $neLng,
            $vehicleId = NULL,
            $timestampFrom = NULL,
            $timestampTo = NULL
        )
    {
        $query = <<<SQL
SELECT * FROM journeys
WHERE 
lat >= $swLat 
AND
lat <= $neLat
AND
lng >= $swLng
AND
lng <= $neLng

SQL;

        if (isset($vehicleId)) {
            $query .= 'AND vehicle_id = ' . $vehicleId . ' ';
        }

        if (isset($timestampFrom)) {
            $query .= 'AND timestamp >= ' . $timestampFrom . ' ';
        }

        if (isset($timestampTo)) {
            $query .= 'AND timestamp <= ' . $timestampTo . ' ';
        }

    $query .= <<<SQL
GROUP BY vehicle_id, journey_id
SQL;

        $result = sqlite_query($this->handle, $query) or die("Error in query: 
            ".sqlite_error_string(sqlite_last_error($this->handle))); 

        $journeys = array();

        if (sqlite_num_rows($result) > 0) { 
            while($row = sqlite_fetch_array($result)) { 
                $vehicle_id = $row['vehicle_id'];
                $journey_id = $row['journey_id'];
                $journeys[$vehicle_id][$journey_id] = $row;
            } 
        } 
        return $journeys;
    }
}
