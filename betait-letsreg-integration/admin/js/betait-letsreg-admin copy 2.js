(function( $ ) {
    'use strict';

    jQuery(document).ready(function($){
        $('.toggle-advanced').on('click', function(){
            let expanded = $(this).attr('aria-expanded') === 'true';
            $(this).attr('aria-expanded', !expanded);
            $('.betait-letsreg-advanced').toggle(!expanded);
            $(this).text( expanded ? 'Vis avanserte innstillinger' : 'Skjul avanserte innstillinger' );
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        // Finn alle form-elementer med 'form-is-readonly="true"'
        const readOnlyForms = document.querySelectorAll('form[form-is-readonly="true"]');

        readOnlyForms.forEach(function (form) {
            // Deaktiver alle input, select, textarea og button elementer
            const elements = form.querySelectorAll('input, select, textarea, button');

            elements.forEach(function (element) {
                element.disabled = true;
            });
        });
    });



    // GET EVENTS AND ADD THEM TO THE TABLE

    let currentPage = 1;
    const limit = 10;
    let totalPages = 1;

    // Sort state
    let currentSortColumn = null;
    let currentSortDirection = 'asc'; // 'asc' or 'desc'

    function logDebug(message, data) {
        if (BetaitLetsReg.debug && console && console.log) {
            console.log('[Betait_LetsReg DEBUG]: ' + message, data || '');
        }
    }

    function loadEvents(page) {
        logDebug('Initiating AJAX request to fetch events for page ' + page);

        // Beregn offset basert på page og limit
        const offset = (page - 1) * limit;

        $.ajax({
            url: BetaitLetsReg.ajax_url,
            type: 'POST',
            data: {
                action: 'betait_letsreg_fetch_events',
                nonce: BetaitLetsReg.nonce,
                page: page, // Behold page for PHP beregning av offset
                sort_field: currentSortColumn,
                sort_direction: currentSortDirection,
            },
            beforeSend: function() {
                $('#betait-letsreg-load-more').text('Laster...');
                logDebug('AJAX request sent for page ' + page);
            },
            success: function(response) {
                logDebug('AJAX request successful.', response);
                if (response.success) {
                    const events = response.data.events;
                    const tbody = $('#betait-letsreg-events-table-body');

                    if (events.length === 0 && page === 1) {
                        tbody.append('<tr><td colspan="9">Ingen aktive arrangementer funnet.</td></tr>');
                        $('#betait-letsreg-load-more').hide();
                        logDebug('No active events found.');
                        return;
                    }

                    events.forEach(function(event) {
                        const startTime = event.startDate ? new Date(event.startDate).toLocaleString() : 'Ingen';
                        const endTime = event.endDate ? new Date(event.endDate).toLocaleString() : 'Ingen';
                        const regDeadline = event.registrationStartDate ? new Date(event.registrationStartDate).toLocaleString() : 'Ingen';

                        const venue = event.location && event.location.name ? event.location.name : 'Ingen';

                        const row = `
							<tr>
								<td>
									<a href="${event.eventUrl}" target="_blank" title="Offentlig URL">
										<span class="dashicons dashicons-external"></span>
									</a>
									<button class="button button-secondary add-to-wp" data-event-id="${event.id}" title="${BetaitLetsReg.add_wp_label}">
										<span class="dashicons dashicons-plus"></span>
									</button>
								</td>
								<td colspan="2" class="beta-letsreg-table-eventname">${event.name}</td>
								<td class="beta-letsreg-table-venue">${venue}</td>
								<td class="beta-letsreg-table-registred">${event.registeredParticipants}</td>
								<td class="beta-letsreg-table-allowedregistred">${event.maxAllowedRegistrations}</td>
								<td class="beta-letsreg-table-waitinglist">${event.hasWaitinglist ? 'Ja' : 'Nei'}</td>
								<td class="beta-letsreg-table-starttime">${startTime}</td>
								<td class="beta-letsreg-table-endtime">${endTime}</td>
								<td class="beta-letsreg-table-deadline">${regDeadline}</td>
							</tr>
						`;
                        tbody.append(row);
                        logDebug('Appended event to table: ', event);
                    });

                    // Oppdater side-nummer og total sider
                    currentPage++;
                    // Hvis API-et returnerer total antall arrangementer, kan vi beregne totalPages
                    // Hvis ikke, kan vi anta at hvis antall hentede arrangementer er lik limit, så er det muligens flere
                    if (events.length === limit) {
                        totalPages = currentPage + 1; // Dette er en enkel tilnærming
                    } else {
                        totalPages = currentPage;
                    }
                    logDebug('Updated currentPage to ' + currentPage + ' and totalPages to ' + totalPages);

                    // Sjekk om det er flere sider
                    if (events.length < limit) {
                        $('#betait-letsreg-load-more').hide();
                        logDebug('No more pages to load. Hiding load more button.');
                    } else {
                        $('#betait-letsreg-load-more').text('Last mer');
                        logDebug('Load more button updated to "Last mer".');
                    }

                    // Apply current sort if any
                    if (currentSortColumn) {
                        sortTable(currentSortColumn, currentSortDirection);
                    }
                } else {
                    alert(response.data.message);
                    logDebug('AJAX request returned error: ' + response.data.message);
                    $('#betait-letsreg-load-more').text('Last mer');
                }
            },
            error: function(xhr, status, error) {
                alert('En feil oppstod: ' + error);
                logDebug('AJAX request failed:', { xhr: xhr, status: status, error: error });
                $('#betait-letsreg-load-more').text('Last mer');
            }
        });
    }

    // Last inn første side med arrangementer
    loadEvents(currentPage);

    // Håndter klikk på "Last mer" knappen
    $('#betait-letsreg-load-more').on('click', function() {
        loadEvents(currentPage);
    });

    // Håndtere "Legg til i WordPress" knapper
    $(document).on('click', '.add-to-wp', function() {
        const eventId = $(this).data('event-id');
        const button = $(this);

        logDebug('Initiating AJAX request to add event ID: ' + eventId);

        $.ajax({
            url: BetaitLetsReg.ajax_url,
            type: 'POST',
            data: {
                action: 'betait_letsreg_add_event',
                nonce: BetaitLetsReg.nonce,
                event_id: eventId,
            },
            beforeSend: function() {
                button.prop('disabled', true);
                button.html('<span class="dashicons dashicons-plus"></span> Legger til...');
                logDebug('AJAX request sent to add event ID: ' + eventId);
            },
            success: function(response) {
                logDebug('AJAX request to add event successful.', response);
                if (response.success) {
                    alert(response.data.message);
                    button.html('<span class="dashicons dashicons-yes"></span>');
                } else {
                    alert('Feil: ' + response.data.message);
                    button.prop('disabled', false);
                    button.html('<span class="dashicons dashicons-plus"></span>');
                    logDebug('AJAX request to add event returned error: ' + response.data.message);
                }
            },
            error: function(xhr, status, error) {
                alert('En feil oppstod: ' + error);
                button.prop('disabled', false);
                button.html('<span class="dashicons dashicons-plus" title="Legg til i WP"></span>');
                logDebug('AJAX request to add event failed:', { xhr: xhr, status: status, error: error });
            }
        });
    });

    // Lokal filtrering basert på søkefelt
    $('#betait-letsreg-search').on('input', function() {
        const query = $(this).val().toLowerCase();
        $('#betait-letsreg-events-table-body tr').each(function() {
            const eventName = $(this).find('td:nth-child(2)').text().toLowerCase();
            if (eventName.includes(query)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Sorting logic
    $('.sortable').on('click', function() {
        const sortField = $(this).data('sort');
        let sortDirection = 'asc';

        if (currentSortColumn === sortField) {
            // Toggle sort direction
            sortDirection = currentSortDirection === 'asc' ? 'desc' : 'asc';
        }

        currentSortColumn = sortField;
        currentSortDirection = sortDirection;

        // Fjern sort-klassene fra alle headers
        $('.sortable').removeClass('asc desc');

        // Legg til sort-klasse til den klikkede headeren
        $(this).addClass(sortDirection);

        // Utfør sortering
        sortTable(sortField, sortDirection);
    });

    function sortTable(sortField, sortDirection) {
        const tbody = $('#betait-letsreg-events-table-body');
        const rows = tbody.find('tr').get();

        rows.sort(function(a, b) {
            const aValue = getFieldValue(a, sortField);
            const bValue = getFieldValue(b, sortField);

            if ($.isNumeric(aValue) && $.isNumeric(bValue)) {
                return sortDirection === 'asc' ? aValue - bValue : bValue - aValue;
            } else {
                if (aValue < bValue) {
                    return sortDirection === 'asc' ? -1 : 1;
                }
                if (aValue > bValue) {
                    return sortDirection === 'asc' ? 1 : -1;
                }
                return 0;
            }
        });

        $.each(rows, function(index, row) {
            tbody.append(row);
        });
    }

    function getFieldValue(row, sortField) {
        // Map sortField til tabellkolonneindeks
        let index;
        switch(sortField) {
            case 'name':
                index = 1;
                break;
            case 'venue':
                index = 2;
                break;
            case 'registeredParticipants':
                index = 3;
                break;
            case 'maxAllowedRegistrations':
                index = 4;
                break;
            case 'hasWaitinglist':
                index = 5;
                break;
            case 'startDate':
                index = 6;
                break;
            case 'endDate':
                index = 7;
                break;
            case 'registrationStartDate':
                index = 8;
                break;
            default:
                index = 1;
        }

        let cell = $(row).find('td').eq(index).text();

        // For datoer, konverter til timestamp
        if (sortField === 'startDate' || sortField === 'endDate' || sortField === 'registrationStartDate') {
            return new Date(cell).getTime();
        }

        // For numeriske felt
        if (sortField === 'registeredParticipants' || sortField === 'maxAllowedRegistrations') {
            return parseInt(cell, 10) || 0;
        }

        // For hasWaitinglist
        if (sortField === 'hasWaitinglist') {
            return cell.toLowerCase() === 'ja' ? 1 : 0;
        }

        // For name og venue, returner lowercase string for case-insensitive sortering
        return cell.toLowerCase();
    }

})( jQuery );
