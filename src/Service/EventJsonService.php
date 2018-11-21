<?php
namespace CFC\Service;

use PHPFacile\Event\Json\Service\EventJsonService as DefaultEventJsonService;

class EventJsonService extends DefaultEventJsonService
{
    /**
     * Customisation for the CFC project
     * {@inheritdoc}
     *
     * @param array $row Row as returned by the database
     *
     * @return array
     */
    public function getDbRowAsRowReadyForJSON($row)
    {
        $row4JSON = [
            'longitude'       => $row['geocoded_longitude'],
            'latitude'        => $row['geocoded_latitude'],
            'url'             => $row['url'],
            'event_type_name' => $row['type'],
            'start_time'      => substr($row['datetime_start'], 11),
            'start_dt'        => $row['datetime_start'],
            'name'            => $row['name'],
            'location'        => $row['location_place'].', '.$row['location_country'],
            'capacity'        => 0,
            'id'              => $row['event_id'],
            'start_day'       => substr($row['datetime_start'], 0, 10),
            'venue_zip'       => $row['postal_code'],
            'is_official'     => ('validated' === $row['status'])?1:0,
            'id_obfuscated'   => $row['event_id'],
        ];
        return $row4JSON;
    }
}
