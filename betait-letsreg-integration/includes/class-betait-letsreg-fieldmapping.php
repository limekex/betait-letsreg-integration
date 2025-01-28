<?php

/**
 * The file that defines the standard API paths for LetsReg integration at the time of production
 *
 *
 * @link       http://betait.no/betaletsreg
 * @since      1.0.0
 *
 * @package    Betait_Letsreg
 * @subpackage Betait_Letsreg/includes
 */

 /**
  * Maps LetsReg API fields to WP fields/meta for each target post type.
  *
  * For The Events Calendar, we’ll store date/time in _EventStartDate / _EventEndDate, etc.
  * For your custom CPT “lr-arr”, we’ll store them in custom meta.
  * For WP’s native posts, we might store them in custom meta or rely on shortcodes to display them.
  */
 
 return array(
 
 // The Events Calendar (tribe_events)
'tribe_events' => array(

    // Basic WP fields
    'post_title'   => 'name',         // e.g. event.name => post_title
    'post_content' => 'description',  // e.g. event.description => post_content
    'post_status'  => 'publish',      // Could be dynamic if event.active == false => 'draft'

    // The Events Calendar meta
    'meta' => array(

        /**
         * The Events Calendar commonly uses these keys:
         *   _EventStartDate   -> e.g. "2025-01-16 18:30:00"
         *   _EventEndDate     -> e.g. "2025-01-16 20:00:00"
         *   _EventAllDay      -> "1" or "0"
         *   _EventShowMap     -> "1" or "0"
         *   _EventShowMapLink -> "1" or "0"
         *   _EventVenueID     -> references a separate tribe_venue post
         *   _EventOrganizerID -> references a separate tribe_organizer post
         */
        '_EventStartDate' => 'startDate', // event.startDate => _EventStartDate
        '_EventEndDate'   => 'endDate',   // event.endDate   => _EventEndDate
        '_EventHideFromUpcoming'    => 'searchable',    // event.searchable => _EventHideFromUpcoming

        // Optional: If you want to store a fallback venue address (without creating a separate Venue):
        '_VenueAddress' => 'location.address1',

        // If you also want to store the entire "location" data in custom meta:
        '_VenueName'     => 'location.name',
        '_VenueAddress' => 'location.address1',
        //'lr_location_address2' => 'location.address2',
        '_VenueZip' => 'location.postCode',
        '_VenueCity'     => 'location.city',
        '_VenueCountry'   => 'location.county',
        '_VenueLng'     => 'location.longitude',
        '_VenueLat'      => 'location.latitude',

        // Or store "venue" sub-fields if the event explicitly has them
        //'_venue_id'   => 'venue.id',
        //'_VenueName' => 'venue.name',
        //'_VenueAddress' => 'venue.address1',
        //'lr_venue_address2' => 'venue.address2',
        //'_VenueZip' => 'venue.postCode',
        //'_VenueCity' => 'venue.city',

        // For an Organizer "object"
        // (We usually create a separate post with Tribe__Events__API::createOrganizer)
        // but you can still store some raw info:
        '_ExtOrganizer_id'        => 'organizer.id',
        '_ExtOrganizer_affiliate' => 'organizer.affiliateId',
        '_OrganizerOrganizer'      => 'organizer.name', //also post title for organizer cpt (tribe_organizer)
        '_OrganizerOrigin'        => 'organizer.origin',
        '_OrganizerPhone' => 'organizer.phone', //not avaliable from the API, add manually.
        '_OrganizerWebsite'      => 'organizer.website', //not avaliable from the API, add manually.

        // Example: If you want custom meta for "contactPerson" if it differs from official "organizer"
        'lr_contact_name'   => 'contactPerson.name',
        'lr_contact_email'  => 'contactPerson.email',
        'lr_contact_phone'  => 'contactPerson.telephone',
        'lr_contact_mobile' => 'contactPerson.mobile',

        // Additional booleans
        'lr_active'           => 'active',
        'lr_published'        => 'published',
        'lr_searchable'       => 'searchable',
        'lr_isPaidEvent'      => 'isPaidEvent',
        'lr_isArchived'       => 'isArchived',
        'lr_hasWaitinglist'   => 'hasWaitinglist',

        // Key times/dates besides start/end
        'lr_lastUpdate'       => 'lastUpdate',
        'lr_registrationStartDate' => 'registrationStartDate',
        'lr_registrationEndDate'   => 'registrationEndDate',
        'lr_startDateVisible'       => 'startDateVisible',
        'lr_endDateVisible'         => 'endDateVisible',
        'lr_registrationEndDateVisible' => 'registrationEndDateVisible',

        // More event info
        '_EventURL'          => 'eventUrl',
        'lr_imageUrl'          => 'imageUrl',
        'lr_imageThumbUrl'     => 'imageThumbnailUrl',
        'lr_ordersTotalSum'    => 'ordersTotalSum',
        'lr_registered'        => 'registeredParticipants',
        'lr_maxAllowed'        => 'maxAllowedRegistrations',
        'lr_availableRegistrations' => 'availableRegistrations',
        'lr_bccEmails'         => 'bccConfirmationEmailRecipients', // if you want that
        'lr_externalPublish'   => 'externalPublish',
        'lr_externalHistoricalPublish' => 'externalHistoricalPublish',

        // registrationPage as a sub-object
        'lr_registrationPage'  => 'registrationPage',

        // Arrays we might store as JSON:
        'lr_coOrganizers'   => 'coOrganizers',
        'lr_prices'         => 'prices',
        'lr_categories'     => 'categories',
        'lr_tags'           => 'tags',
        'lr_municipalities' => 'municipalities',
        'lr_areas'          => 'areas',
        'lr_fields'         => 'fields',
        'lr_instructors'    => 'instructors',
        'lr_course'         => 'course',
        'lr_riskAssessment' => 'riskAssessment',

        // etc.
    ),
),
 
 // Your custom CPT (slug = "lr-arr")
'lr-arr' => array(
    'post_title'   => 'name',
    'post_content' => 'description',
    'post_status'  => 'publish', 
    // e.g. you could set 'draft' if event.active == false, etc.

    'meta' => array(

        // Basic identifiers & booleans
        'lr_id'               => 'id',
        'lr_active'           => 'active',
        'lr_published'        => 'published',
        'lr_searchable'       => 'searchable',
        'lr_isPaidEvent'      => 'isPaidEvent',
        'lr_isArchived'       => 'isArchived',
        'lr_hasWaitinglist'   => 'hasWaitinglist',

        // Key times/dates
        'lr_lastUpdate'       => 'lastUpdate', // from spreadsheet
        'lr_startDate'        => 'startDate',
        'lr_endDate'          => 'endDate',

        /*
         * The spreadsheet says "registrationStartDate: boolean", 
         * but also "string($date-time)" in some places. 
         * If it's truly a date/time, store it as below:
         */
        'lr_registrationStartDate' => 'registrationStartDate',

        'lr_registrationEndDate'   => 'registrationEndDate',

        // Visibility toggles
        'lr_startDateVisible'         => 'startDateVisible',
        'lr_endDateVisible'           => 'endDateVisible',
        'lr_registrationEndDateVisible' => 'registrationEndDateVisible',

        // Additional event info
        'lr_eventUrl'            => 'eventUrl',
        'lr_imageUrl'            => 'imageUrl',           // doc had "imageUr"? 
        'lr_imageThumbUrl'       => 'imageThumbnailUrl',  // from doc
        'lr_registered'          => 'registeredParticipants',
        'lr_maxAllowed'          => 'maxAllowedRegistrations',
        'lr_availableRegistrations' => 'availableRegistrations',

        /*
         * If your doc included "pageHits" or "ordersTotalSum" etc.
         * you can keep them here:
         */
        'lr_pageHits'         => 'pageHits',
        'lr_ordersTotalSum'   => 'ordersTotalSum',

        /*
         * contactPerson is described as a JSON array in the doc, 
         * but you’re already splitting out sub-fields if you want:
         */
        'lr_contact_name'   => 'contactPerson.name',
        'lr_contact_phone'  => 'contactPerson.telephone',
        'lr_contact_mobile' => 'contactPerson.mobile',
        'lr_contact_email'  => 'contactPerson.email',

        /*
         * location is also a JSON object with multiple sub-fields:
         */
        'lr_location_name'     => 'location.name',
        'lr_location_address1' => 'location.address1',
        'lr_location_address2' => 'location.address2',
        'lr_location_postCode' => 'location.postCode',
        'lr_location_city'     => 'location.city',
        'lr_location_county'   => 'location.county',
        'lr_location_long'     => 'location.longitude',
        'lr_location_lat'      => 'location.latitude',

        /*
         * venue is a separate JSON object, if distinct from location
         */
        'lr_venue_id'      => 'venue.id',
        'lr_venue_name'    => 'venue.name',
        'lr_venue_address1'=> 'venue.address1',
        'lr_venue_address2'=> 'venue.address2',
        'lr_venue_postCode'=> 'venue.postCode',
        'lr_venue_city'    => 'venue.city',

        /*
         * organizer is also an object
         */
        'lr_organizer_id'        => 'organizer.id',
        'lr_organizer_affiliate' => 'organizer.affiliateId',
        'lr_organizer_name'      => 'organizer.name',

        /*
         * Arrays: e.g. prices, fields (and sub-fields)
         * you can store them as JSON or parse further
         */
        'lr_prices' => 'prices', // entire array
        // 'lr_prices.[i].id' => 'prices[i].id'  (if you want sub-fields)

        'lr_fields' => 'fields', // entire fields array

        /*
         * If you have "fields.options" you can store that 
         * either combined or parse further
         */

        // Possibly store these arrays as JSON:
        'lr_course'         => 'course',
        'lr_instructors'    => 'instructors',

        // If your doc mentions "coOrganizers", "tags", "municipalities", etc.
        // add them as well:
        'lr_coOrganizers'   => 'coOrganizers',
        'lr_tags'           => 'tags',
        'lr_municipalities' => 'municipalities',
        'lr_areas'          => 'areas',

    ),
),


 
     // WordPress default post (slug = "post")
'post' => array(
    'post_title'   => 'name',        // event.name => post_title
    'post_content' => 'description', // event.description => post_content
    'post_status'  => 'publish',     // e.g. set 'draft' if event.active == false

    'meta' => array(

        // Basic identifiers & booleans
        'lr_id'               => 'id',
        'lr_active'           => 'active',
        'lr_published'        => 'published',
        'lr_searchable'       => 'searchable',
        'lr_isPaidEvent'      => 'isPaidEvent',
        'lr_isArchived'       => 'isArchived',
        'lr_hasWaitinglist'   => 'hasWaitinglist',

        // Times/dates
        'lr_lastUpdate'       => 'lastUpdate',               // from doc
        'lr_startDate'        => 'startDate',
        'lr_endDate'          => 'endDate',
        'lr_registrationStartDate' => 'registrationStartDate',
        'lr_registrationEndDate'   => 'registrationEndDate',

        // Visibility toggles
        'lr_startDateVisible'         => 'startDateVisible',
        'lr_endDateVisible'           => 'endDateVisible',
        'lr_registrationEndDateVisible' => 'registrationEndDateVisible',

        // Additional event info
        'lr_eventUrl'            => 'eventUrl',
        'lr_imageUrl'            => 'imageUrl',           // doc: "imageUr"? ensure correct spelling
        'lr_imageThumbUrl'       => 'imageThumbnailUrl',  // doc: "ImageThumbnailUrl"
        'lr_pageHits'            => 'pageHits',
        'lr_ordersTotalSum'      => 'ordersTotalSum',
        'lr_registered'          => 'registeredParticipants',
        'lr_maxAllowed'          => 'maxAllowedRegistrations',
        'lr_availableRegistrations' => 'availableRegistrations',

        // Possibly store arrays as JSON
        'lr_bccEmails' => 'bccConfirmationEmailRecipients',

        // External flags
        'lr_externalPublish'           => 'externalPublish',
        'lr_externalHistoricalPublish' => 'externalHistoricalPublish',

        // Registration page sub-fields or store entire array
        'lr_registrationPage' => 'registrationPage',

        // Location object
        'lr_location_name'     => 'location.name',
        'lr_location_address1' => 'location.address1',
        'lr_location_address2' => 'location.address2',
        'lr_location_postCode' => 'location.postCode',
        'lr_location_city'     => 'location.city',
        'lr_location_county'   => 'location.county',
        'lr_location_long'     => 'location.longitude',
        'lr_location_lat'      => 'location.latitude',

        // Venue object
        'lr_venue_id'      => 'venue.id',
        'lr_venue_name'    => 'venue.name',
        'lr_venue_address1'=> 'venue.address1',
        'lr_venue_address2'=> 'venue.address2',
        'lr_venue_postCode'=> 'venue.postCode',
        'lr_venue_city'    => 'venue.city',

        // Contact person
        'lr_contact_name'   => 'contactPerson.name',
        'lr_contact_email'  => 'contactPerson.email',
        'lr_contact_phone'  => 'contactPerson.telephone',
        'lr_contact_mobile' => 'contactPerson.mobile',

        // Organizer
        'lr_organizer_id'        => 'organizer.id',
        'lr_organizer_affiliate' => 'organizer.affiliateId',
        'lr_organizer_name'      => 'organizer.name',

        // Arrays: coOrganizers, prices, categories, tags, etc. (store as JSON or parse further)
        'lr_coOrganizers'   => 'coOrganizers',
        'lr_prices'         => 'prices',
        'lr_categories'     => 'categories',
        'lr_tags'           => 'tags',
        'lr_municipalities' => 'municipalities',
        'lr_areas'          => 'areas',

        // Additional sub-objects
        'lr_course'         => 'course',
        'lr_riskAssessment' => 'riskAssessment',
        'lr_fields'         => 'fields',
        'lr_instructors'    => 'instructors',
    ),
),


 
 );
 