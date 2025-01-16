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
 * Dette er et standard-liste over endepunkter,
 * med full URL for hvert (uten at du må bygge base + path).
 */

$default_endpoints = array(

    array(
        'slug'   => 'token',
        'method' => 'POST',
        'url'    => 'https://legacyapi.deltager.no/token',
        'desc'   => 'Henter Access Token (legacy-endepunkt)'
    ),

    // ORGANIZERS
    array(
        'slug'   => 'organizers_list',
        'method' => 'GET',
        'url'    => 'https://integrate.deltager.no/organizers',
        'desc'   => 'Henter liste over arrangører'
    ),
    array(
        'slug'   => 'organizer_detail',
        'method' => 'GET',
        'url'    => 'https://integrate.deltager.no/organizers/{organizerId}',
        'desc'   => 'Henter detaljer for en spesifikk arrangør'
    ),
    array(
        'slug'   => 'create_organizer',
        'method' => 'POST',
        'url'    => 'https://integrate.deltager.no/organizers',
        'desc'   => 'Oppretter en ny arrangør'
    ),
    array(
        'slug'   => 'update_organizer',
        'method' => 'PUT',
        'url'    => 'https://integrate.deltager.no/organizers',
        'desc'   => 'Oppdaterer en eksisterende arrangør'
    ),
    array(
        'slug'   => 'organizer_accounts',
        'method' => 'GET',
        'url'    => 'https://integrate.deltager.no/organizers/{organizerId}/accounts',
        'desc'   => 'Henter kontoer knyttet til en arrangør'
    ),

    // EVENTS
    array(
        'slug'   => 'organizer_events',
        'method' => 'GET',
        'url'    => 'https://integrate.deltager.no/organizers/{organizerId}/events',
        'desc'   => 'Henter events fra en arrangør'
    ),
    array(
        'slug'   => 'create_event',
        'method' => 'POST',
        'url'    => 'https://integrate.deltager.no/organizers/{organizerId}/events',
        'desc'   => 'Oppretter en ny event for en arrangør'
    ),
    array(
        'slug'   => 'affiliate_events',
        'method' => 'GET',
        'url'    => 'https://integrate.deltager.no/affiliates/events',
        'desc'   => 'Henter events fra en affiliate'
    ),
    array(
        'slug'   => 'get_event',
        'method' => 'GET',
        'url'    => 'https://integrate.deltager.no/events/{eventId}',
        'desc'   => 'Henter detaljinfo om en spesifikk event'
    ),
    array(
        'slug'   => 'update_event',
        'method' => 'PUT',
        'url'    => 'https://integrate.deltager.no/events/{eventId}',
        'desc'   => 'Oppdaterer en eksisterende event'
    ),
    array(
        'slug'   => 'event_waitinglist',
        'method' => 'GET',
        'url'    => 'https://integrate.deltager.no/events/{eventId}/waitinglist',
        'desc'   => 'Henter venteliste for en spesifikk event'
    ),
    array(
        'slug'   => 'event_prices',
        'method' => 'GET',
        'url'    => 'https://integrate.deltager.no/events/{eventId}/prices',
        'desc'   => 'Henter eventpriser for en spesifikk event'
    ),
    array(
        'slug'   => 'add_event_price',
        'method' => 'POST',
        'url'    => 'https://integrate.deltager.no/events/{eventId}/prices',
        'desc'   => 'Legger til en pris for en event'
    ),
    array(
        'slug'   => 'update_event_price',
        'method' => 'PUT',
        'url'    => 'https://integrate.deltager.no/events/{eventId}/prices/{priceId}',
        'desc'   => 'Oppdaterer en spesifikk pris i en event'
    ),


    // ORDERS
    array(
        'slug'   => 'event_orders',
        'method' => 'GET',
        'url'    => 'https://integrate.deltager.no/events/{eventId}/orders/orderdetails',
        'desc'   => 'Henter påmeldingsordre (ordre/ordredetaljer) for en event'
    ),
    array(
        'slug'   => 'organizer_orders',
        'method' => 'GET',
        'url'    => 'https://integrate.deltager.no/organizers/{organizerId}/orders/orderdetails',
        'desc'   => 'Henter alle ordre for en arrangør'
    ),
    array(
        'slug'   => 'affiliate_orders',
        'method' => 'GET',
        'url'    => 'https://integrate.deltager.no/affiliates/{affId}/orders/orderdetails',
        'desc'   => 'Henter alle ordre for en affiliate'
    ),
    array(
        'slug'   => 'get_order',
        'method' => 'GET',
        'url'    => 'https://integrate.deltager.no/orders/{orderId}',
        'desc'   => 'Henter en spesifikk ordre'
    ),
    array(
        'slug'   => 'get_orderdetail',
        'method' => 'GET',
        'url'    => 'https://integrate.deltager.no/orders/{orderId}/orderdetails/{orderDetailId}',
        'desc'   => 'Henter et spesifikt orderdetail-objekt'
    ),
    array(
        'slug'   => 'event_orders_ids',
        'method' => 'GET',
        'url'    => 'https://integrate.deltager.no/events/{eventId}/orders/ids',
        'desc'   => 'Henter liste over ordre-ID-er for en event'
    ),


    // SETTLEMENTS
array(
    'slug'   => 'organizer_settlements',
    'method' => 'GET',
    'url'    => 'https://integrate.deltager.no/organizers/{organizerId}/settlements',
    'desc'   => 'Henter siste 20 oppgjør for en arrangør'
),
array(
    'slug'   => 'organizer_settlement_detail',
    'method' => 'GET',
    'url'    => 'https://integrate.deltager.no/organizers/{organizerId}/settlements/{settlementId}',
    'desc'   => 'Henter alle transaksjoner for et gitt oppgjør'
),
array(
    'slug'   => 'organizer_settlement_pdf',
    'method' => 'GET',
    'url'    => 'https://integrate.deltager.no/organizers/{organizerId}/settlements/{settlementId}/pdf',
    'desc'   => 'Henter transaksjoner for et gitt oppgjør i PDF-format'
),

// WEBHOOKS
array(
    'slug'   => 'webhooks_my',
    'method' => 'GET',
    'url'    => 'https://integrate.deltager.no/webhooks/my',
    'desc'   => 'Henter alle webhook-abonnement registrert på din konto'
),
array(
    'slug'   => 'webhooks_list',
    'method' => 'GET',
    'url'    => 'https://integrate.deltager.no/webhooks',
    'desc'   => 'Henter alle mulige hooks du kan abonnere på'
),
array(
    'slug'   => 'webhooks_subscribe',
    'method' => 'POST',
    'url'    => 'https://integrate.deltager.no/webhooks',
    'desc'   => 'Abonnerer på en bestemt webhook'
),
array(
    'slug'   => 'webhooks_delete',
    'method' => 'DELETE',
    'url'    => 'https://integrate.deltager.no/webhooks/{id}',
    'desc'   => 'Fjerner et webhook-abonnement'
),
array(
    'slug'   => 'webhooks_get_subscription',
    'method' => 'GET',
    'url'    => 'https://integrate.deltager.no/webhooks/{id}',
    'desc'   => 'Henter et spesifikt subscription-objekt'
),

// TAGS
array(
    'slug'   => 'tags_organizer',
    'method' => 'GET',
    'url'    => 'https://integrate.deltager.no/tags/organizer/{organizerId}',
    'desc'   => 'Henter alle tags definert for en spesifikk arrangør'
),
array(
    'slug'   => 'tags_affiliate',
    'method' => 'GET',
    'url'    => 'https://integrate.deltager.no/tags/affiliate/{affId}',
    'desc'   => 'Henter globale tags for en spesifikk affiliate'
),
array(
    'slug'   => 'tag_detail',
    'method' => 'GET',
    'url'    => 'https://integrate.deltager.no/tags/{tagId}',
    'desc'   => 'Henter detaljer om en spesifikk tag'
),

// USERS
array(
    'slug'   => 'create_user_account',
    'method' => 'POST',
    'url'    => 'https://integrate.deltager.no/useraccounts',
    'desc'   => 'Oppretter en brukerkonto med standardprofil'
),
array(
    'slug'   => 'update_user_account',
    'method' => 'PUT',
    'url'    => 'https://integrate.deltager.no/useraccounts/{accountId}',
    'desc'   => 'Oppdaterer e-post eller passord for en brukerkonto'
),
array(
    'slug'   => 'user_account_detail',
    'method' => 'GET',
    'url'    => 'https://integrate.deltager.no/useraccounts/{accountId}',
    'desc'   => 'Henter kontoinformasjon for en bruker basert på ID'
),
array(
    'slug'   => 'user_account_add_role',
    'method' => 'POST',
    'url'    => 'https://integrate.deltager.no/useraccounts/{accountId}/profiles/{profileId}/roles',
    'desc'   => 'Legger til en organisatortilknytning (rolle) i en brukers profil'
),
array(
    'slug'   => 'user_account_remove_role',
    'method' => 'DELETE',
    'url'    => 'https://integrate.deltager.no/useraccounts/{accountId}/profiles/{profileId}/roles/{organizerId}',
    'desc'   => 'Fjerner en organisatortilknytning fra en brukers profil'
),
array(
    'slug'   => 'user_account_by_email',
    'method' => 'GET',
    'url'    => 'https://integrate.deltager.no/useraccounts/{email}',
    'desc'   => 'Henter en brukerkonto basert på e-postadresse'
),
array(
    'slug'   => 'user_account_add_profile',
    'method' => 'POST',
    'url'    => 'https://integrate.deltager.no/useraccounts/{accountId}/profiles',
    'desc'   => 'Legger til en profil på en brukerkonto'
),

);

