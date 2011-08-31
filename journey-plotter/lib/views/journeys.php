<?php

/**
 * Journey Plotter
 *
 * journeysView
 *
 **/

class journeysView extends plotterView
{
    protected $journeys;

    public function getJson(
        $params = NULL
    )
    {
        if (
            isset($params['swLat'])
            &&
            isset($params['swLng'])
            &&
            isset($params['neLat'])
            &&
            isset($params['neLng'])
        )
        {
            $vehicleId = NULL;
            $timestampFrom = NULL;
            $timestampTo = NULL;
            if (isset($params['vehicleId'])) { $vehicleId = $params['vehicleId']; }
            if (isset($params['timestampFrom'])) { $timestampFrom = $params['timestampFrom']; }
            if (isset($params['timestampTo'])) { $timestampTo = $params['timestampTo']; }
            return json_encode(
                $this->db->getJourneysForLatLngBounds(
                    $params['swLat'],
                    $params['swLng'],
                    $params['neLat'],
                    $params['neLng'],
                    $vehicleId,
                    $timestampFrom,
                    $timestampTo
                )
            );
        }
        return json_encode(array('error' => 'params not set'));
    }

    public function getHtml($params = NULL)
    {
        return <<<HTML
Error.
HTML;
    }


}


