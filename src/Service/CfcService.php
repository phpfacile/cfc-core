<?php
namespace CFC\Service;

class CfcService
{
    /**
     * Service to store and retrieve events
     *
     * @var $eventService
     */
    protected $eventService;

    /**
     * Service to geocode places given in events
     *
     * @var $geocodingService
     */
    protected $geocodingService;

    /**
     * Defines the event service
     *
     * @param EventService $eventService Event service
     *
     * @return void
     */
    public function setEventService($eventService)
    {
        $this->eventService = $eventService;
    }

    /**
     * Defines the geocoding service
     *
     * @param GeocodingService $geocodingService Geocoding service
     *
     * @return void
     */
    public function setGeocodingService($geocodingService)
    {
        $this->geocodingService = $geocodingService;
    }

    /**
     * Saves event data based on POSTed data
     *
     * @param StdClass $event    Event retrieved from POST data
     * @param StdClass $location Location selected for this event place amoung results returned by the geocoding service
     *
     * @return void
     */
    public function saveFormEvent($event, $location)
    {
        // Populate $geoEvent with data from POST and computed $location
        // TODO Take into account locale ??
        // $geoEvent->locale = ...
        $geoEvent       = new \StdClass();
        $geoEvent->name = $event->name;
        $geoEvent->dateTimeStart = $event->dateStart;
        $geoEvent->dateTimeEnd   = $event->dateEnd;

        $geoEvent->address       = new \StdClass();
        $geoEvent->address->name = $event->address;

        $geoEvent->place        = new \StdClass();
        $geoEvent->place->place = $event->place;

        // TAKE CARE country isocode is not available for mapping
        // $geoEvent->place->country->isocode = $event->country->isocode;
        $geoEvent->place->country       = new \StdClass();
        $geoEvent->place->country->name = $event->country;

        $this->geocodingService->completeWithMissingRequiredFields($location, ['timezone']);
        $geoEvent->place->geocoding = $location;

        // Here we assume webserver and database clocks are synchronized (usually it's OK as they are on the same server)
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $geoEvent->place->geocoding->dateTimeUTC = $now->format('Y-m-d H:i:s');

        $this->eventService->insertStdClassEvent($geoEvent);
    }

    /**
     * Returns a list of events as a JSON string
     *
     * @return string JSON encoded list of events
     */
    public function getEventsAsJSON()
    {
        $filter           = null;
        $eventJsonService = new EventJsonService();

        return json_encode($this->eventService->getEventsAsArrayReadyForJSON($filter, $eventJsonService));
    }
}
