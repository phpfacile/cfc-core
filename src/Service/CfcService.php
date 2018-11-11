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

    public function getGeocodingService()
    {
        return $this->geocodingService;
    }

    public static function getEventSubmissionFromFormEventSubmission($formEventSubmission)
    {
        $formSubmitter = $formEventSubmission->submitter;
        $formEvent = $formEventSubmission->event;

        // This could have been done by $submitter = clone $formSubmitter
        // but Facade design pattern is a better way
        $submitter = new \StdClass();
        $submitter->name  = $formSubmitter->name;
        $submitter->email = $formSubmitter->email;

        $event       = new \StdClass();
        $event->name = $formEvent->name;
        $event->type = $formEvent->type;
        $event->url  = $formEvent->url;
        $event->dateTimeStart = $formEvent->dateStart;
        $event->dateTimeEnd   = $formEvent->dateEnd;

        // This could have been done by $event->location = clone $formEvent->location
        // but Facade design pattern is a better way
        $event->location          = new \StdClass();
        if (property_exists($formEvent->location, 'address')) {
            $event->location->address = $formEvent->location->address;
        }

        $event->location->place       = new \StdClass();
        $event->location->place->name = $formEvent->location->place->name;

        $event->location->place->country       = new \StdClass();
        $event->location->place->country->name = $formEvent->location->place->country->name;
        $event->location->place->country->code = $formEvent->location->place->country->code;

        $eventSubmission = new \StdClass();
        if (property_exists($formEventSubmission, 'id')) {
            $eventSubmission->id = $formEventSubmission->id;
        }

        $eventSubmission->submitter = $submitter;
        $eventSubmission->event = $event;
        $eventSubmission->locale = $formEventSubmission->locale;

        return $eventSubmission;
    }

    /**
     * Saves event data based on POSTed data
     *
     * @param StdClass $formEventSubmission All form data
     *
     * @return void
     */
    public function saveNewFormEventSubmission($formEventSubmission)
    {
        // TODO Implement input data validity check

        $eventSubmission = self::getEventSubmissionFromFormEventSubmission($formEventSubmission);
        $this->eventService->insertStdClassEventSubmission($eventSubmission);
    }

    public function updateAndValidateFormEventSubmission($formEventSubmission, $geocodedPlace)
    {
        $eventSubmission = self::getEventSubmissionFromFormEventSubmission($formEventSubmission);

        $this->geocodingService->completeWithMissingRequiredFields($geocodedPlace, ['timezone']);
        $eventSubmission->event->location->place->geocoding = $geocodedPlace;

        $eventSubmission->status = 'validated';

        $this->eventService->updateStdClassEventSubmission($eventSubmission);
    }

    public static function getFormEventSubmissionFromEventSubmission($eventSubmission)
    {
        $formEventSubmission = new \StdClass();
        // TAKE CARE: Better use a facade design pattern
        $formEventSubmission->submitter = clone $eventSubmission->submitter;

        $event = $eventSubmission->event;

        $formEvent            = new \StdClass();
        $formEvent->name      = $event->name;
        $formEvent->type      = $event->type;
        $formEvent->url       = $event->url;
        $formEvent->dateStart = substr($event->dateTimeStart, 0, 10);
        $formEvent->dateEnd   = substr($event->dateTimeEnd, 0, 10);

        // TAKE CARE: Better use a facade design pattern
        $formEvent->location = clone $event->location;

        $formEventSubmission->event  = $formEvent;
        $formEventSubmission->id     = $eventSubmission->id;
        $formEventSubmission->locale = $eventSubmission->locale;

        return $formEventSubmission;
    }

    public function getNextFormEventSubmissionToBeValidated()
    {
        $eventSubmission = $this->eventService->getNextEventSubmissionToBeValidated();
        if (null === $eventSubmission) return null;

        return self::getFormEventSubmissionFromEventSubmission($eventSubmission);
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
