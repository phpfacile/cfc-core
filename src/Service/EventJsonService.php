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
            'url'             => '(not filled)',
            'event_type_name' => '(not filled)',
            'start_time'      => '(not filled)',
            'start_dt'        => $row['datetime_start'],
            'name'            => $row['name'],
            'location'        => $row['location_place'].', '.$row['location_country'],
            'capacity'        => '(not filled)',
            'id'              => $row['event_id'],
            'start_day'       => substr($row['datetime_start'], 0, 10),
            'venue_zip'       => '(not filled)',
            'is_official'     => '(not filled)',
            'id_obfuscated'   => '(not filled)',
        ];
        return $row4JSON;
    }
}
