#!/bin/bash
# Creates the sqlite insert statements for the points data

while IFS=";" read c1 c2 c3 c4 c5 c6 c7
do
    echo "insert into journeys (vehicle_id, timestamp, journey_id, lat, lng, accuracy, heading) values ($c1, $c2, $c3, $c4, $c5, $c6, $c7);"
done < ../data/points.csv 
