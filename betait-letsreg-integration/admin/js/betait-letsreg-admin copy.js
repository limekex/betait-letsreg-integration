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
    const perPage = 10;
    let totalPages = 1;

    function logDebug(message, data) {
        if (BetaitLetsReg.debug && console && console.log) {
            console.log('[Betait_LetsReg DEBUG]: ' + message, data || '');
        }
    }

    function loadEvents(page) {
        logDebug('Initiating AJAX request to fetch events for page ' + page);

        $.ajax({
            url: BetaitLetsReg.ajax_url,
            type: 'POST',
            data: {
                action: 'betait_letsreg_fetch_events',
                nonce: BetaitLetsReg.nonce,
                page: page,
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
						const startTime = new Date(event.startDate).toLocaleString();
						const endTime = new Date(event.endDate).toLocaleString();
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
								<td class="beta-letsreg-table-eventname">${event.name}</td>
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
                    totalPages = response.data.pagination.total_pages || 1;
                    logDebug('Updated currentPage to ' + currentPage + ' and totalPages to ' + totalPages);

                    // Sjekk om det er flere sider
                    if (currentPage > totalPages) {
                        $('#betait-letsreg-load-more').hide();
                        logDebug('No more pages to load. Hiding load more button.');
                    } else {
                        $('#betait-letsreg-load-more').text('Last mer');
                        logDebug('Load more button updated to "Last mer".');
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
                    button.html('<span class="dashicons dashicons-yes"></span> Lagret');
                } else {
                    alert('Feil: ' + response.data.message);
                    button.prop('disabled', false);
                    button.html('<span class="dashicons dashicons-plus"></span> Legg til i WP');
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
	
})( jQuery );
