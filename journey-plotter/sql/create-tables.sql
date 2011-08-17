create table journeys (
    id          integer primary key,
    vehicle_id  integer,
    timestamp   integer,
    journey_id  integer,
    lat         real,
    lng         real,
    accuracy    real,
    heading     real
);
