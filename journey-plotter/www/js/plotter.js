var journey_plotter = function () {
    // Enable console logging 
    // var debug = true;
    var map = false;
    var initialize = function() {
        map = new GMap2(document.getElementById("map_canvas"));
        map.setCenter(new GLatLng(51.5094,-0.1183), 16);
        map.setMapType(G_PHYSICAL_MAP);

        var bounds = map.getBounds();
        var sw = bounds.getSouthWest();
        var ne = bounds.getNorthEast();

        var url = '/journeys.json?swLat=' + sw.lat() + '&swLng=' + sw.lng() + '&neLat=' + ne.lat() + '&neLng=' + ne.lng() + '&vehicleId=3';
        console.log(url);
        $.get(url,function(data) {
                // console.log(data);
                // console.log(data);
                plotJourneys(data);
            });


    }

    var plotJourneys = function(vehicles) {

        _.each(vehicles, function(vehicle) {
                _.each(vehicle, function(journey) {
                        var point = new GLatLng(journey.lat,journey.lng);
                        map.addOverlay(new GMarker(point));
                    });

            });
    }

    initialize();
}
/**
 * Attach to jQuery Document ready...
 */
$(journey_plotter);

