(function($) {
  'use strict';

  /**
   * Document Ready for basic toggles and advanced settings panel
   */
  jQuery(document).ready(function($) {
    // Toggle "advanced" section
    $('.toggle-advanced').on('click', function() {
      let expanded = $(this).attr('aria-expanded') === 'true';
      $(this).attr('aria-expanded', !expanded);
      $('.betait-letsreg-advanced').toggle(!expanded);
      $(this).text(expanded ? 'Vis avanserte innstillinger' : 'Skjul avanserte innstillinger');
    });
  });

  /**
   * Once DOM is fully loaded
   */
  document.addEventListener('DOMContentLoaded', function() {

    // Disable form elements if form-is-readonly="true"
    const readOnlyForms = document.querySelectorAll('form[form-is-readonly="true"]');
    readOnlyForms.forEach(function(form) {
      const elements = form.querySelectorAll('input, select, textarea, button');
      elements.forEach(function(element) {
        element.disabled = true;
      });
    });

    // If you want the table to refresh automatically when toggles change,
    // you could add listeners here, e.g.:
    //
    // document.getElementById('betait_toggle_activeonly')
    //   ?.addEventListener('change', () => {
    //     currentPage = 1;
    //     totalLoadedSoFar = 0;
    //     $('#betait-letsreg-events-table-body').empty();
    //     loadEvents(currentPage);
    //   });
    //
    // document.getElementById('betait_toggle_searchableonly')
    //   ?.addEventListener('change', () => {
    //     currentPage = 1;
    //     totalLoadedSoFar = 0;
    //     $('#betait-letsreg-events-table-body').empty();
    //     loadEvents(currentPage);
    //   });

  });

  // Global pagination/sorting variables
  let currentPage = 1;
  const limit = 10; // Not strictly necessary if the server is limiting to 10 as well
  let totalPages = 1;

  // Running tally of how many events we've loaded so far
  // If your API provides an overall total, replace this approach with that total
  let totalLoadedSoFar = 0;

  // Track sorting state
  let currentSortColumn = null;
  let currentSortDirection = 'asc'; // 'asc' or 'desc'

  /**
   * Simple debug logger
   */
  function logDebug(message, data) {
    if (window.BetaitLetsReg?.debug && console && console.log) {
      console.log('[Betait_LetsReg DEBUG]: ' + message, data || '');
    }
  }

  /**
   * Main function to load events via AJAX
   */
  function loadEvents(page) {
    logDebug('Initiating AJAX request to fetch events for page ' + page);

    // Read the toggle states:
    const activeOnlyCheckbox = document.getElementById('betait_toggle_activeonly');
    const searchableOnlyCheckbox = document.getElementById('betait_toggle_searchableonly');

    // Convert them to booleans
    const activeOnly = !!activeOnlyCheckbox?.checked;
    const searchableOnly = !!searchableOnlyCheckbox?.checked;

    $.ajax({
      url: BetaitLetsReg.ajax_url,
      type: 'POST',
      data: {
        action: 'betait_letsreg_fetch_events',
        nonce: BetaitLetsReg.nonce,
        page: page,
        sort_field: currentSortColumn,
        sort_direction: currentSortDirection,
        // Toggles
        activeonly: activeOnly,
        searchableonly: searchableOnly,
      },
      beforeSend: function() {
        $('#betait-letsreg-load-more').text('Loading...');
        logDebug('AJAX request sent for page ' + page);
      },
      success: function(response) {
        logDebug('AJAX request successful.', response);
        if (response.success) {
          const events = response.data.events;
          const tbody = $('#betait-letsreg-events-table-body');

          // Log how many events were returned in this batch
          logDebug(`Received ${events.length} events in this batch.`);

          // If no events on first page, show a "no events" row
          if (events.length === 0 && page === 1) {
            tbody.append('<tr><td colspan="9">No events found.</td></tr>');
            $('#betait-letsreg-load-more').hide();
            logDebug('No events found for the first page. Stopping.');
            // Update status to show 0 loaded if you wish
            updateLoadStatus(0, 0, 0);
            return;
          }

          // Append each event to the table
          events.forEach(function(event) {
            const startTime = event.startDate ? new Date(event.startDate).toLocaleString() : 'N/A';
            const endTime = event.endDate ? new Date(event.endDate).toLocaleString() : 'N/A';
            const regDeadline = event.registrationStartDate
              ? new Date(event.registrationStartDate).toLocaleString()
              : 'N/A';

            const venue = event.location && event.location.name
              ? event.location.name
              : 'N/A';

              const row = `
							<tr>
								<td class="beta-letsreg-table-actions"
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
            logDebug('Appended event to table:', event);
          });

          // Increase the page count
          currentPage++;

          // If exactly limit were returned, we assume there's possibly more
          if (events.length === limit) {
            totalPages = currentPage + 1;
          } else {
            totalPages = currentPage;
          }
          logDebug('Updated currentPage to ' + currentPage + ' and totalPages to ' + totalPages);

          // Update how many we've loaded so far (local approach)
          totalLoadedSoFar += events.length;

          // For the new batch, figure out the displayed range
          // Because currentPage was just incremented:
          // previousPage = currentPage - 1
          // So the new events go from (previousPage - 1)*limit + 1  to  (previousPage - 1)*limit + events.length
          const prevPage = currentPage - 1;
          const startRange = (prevPage - 1) * limit + 1;
          const endRange = (prevPage - 1) * limit + events.length;
          updateLoadStatus(startRange, endRange, totalLoadedSoFar);

          // If fewer than limit, no more pages
          if (events.length < limit) {
            $('#betait-letsreg-load-more').hide();
            logDebug('No more pages to load; last batch was under limit.');
          } else {
            $('#betait-letsreg-load-more').text('Load More');
            logDebug('Load more button reset to "Load More".');
          }

          // If there's an existing sort, re-sort the table
          if (currentSortColumn) {
            sortTable(currentSortColumn, currentSortDirection);
          }

        } else {
          // success=false
          alert(response.data.message);
          logDebug('AJAX request returned error: ' + response.data.message);
          $('#betait-letsreg-load-more').text('Load More');
        }
      },
      error: function(xhr, status, error) {
        alert('An error occurred: ' + error);
        logDebug('AJAX request failed:', { xhr: xhr, status: status, error: error });
        $('#betait-letsreg-load-more').text('Load More');
      }
    });
  }

  /**
   * Updates the "Loaded X–Y of Z" text
   * If your API provides a total, replace totalSoFar with the official total
   */
  function updateLoadStatus(start, end, totalSoFar) {
    // If your API has a total count:  let totalCount = response.data.totalCount;
    // then do: const infoText = `Loaded ${start}-${end} of ${totalCount}`
    const infoText = `Loaded ${start}–${end} of ${totalSoFar}`;
    $('#betait-letsreg-load-info').text(infoText);
    logDebug(infoText);
  }

  /**
   * "Load More" button
   */
  $('#betait-letsreg-load-more').on('click', function() {
    loadEvents(currentPage);
  });

  /**
   * Handle "Add to WordPress" buttons
   */
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
        button.html('<span class="dashicons dashicons-plus"></span> Adding...');
        logDebug('AJAX request sent to add event ID: ' + eventId);
      },
      success: function(response) {
        logDebug('AJAX request to add event successful.', response);
        if (response.success) {
          alert(response.data.message);
          // Optionally show a check mark
          button.html('<span class="dashicons dashicons-yes"></span>');
        } else {
          alert('Error: ' + response.data.message);
          button.prop('disabled', false);
          button.html('<span class="dashicons dashicons-plus"></span>');
          logDebug('AJAX request to add event returned error: ' + response.data.message);
        }
      },
      error: function(xhr, status, error) {
        alert('An error occurred: ' + error);
        button.prop('disabled', false);
        button.html('<span class="dashicons dashicons-plus" title="Add to WP"></span>');
        logDebug('AJAX request to add event failed:', { xhr: xhr, status: status, error: error });
      }
    });
  });

  /**
   * Local text-based searching (client-side)
   */
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

  /**
   * Sorting logic when clicking on .sortable column headers
   */
  $('.sortable').on('click', function() {
    const sortField = $(this).data('sort');
    let sortDirection = 'asc';

    // Toggle direction if it's the same column
    if (currentSortColumn === sortField) {
      sortDirection = currentSortDirection === 'asc' ? 'desc' : 'asc';
    }

    currentSortColumn = sortField;
    currentSortDirection = sortDirection;

    // Update column classes
    $('.sortable').removeClass('asc desc');
    $(this).addClass(sortDirection);

    // Perform client-side sort
    sortTable(sortField, sortDirection);
  });

  /**
   * Sort the already-rendered table rows
   */
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

  /**
   * Retrieve a cell's value for sorting
   */
  function getFieldValue(row, sortField) {
    let index;
    switch (sortField) {
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
        index = 1; // Fallback to event name
    }

    const cellText = $(row).find('td').eq(index).text();

    // If it's a date field, parse to numeric timestamp
    if (['startDate','endDate','registrationStartDate'].includes(sortField)) {
      return new Date(cellText).getTime();
    }

    // If it's numeric fields
    if (['registeredParticipants','maxAllowedRegistrations'].includes(sortField)) {
      return parseInt(cellText, 10) || 0;
    }

    // For hasWaitinglist: 'Yes' -> 1, 'No' -> 0
    if (sortField === 'hasWaitinglist') {
      return cellText.toLowerCase() === 'yes' ? 1 : 0;
    }

    // For name, venue, etc.:
    return cellText.toLowerCase();
  }

  // Initially load page 1
  loadEvents(currentPage);

})(jQuery);
