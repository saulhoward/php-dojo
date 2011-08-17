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
    $db;

    public function __construct()
    {
        $db = $_SERVER['DOCUMENT_ROOT']."/../plotter.db"; 
        $handle = sqlite_open($db) or die("Could not open database"); 

        $query = "SELECT * FROM journeys"; 
        $result = sqlite_query($handle, $query) or die("Error in query: 
            ".sqlite_error_string(sqlite_last_error($handle))); 

        $journeys = array();

        if (sqlite_num_rows($result) > 0) { 
            while($row = sqlite_fetch_array($result)) { 
                $journeys[][ = $row;
            } 
        } 
    }
}
