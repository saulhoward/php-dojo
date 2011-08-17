<?php
/**
 * Journey Plotter
 *
 * index.php
 *
 * Handles the organisational logic
 */

/**
 * Include the Library files
 */
require_once($_SERVER['DOCUMENT_ROOT']."/lib/view.php"); 
require_once($_SERVER['DOCUMENT_ROOT']."/lib/db.php"); 

$journeys = plotterDb::getJourneys();

/**
 * Render the View
 */
$plotterView = new plotterView($journeys);
$plotterView->renderPage();

