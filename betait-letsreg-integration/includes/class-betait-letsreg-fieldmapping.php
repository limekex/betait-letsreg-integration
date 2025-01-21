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
 
     // The Events Calendar (post_type = tribe_events)
     'tribe_events' => array(
    // Basic WP fields
    'post_title'   => 'name',         // event.name => post_title
    'post_content' => 'description',  // event.description => post_content
    'post_status'  => 'publish',      // Could be dynamic based on event.active

    // The Events Calendar meta
    'meta' => array(
        /**
         * Common meta keys The Events Calendar uses:
         *  - _EventStartDate     => "2025-01-16 18:30:00"
         *  - _EventEndDate       => "2025-01-16 20:00:00"
         *  - _EventAllDay        => "1" or "0"
         *  - _EventShowMapLink   => "1" or "0"
         *  - _EventShowMap       => "1" or "0"
         *  - _EventVenueID       => references a tribe_venue post
         *  - _EventOrganizerID   => references a tribe_organizer post
         */
        '_EventStartDate' => 'startDate',  // event.startDate => _EventStartDate
        '_EventEndDate'   => 'endDate',    // event.endDate   => _EventEndDate

        /**
         * If you want to store the raw location address directly:
         */
        '_VenueAddress' => 'location.address1', 

        /**
         * If you want a real "Venue" in The Events Calendar's system,
         * you can create it programmatically, e.g.:
         *
         *   // Pseudocode in your "Add to WP" function:
         *
         *   if ( ! empty( $event_data['location'] ) ) {
         *       $venue_args = array(
         *          'Venue'   => $event_data['location']['name'] ?? '',
         *          'Address' => $event_data['location']['address1'] ?? '',
         *          'City'    => $event_data['location']['city'] ?? '',
         *          'Country' => 'Norway', // or fetch from event
         *       );
         *       // create an actual Venue using Tribe__Events__API
         *       $venue_id = Tribe__Events__API::createVenue( $venue_args );
         *
         *       // Then store that ID in _EventVenueID
         *       update_post_meta( $post_id, '_EventVenueID', $venue_id );
         *    }
         *
         * Similarly, for an Organizer:
         *
         *   $organizer_args = array(
         *       'Organizer'  => $event_data['contactPerson']['name'] ?? '',
         *       'Phone'      => $event_data['contactPerson']['mobile'] ?? '',
         *       'Email'      => $event_data['contactPerson']['email'] ?? '',
         *   );
         *   $org_id = Tribe__Events__API::createOrganizer( $organizer_args );
         *   update_post_meta( $post_id, '_EventOrganizerID', $org_id );
         *
         * If you do that, you might skip storing _VenueAddress directly, 
         * or store it only as a fallback.
         */
    ),
),

 
     // Your custom CPT (slug = "lr-arr")
'lr-arr' => array(
    'post_title'   => 'name',
    'post_content' => 'description',
    'post_status'  => 'publish', // you could set 'draft' if event.active == false, etc.

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
        'lr_startDate'        => 'startDate',
        'lr_endDate'          => 'endDate',
        'lr_registrationStartDate' => 'registrationStartDate',
        'lr_registrationEndDate'   => 'registrationEndDate',

        // Visibility toggles
        'lr_startDateVisible'         => 'startDateVisible',
        'lr_endDateVisible'           => 'endDateVisible',
        'lr_registrationEndDateVisible' => 'registrationEndDateVisible',

        // Additional event info
        'lr_eventUrl'         => 'eventUrl',
        'lr_imageUrl'         => 'imageUrl',
        'lr_imageThumbUrl'    => 'imageThumbnailUrl',
        'lr_pageHits'         => 'pageHits',
        'lr_ordersTotalSum'   => 'ordersTotalSum',
        'lr_registered'       => 'registeredParticipants',
        'lr_maxAllowed'       => 'maxAllowedRegistrations',
        'lr_availableRegistrations' => 'availableRegistrations',

        // Possibly store arrays as JSON
        // e.g. bccConfirmationEmailRecipients
        'lr_bccEmails' => 'bccConfirmationEmailRecipients',

        // External flags
        'lr_externalPublish'           => 'externalPublish',
        'lr_externalHistoricalPublish' => 'externalHistoricalPublish',

        // Registration page sub-field
        // e.g. registrationPage.showMaxAllowedRegistrations
        // We'll store that as 'lr_registrationPage' => 'registrationPage'
        // or parse further. For now, let's store the entire registrationPage as JSON:
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

        // The "venue" object if distinct from location, store it as you like
        'lr_venue_id'   => 'venue.id',
        'lr_venue_name' => 'venue.name',

        // Contact person
        'lr_contact_name'  => 'contactPerson.name',
        'lr_contact_email' => 'contactPerson.email',
        'lr_contact_phone' => 'contactPerson.telephone',
        'lr_contact_mobile'=> 'contactPerson.mobile',

        // Organizer
        'lr_organizer_id'   => 'organizer.id',
        'lr_organizer_name' => 'organizer.name',

        // Arrays: coOrganizers, prices, tags, municipalities, etc.
        // Store as JSON if you want to keep them. 
        // Or parse them further to create WP taxonomies or post relationships:
        'lr_coOrganizers'   => 'coOrganizers',
        'lr_prices'         => 'prices',
        'lr_categories'     => 'categories',
        'lr_tags'           => 'tags',
        'lr_municipalities' => 'municipalities',
        'lr_areas'          => 'areas',

        // Additional fields
        'lr_course'         => 'course',
        'lr_riskAssessment' => 'riskAssessment',
        'lr_fields'         => 'fields',
        'lr_instructors'    => 'instructors',
    ),
),

 
     // WordPress default post (slug = "post")
'post' => array(
    'post_title'   => 'name',         // event.name => post_title
    'post_content' => 'description',  // event.description => post_content
    'post_status'  => 'publish',      // or dynamic if event is active/inactive

    'meta' => array(
        'lr_id'                  => 'id',
        'lr_active'             => 'active',
        'lr_published'          => 'published',
        'lr_searchable'         => 'searchable',
        'lr_isPaidEvent'        => 'isPaidEvent',
        'lr_isArchived'         => 'isArchived',
        'lr_hasWaitinglist'     => 'hasWaitinglist',

        // Times/dates
        'lr_startDate'          => 'startDate',
        'lr_endDate'            => 'endDate',
        'lr_registrationStartDate' => 'registrationStartDate',
        'lr_registrationEndDate'   => 'registrationEndDate',

        // Visibility toggles
        'lr_startDateVisible'          => 'startDateVisible',
        'lr_endDateVisible'            => 'endDateVisible',
        'lr_registrationEndDateVisible'=> 'registrationEndDateVisible',

        // More event info
        'lr_eventUrl'          => 'eventUrl',
        'lr_imageUrl'          => 'imageUrl',
        'lr_imageThumbUrl'     => 'imageThumbnailUrl',
        'lr_pageHits'          => 'pageHits',
        'lr_ordersTotalSum'    => 'ordersTotalSum',
        'lr_registered'        => 'registeredParticipants',
        'lr_maxAllowed'        => 'maxAllowedRegistrations',
        'lr_availableRegistrations' => 'availableRegistrations',

        // Possibly store arrays as JSON:
        'lr_bccEmails' => 'bccConfirmationEmailRecipients',

        // External booleans
        'lr_externalPublish'           => 'externalPublish',
        'lr_externalHistoricalPublish' => 'externalHistoricalPublish',

        // Registration page subfields (stored as JSON or parse further):
        'lr_registrationPage' => 'registrationPage',

        // location object
        'lr_location_name'     => 'location.name',
        'lr_location_address1' => 'location.address1',
        'lr_location_address2' => 'location.address2',
        'lr_location_postCode' => 'location.postCode',
        'lr_location_city'     => 'location.city',
        'lr_location_county'   => 'location.county',
        'lr_location_long'     => 'location.longitude',
        'lr_location_lat'      => 'location.latitude',

        // Venue object
        'lr_venue_id'   => 'venue.id',
        'lr_venue_name' => 'venue.name',

        // Contact person
        'lr_contact_name'  => 'contactPerson.name',
        'lr_contact_email' => 'contactPerson.email',
        'lr_contact_phone' => 'contactPerson.telephone',
        'lr_contact_mobile'=> 'contactPerson.mobile',

        // Organizer
        'lr_organizer_id'   => 'organizer.id',
        'lr_organizer_name' => 'organizer.name',

        // Arrays: coOrganizers, prices, tags, etc.
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
 