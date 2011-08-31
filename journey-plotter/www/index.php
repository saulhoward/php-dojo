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
define('LIB_PATH', dirname($_SERVER['DOCUMENT_ROOT']) . '/lib/');

require_once(LIB_PATH . "/view.php"); 
require_once(LIB_PATH . "views/index.php"); 
require_once(LIB_PATH . "views/journeys.php"); 
require_once(LIB_PATH . "db.php"); 

/**
 * Get the DB
 */
$db = new plotterDb();

/**
 * Render the View
 */
$url = parse_url($_SERVER['REQUEST_URI']);
$matches = array();
$resource = $url['path'];
$format = '';
preg_match('/([^\.]*)\.(.*)$/', $url['path'], $matches);
if (isset($matches[1]))
{
    $resource = $matches[1];
}
if (isset($matches[2]))
{
    $format = $matches[2];
}

switch ($resource) {
    case '/journeys':
        $view = new journeysView($db);
        break;
    case '/':
    default:
        $view = new indexView($db);
}

$view->renderPage($format);
