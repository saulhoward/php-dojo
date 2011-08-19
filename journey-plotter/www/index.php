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
require_once($_SERVER['DOCUMENT_ROOT']."../lib/view.php"); 
require_once($_SERVER['DOCUMENT_ROOT']."../lib/db.php"); 

/**
 * Get the DB
 */
$db = new plotterDb();
$journeys = $db->getJourneys();

/**
 * Render the View
 */
$view = new plotterView($journeys);
$view->renderPage();

switch (HttpRequest::getUrl()) {
case '/':

case '/ajax-endpoint':



}
