<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://betait.no/betaletsreg
 * @since      1.0.0
 *
 * @package    Betait_Letsreg
 * @subpackage Betait_Letsreg/admin/partials
 */

/**
 * Arrangementer Partial
 *
 * Denne filen viser en oversikt over arrangementer med paginering og handlinger.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

$is_read_only = get_option('betait_letsreg_read_only', 0);
$form_attributes = '';

if ($is_read_only) {
    $form_attributes = 'form-is-readonly=true';
}?>
<div class="wrap betait-letsreg-wrap">
    
    <header class="betait-letsreg-header">
        <h1><?php esc_html_e( 'Arrangementer Oversikt', 'betait-letsreg' ); ?></h1>
    </header>

    <main class="betait-letsreg-main">
    <h1><?php esc_html_e( 'Arrangementer Oversikt', 'betait-letsreg' ); ?></h1>

    <?php if ($is_read_only): ?>
            <div class="readonly-message">
                <?php esc_html_e('OBS! LetsReg er i Read-Only modus.', 'betait-letsreg'); ?>
            </div>
    <?php endif; ?>

    <div id="betait-letsreg-container">
    <input type="text" id="betait-letsreg-search" placeholder="<?php esc_html_e( 'Søk etter arrangement...', 'betait-letsreg' ); ?>">
     <!-- Toggles container -->
  <div class="betait-letsreg-toggles">
    <label class="toggle-label" for="betait_toggle_activeonly">
      <?php esc_html_e('Kun aktive', 'betait-letsreg'); ?>
    </label>
    <label class="toggle-switch">
      <input
        type="checkbox"
        id="betait_toggle_activeonly"
        name="activeonly"
      />
      <span class="slider round"></span>
    </label>

    <label class="toggle-label" for="betait_toggle_searchableonly">
      <?php esc_html_e('Offentlige s&oslash;kbare', 'betait-letsreg'); ?>
    </label>
    <label class="toggle-switch">
      <input
        type="checkbox"
        id="betait_toggle_searchableonly"
        name="searchableonly"
      />
      <span class="slider round"></span>
    </label>

    <label class="toggle-label" for="betait_toggle_future">
    <?php esc_html_e('Fremtidige', 'betait-letsreg'); ?>
  </label>
  <label class="toggle-switch">
    <input type="checkbox" id="betait_toggle_future" name="futureonly" />
    <span class="slider round"></span>
  </label>

  <label class="toggle-label" for="betait_toggle_published">
    <?php esc_html_e('Publiserte', 'betait-letsreg'); ?>
  </label>
  <label class="toggle-switch">
    <input type="checkbox" id="betait_toggle_published" name="publishedonly" checked/>
    <span class="slider round"></span>
  </label>

  <label class="toggle-label" for="betait_toggle_free">
    <?php esc_html_e('Gratis', 'betait-letsreg'); ?>
  </label>
  <label class="toggle-switch">
    <input type="checkbox" id="betait_toggle_free" name="freeonly" />
    <span class="slider round"></span>
  </label>
  </div>
</div>
    <table class="wp-list-table widefat fixed striped">
        <thead class="beta-letsreg-headtable">
            <tr>
                <th class="beta-letsreg-table-actions beta-letsreg-table-header"></th>
                <th class="sortable beta-letsreg-table-eventname beta-letsreg-table-header" data-sort="name">
                    <?php esc_html_e( 'Arrangementstittel', 'betait-letsreg' ); ?>
                    <span class="sort-arrows">
                        <span class="dashicons dashicons-arrow-up"></span>
                        <span class="dashicons dashicons-arrow-down"></span>
                    </span>
                </th>
                <th class="sortable beta-letsreg-table-venue beta-letsreg-table-header" data-sort="venue">
                    <?php esc_html_e( 'Stedsnavn', 'betait-letsreg' ); ?>
                    <span class="sort-arrows">
                        <span class="dashicons dashicons-arrow-up"></span>
                        <span class="dashicons dashicons-arrow-down"></span>
                    </span>
                </th>
                <th class="sortable beta-letsreg-table-registred beta-letsreg-table-header" data-sort="registeredParticipants">
                <span class="dashicons dashicons-admin-users" title="<?php esc_html_e( 'Antalle registrerte', 'betait-letsreg' ); ?>"></span>
                    <span class="sort-arrows">
                        <span class="dashicons dashicons-arrow-up"></span>
                        <span class="dashicons dashicons-arrow-down"></span>
                    </span>
                </th>
                <th class="sortable beta-letsreg-table-allowedregistred beta-letsreg-table-header" data-sort="maxAllowedRegistrations">
                <span class="dashicons dashicons-groups" title="<?php esc_html_e( 'Max antall', 'betait-letsreg' ); ?>"></span>
                    <span class="sort-arrows">
                        <span class="dashicons dashicons-arrow-up"></span>
                        <span class="dashicons dashicons-arrow-down"></span>
                    </span>
                </th>
                <th class="sortable beta-letsreg-table-waitinglist beta-letsreg-table-header" data-sort="hasWaitinglist">
                <span class="dashicons dashicons-bell" title="<?php esc_html_e( 'Venteliste', 'betait-letsreg' ); ?>"></span>
                    <span class="sort-arrows">
                        <span class="dashicons dashicons-arrow-up"></span>
                        <span class="dashicons dashicons-arrow-down"></span>
                    </span>
                </th>
                <th class="sortable beta-letsreg-table-starttime beta-letsreg-table-header" data-sort="startDate">
                <?php esc_html_e( 'Start', 'betait-letsreg' ); ?>
                    <span class="sort-arrows">
                        <span class="dashicons dashicons-arrow-up"></span>
                        <span class="dashicons dashicons-arrow-down"></span>
                    </span>
                </th>
                <th class="sortable beta-letsreg-table-endtime beta-letsreg-table-header" data-sort="endDate">
                    <?php esc_html_e( 'Slutt', 'betait-letsreg' ); ?>
                    <span class="sort-arrows">
                        <span class="dashicons dashicons-arrow-up"></span>
                        <span class="dashicons dashicons-arrow-down"></span>
                    </span>
                </th>
                <th class="sortable beta-letsreg-table-deadline beta-letsreg-table-header" data-sort="registrationStartDate">
                    <?php esc_html_e( 'Påmelding', 'betait-letsreg' ); ?>
                    <span class="sort-arrows">
                        <span class="dashicons dashicons-arrow-up"></span>
                        <span class="dashicons dashicons-arrow-down"></span>
                    </span>
                </th>
            </tr>
        </thead>
        <tbody id="betait-letsreg-events-table-body">
            <!-- Arrangementer blir lagt til her via AJAX -->
        </tbody>
    </table>

    <div class="pagination-controls">
        <button id="betait-letsreg-load-more" class="button button-primary"><?php esc_html_e( 'Last mer', 'betait-letsreg' ); ?></button>
        <span id="betait-letsreg-load-info" style="margin-left: 10px;"></span>
  
    </div>
</div>
</main>

    <footer class="betait-letsreg-footer">
    <hr />
    <p>
        <a href="https://betait.no" target="_blank">BeTA IT</a>
        <?php 
    
            echo sprintf(
                __(' LetsReg Integration Plugin – Alle rettigheter, 2024 – %s', 'betait-letsreg'), 
                date('Y') 
            );
        ?>
    </p>
</footer>
</div>
