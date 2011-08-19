function initialize() {
    alert(window.location.host);

    var map = new GMap2(document.getElementById("map_canvas"));
    map.setCenter(new GLatLng(38.693482,-75.057564), 16);
    map.setMapType(G_PHYSICAL_MAP);

    var point = new GLatLng(38.693281,-75.075159);
    map.addOverlay(new GMarker(point));
}

