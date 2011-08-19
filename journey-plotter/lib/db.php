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
    }
}
