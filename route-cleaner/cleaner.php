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
 * A cleaned route is one that has had suspicious points removed.
 *
 * Params:
 *
 *  -i  Input CSV file location.
 *  -o  Output file location (file will be created if it doesn't exist).
 *
 * @author Saul <saul@saulhoward.com>
 */

// Get the flags
$opts = getopt('i:o:');
if (isset($opts['i'])) {
    $inputFile = $opts['i'];
} else {
    die('No input file provided.' . "\n");
}
if (isset($opts['o'])) {
    $outputFile = $opts['o'];
} else {
    die('no output file location provided.' . "\n");
}

// Create the route
$route = new route();

// Iterate through the CSV
if (($handle = fopen($inputFile, "r")) !== FALSE) {
    while (($row = fgetcsv($handle, 0, ",")) !== FALSE) {
        // Add the rows to the route as point objects
        $route->addPoint(
            new point(
                $row[0],
                $row[1],
                $row[2]
            )
        );
    }
    fclose($handle);
} else {
    die('File ' . $inputFile . ' not found.' . "\n");
}

// --

// Clean the route
$cleanedRoute = geographyHelper::cleanRoute($route);

print_r($cleanedRoute->getPoints());die;



// -- STATIC HELPER CLASSES 

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
     * From these two, a third number can be calculated:
     * 
     *
     * We're looking for points which have gone a large distance in a 
     * short time....
     *
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

            //$distTimeArr[] = $distDiff / $timeDiff;
            //$distTimeArr[] = $distDiff;
            $distTimeArr[] = $timeDiff;

            $prevPoint = $point;
            $i++;
        }

        print_R($distTimeArr);die;

        return $route;
    }


    /**
     * Distance between two points in degrees
     */
    public static function distance(
        point $pointOne,
        point $pointTwo
    ) 
    {
        $theta = $pointOne->lon - $pointTwo->lon;
        $dist = sin(deg2rad($pointOne->lat)) 
            * sin(deg2rad($pointTwo->lat))
            + cos(deg2rad($pointOne->lat)) 
            * cos(deg2rad($pointTwo->lat))
            * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        return $dist * 60;
    }
}


// -- DATA CLASSES (point, route)

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

    public function addPoint(
        point $point
    )
    {
        $this->points[$point->timestamp] = $point;

        // Expensive to do this every time, but it means the data will 
        // be always in order.
        ksort($this->points);
    }

    public function getPoints()
    {
        return $this->points;
    }
}

/**
 * Point class
 *
 * Represents one point, a combination of lat, long and timestamp.
 */
class point
{
    public $lat;
    public $lon;
    public $timestamp;

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


