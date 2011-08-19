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
define('LIB_PATH', dirname($_SERVER['DOCUMENT_ROOT'] . "../lib/"));

require_once(LIB_PATH . "view.php"); 
require_once(LIB_PATH . "views/index.php"); 
require_once(LIB_PATH . "views/journeys.php"); 
require_once(LIB_PATH . "db.php"); 

/**
 * Get the DB
 */
$db = new plotterDb();
$journeys = $db->getJourneys();

/**
 * Render the View
 */
switch ($_SERVER['REQUEST_URI']) {
    case '/journeys/all':
        $view = new journeysView($journeys);
        break;
    case '/':
    default:
        $view = new indexView();
}
$view->renderPage();
