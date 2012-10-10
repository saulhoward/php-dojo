<?php
/**
 * Route Cleaner
 *
 * Given an input CSV of points comprising a route, of the form:
 *
 *     lat,long,timestamp 
 *
 * this script will write a 'cleaned' route to the output file location 
 * provided.
 *
 * A cleaned route is one that has had potentially erroneous points removed.
 *
 * Possible outputs are:
 *
 *   * An HTML file, with the points plotted on a Google Map (will 
 *     require an internet connection to view).
 *   * A CSV file, in the same format as above
 * 
 * Params:
 *
 *  -i  Input CSV file location (required).
 *  -w  Output HTML file location (file will be created if it doesn't exist).
 *  -c  Output CSV file location (file will be created if it doesn't exist).
 *  -h  Help message.
 *
 * @author Saul <saul@saulhoward.com>
 */

// Get the flags
$opts = getopt('hi:c:w:');
if (isset($opts['h'])) {
    echo <<<TXT
Route Cleaner
-------------

Given a CSV of a route (lat,lon,timestamp), this will remove potentially 
erroneous points.

Usage: 

    php route-cleaner.php -i [INPUT_CSV] -w [OUTPUT_HTML]

    php route-cleaner.php -i [INPUT_CSV] -c [OUTPUT_CSV]

Params:

 -i  Input CSV file location (required).
 -w  HTML output file location (file will be created if it doesn't exist).
 -c  CSV output file location (file will be created if it doesn't exist).
 -h  This help message.

@author Saul <saul@saulhoward.com>
TXT;

    exit(0);
} 

if (isset($opts['i'])) {
    $inputFile = $opts['i'];
} else {
    die('No input file provided.' . "\n");
}

if (isset($opts['c'])) {
    $outputCSV = $opts['c'];
} 
if (isset($opts['w'])) {
    $outputHTML = $opts['w'];
}
if (!isset($outputHTML) && !isset($outputCSV)) {
    die('no output file location provided (CSV or HTML).' . "\n");
}

// Create the route
$route = new route();

// Iterate through the CSV
if (($handle = fopen($inputFile, "r")) !== FALSE) {
    while (($row = fgetcsv($handle, 0, ",")) !== FALSE) {
        // Add the rows to the route as point objects
        $route->addPoint(
            new point($row[0], $row[1], $row[2])
        );
    }
    fclose($handle);
} else {
    die('File ' . $inputFile . ' not found.' . "\n");
}

// Clean the route
if (isset($outputHTML)) {
    $dirtyRoute = clone $route;
}
$cleanedRoute = geographyHelper::cleanRoute($route);

// And output it.
if (isset($outputHTML)) {
    $html = htmlHelper::createRouteMap($cleanedRoute, $dirtyRoute);
    $success = file_put_contents(
        $outputHTML,
        $html
    );
    if ($success === FALSE) {
        die('Unable to write to file ' . $outputHTML . '.' . "\n");
    }
}
if (isset($outputCSV)) {
    if (($handle = fopen($outputCSV, "w")) !== FALSE) {
        //print_r($outputCSV);die;
        foreach ($cleanedRoute->getPoints() as $point) {
            fputcsv($handle, array(
                $point->lat,
                $point->lon,
                $point->timestamp
            ));
        }
        fclose($handle);
    } else {
        die('Unable to write to file ' . $outputCSV . '.' . "\n");
    }
}
exit(0);


// --


/* STATIC HELPER CLASSES  */

/**
 * Methods for cleaning routes through statistical inference.
 */
class geographyHelper
{

    /**
     * 'Clean' the route.
     *
     * With the intention of 'disregarding potentially erroneous 
     * points', this function will calculate two values for each point:
     *
     *   * the distance difference between this point and its predecessor
     *   * the time difference between this point and its predecessor
     *
     * We're looking for points which have gone a large distance in a 
     * short time. Ie, outliers in the set.
     *
     * @param route $route Dirty Route
     * @return route Clean Route
     */
    public static function cleanRoute(
        route $route
    )
    {
        $distTimeArr = array();

        $i = 0;
        $prevPoint = NULL;
        foreach ($route->getPoints() as $point) {
            if ($i == 0) {
                $prevPoint = $point;
            }
            $distDiff = self::distance($prevPoint, $point);
            $timeDiff = $point->timestamp - $prevPoint->timestamp;

            if ($timeDiff > 0) {
                $distTimeArr[$point->timestamp] = $distDiff / $timeDiff;
            } elseif ($distDiff > 0) {
                // Time is 0, but distance is more than zero -- bogus
                $route->removePoint($point);
            }
            $prevPoint = $point;
            $i++;
        }

        // Remove outliers
        $outliers = self::getOutliers($distTimeArr);
        foreach ($route->getPoints() as $point) {
            if (isset($outliers[$point->timestamp])) {
                $route->removePoint($point);
            }
        }

        return $route;
    }

    /**
     * Finds outliers in an array and returns them.
     *
     * Uses the 1.5 * Interquartile Range method.
     *
     * @param $arr an array
     * @return array
     */
    public static function getOutliers(
        $arr
    )
    {
        $origArr = $arr;
        $count = count($arr);
        sort($arr);

        // Quartiles
        $q1Key = round(.25 * ($count + 1)) - 1;
        $q3Key = round(.75 * ($count + 1)) - 1;
        $q1 = $arr[$q1Key];
        $q3 = $arr[$q3Key];
        $iqr = $q3 - $q1;
        $lower = $q1 - (1.5 * $iqr);
        $upper = $q3 + (1.5 * $iqr);
        $outliers = array();
        foreach ($origArr as $k => $v) {
            if ($v < $lower || $v > $upper) {
                $outliers[$k] = $v;
            }
        }
        return $outliers;
    }

    /**
     * Distance between two points (lat,lon) in degrees
     *
     * @param point $point1
     * @param point $point2
     */
    public static function distance(
        point $point1,
        point $point2
    ) 
    {
        $theta = $point1->lon - $point2->lon;
        $dist = sin(deg2rad($point1->lat)) 
            * sin(deg2rad($point2->lat))
            + cos(deg2rad($point1->lat)) 
            * cos(deg2rad($point2->lat))
            * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        return $dist * 60;
    }

    /**
     * Get the center point of a route.
     *
     * Simplistic method: averages the lat and lon of all points.
     *
     * @return point $point
     */
    public static function getCenterPoint(
        route $route
    )
    {
        $totLat = 0;
        $totLon = 0;
        $totTS = 0;
        foreach ($route->getPoints() as $point) {
            $totLat += $point->lat;
            $totLon += $point->lon;
            $totTS += $point->timestamp;
        }
        $count = count($route->getPoints());
        $meanLat = $totLat / $count;
        $meanLon = $totLon / $count;
        $meanTS = $totTS / $count;

        return new point($meanLat, $meanLon, $meanTS);
    }
}


/**
 * Helper class to create an HTML plot of a route, using the Google Maps 
 * API.
 *
 */
class htmlHelper
{
    /**
     * Returns HTML for a route map,
     * comparing two routes.
     *
     * @param route $route1
     * @param route $route2
     */
    public static function createRouteMap(
        route $route1,
        route $route2
    )
    {
        //  Make a Javascript array from the route data
        $jsData = 'var route1,route2,center;' . "\n";
        $jsData .= 'route1 = [';
        foreach ($route1->getPoints() as $point) {
            $jsData .= '[' . $point->lat . ',' . $point->lon . '],';
        }
        $jsData .= '];' . "\n";
        $jsData .= 'route2 = [';
        foreach ($route2->getPoints() as $point) {
            $jsData .= '[' . $point->lat . ',' . $point->lon . '],';
        }
        $jsData .= '];' . "\n";
        $centerPoint = geographyHelper::getCenterPoint($route1);
        $jsData .= 'center = [' . $centerPoint->lat . ',' . $centerPoint->lon . '];';

        // Add the JS & CSS to the HTML
        $html = self::htmlTemplate;
        $html = str_replace('<% CSS %>', self::css, $html);
        $html = str_replace('<% JS_LIB %>', self::js, $html);
        $html = str_replace('<% JS_DATA %>', $jsData, $html);
        return $html;
    }

    /**
     * HTML Template for the output page.
     */
    const htmlTemplate = <<<HTML
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Hailo Codetest</title>
        <script type="text/javascript" src="https://www.google.com/jsapi"></script>
        <script type="text/javascript">
            google.load("maps", "3",{"other_params":"sensor=false"});
        </script>
        <style>
            <% CSS %>
        </style>
    </head>
    <body>
        <header>
            <h1>Hailocab Test by <span class="author">Saul &lt;saul@saulhoward.com&gt;</span></h1>
        </header>
        <div id="map"></div>
        <div id="controls">
            <p class="instructions">
            Click to toggle route display:
            </p>
            <ul>
                <li><a href="#" id="route1Ctl">Clean Route</a></li>
                <li><a href="#" id="route2Ctl">Dirty Route</a></li>
            </ul>
        </div>
        <script>
            <% JS_DATA %>

            <% JS_LIB %>
        </script>
    </body>
</html>
HTML;

    /**
     * Javascript library functions for the output page.
     */
    const js = <<<JS
function drawMap(
    el,
    center
) 
{
    var latlng = new google.maps.LatLng(center[0], center[1]);
    var myOptions = {
        zoom: 14,
        center: latlng,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        panControl: false,
        zoomControl: true,
        zoomControlOptions: {
            position: google.maps.ControlPosition.LEFT_CENTER
        }
    };
    map = new google.maps.Map(document.getElementById("map"), myOptions);
    return map;
}

function plotRoute(
    map,
    route,
    color
) 
{
    var routePoints = [];
    for (var i = 0; i < route.length; i++) {
        var point = new google.maps.LatLng(route[i][0],route[i][1]);
        routePoints.push(point);
    }
    var routePath = new google.maps.Polyline({
        path: routePoints,
        strokeColor: color,
        strokeOpacity: 1.0,
        strokeWeight: 5
    });
    routePath.setMap(map);
    routePath.setVisible(true);
    return routePath;
}

function toggleVisible(plot) {
    plot.getVisible() ? plot.setVisible(false) : plot.setVisible(true);
}


/* Setup the map and routes */
var map = drawMap('map', center);

var route1Ctl = document.getElementById('route1Ctl')
var route2Ctl = document.getElementById('route2Ctl')

route2Plot = plotRoute(map, route2, '#FF4040');
route1Plot = plotRoute(map, route1, '#4040FF');
toggleVisible(route2Plot);

route1Ctl.onclick = function() {toggleVisible(route1Plot); return false;}
route2Ctl.onclick = function() {toggleVisible(route2Plot); return false;}


JS;

    /**
     * CSS for the output page
     */
    const css = <<<CSS
body {
    background: #45484d;
    font-family: "Helvetica Neue", "Helvetica", Arial, sans;
    color: white;
    height: 100%;
    padding: 0;
    margin: 0;
}
html {
    height: 100% ;
}
header {
    position: absolute;
    z-index: 10;
    background: #45484d; /* Old browsers */
    background: -moz-linear-gradient(top, #45484d 0%, #000000 100%); /* FF3.6+ */
    background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#45484d), color-stop(100%,#000000)); /* Chrome,Safari4+ */
    background: -webkit-linear-gradient(top, #45484d 0%,#000000 100%); /* Chrome10+,Safari5.1+ */
    background: -o-linear-gradient(top, #45484d 0%,#000000 100%); /* Opera 11.10+ */
    background: -ms-linear-gradient(top, #45484d 0%,#000000 100%); /* IE10+ */
    background: linear-gradient(to bottom, #45484d 0%,#000000 100%); /* W3C */
    border-bottom: 5px solid #FDB424;
    padding: 10px;
    top: 0;
    width: 100%;
    -webkit-box-shadow: 0px 0px 10px 5px rgba(0, 0, 0, 0.4);
    box-shadow: 0px 0px 10px 5px rgba(0, 0, 0, 0.4);
}
header h1 {
    line-height: 0;
    font-size: 1.4em;
    color: #ccc;
}
header h1 span {
    color: #f5f5f5;
}
#map {
    width: 100%;
    height: 100%;
}
#controls {
    position: absolute;
    top: 100px;
    right: 20px;
    padding: 10px;
    -webkit-border-radius: 15px;
    border-radius: 15px;
    background: #45484d; /* Old browsers */
    background: -moz-linear-gradient(top, #45484d 0%, #000000 100%); /* FF3.6+ */
    background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#45484d), color-stop(100%,#000000)); /* Chrome,Safari4+ */
    background: -webkit-linear-gradient(top, #45484d 0%,#000000 100%); /* Chrome10+,Safari5.1+ */
    background: -o-linear-gradient(top, #45484d 0%,#000000 100%); /* Opera 11.10+ */
    background: -ms-linear-gradient(top, #45484d 0%,#000000 100%); /* IE10+ */
    background: linear-gradient(to bottom, #45484d 0%,#000000 100%); /* W3C */
    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#45484d', endColorstr='#000000',GradientType=0 ); /* IE6-9 */
    -webkit-box-shadow: 0px 0px 10px 5px rgba(0, 0, 0, 0.4);
    box-shadow: 0px 0px 10px 5px rgba(0, 0, 0, 0.4);
    border: 3px solid #FDB424;
    }
#controls ul {
    text-align: center;
    margin: 0 auto;
    padding: 10px;
}
#controls ul li {
    list-style-type: none;
    line-height: 2em;
}
#controls p.instructions {
    color: #f5f5f5;
    text-align: center;
    padding-bottom: 0;
    margin-bottom: 0;
    padding-left: 10px;
    padding-right: 10px;
}
#controls a {
    text-decoration: none;
    font-size: 1.4em;
}
#controls a#route1Ctl {
    color: #4040FF;
}
#controls a#route2Ctl {
    color: #FF4040;
}
CSS;

}

// --

/* DATA CLASSES (point, route) */

/**
 * Route class.
 *
 * Represents a route made up of point objects, in timestamp order.
 */
class route
{
    /**
     * Array of point objects.
     *
     * Of the form:
     *
     *     [timestamp] => point
     *
     * This is so we can order on the timestamp easily.
     *
     * The array is always ordered correctly by timestamp.
     *
     * @var array $points
     */
    private $points;

    /**
     * Add a point to the route
     * This will sort the points to maintain order
     *
     * @param point $point
     */
    public function addPoint(
        point $point
    )
    {
        $this->points[$point->timestamp] = $point;

        // Expensive to do this every time, but it means the data will 
        // be always in order.
        ksort($this->points);
    }

    /**
     * Get all points
     *
     * @return array points
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * Remove a point from the route
     *
     * @param point $point
     */
    public function removePoint($point)
    {
        unset($this->points[$point->timestamp]);
    }

    /**
     * Get the first point
     *
     * @return point $point
     */
    public function getStartPoint()
    {
        $p = $this->points;
        return reset($p);
    }

    /**
     * Get the last point
     *
     * @return point $point
     */
    public function getEndPoint()
    {
        $p = $this->points;
        return end($p);
    }
}

/**
 * Point class
 *
 * Represents one point, a combination of lat, long and timestamp.
 */
class point
{
    /**
     * @var $lat Latitude
     */
    public $lat;

    /**
     * @var $lon Longitude
     */
    public $lon;

    /**
     * @var $timestamp Timestamp
     */
    public $timestamp;

    /**
     * Constructor
     *
     * @param int lat
     * @param int lon
     * @param int timestamp
     */
    public function __construct(
        $lat,
        $lon,
        $timestamp
    )
    {
        $this->lat = $lat;
        $this->lon = $lon;
        $this->timestamp = $timestamp;
    }
}

