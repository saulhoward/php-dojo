#!/bin/bash
if [ ! -f /usr/bin/sqlite ] 
then
    echo "/usr/bin/sqlite not found"
    exit 1
fi

rm plotter.db
sqlite plotter.db < ./sql/create-tables.sql
sqlite plotter.db < ./sql/insert-data.sql

