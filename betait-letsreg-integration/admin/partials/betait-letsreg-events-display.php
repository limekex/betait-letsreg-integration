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
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Aksjoner', 'betait-letsreg' ); ?></th>
                <th colspan="2" class="sortable" data-sort="name">
                    <?php esc_html_e( 'Arrangementstittel', 'betait-letsreg' ); ?>
                    <span class="sort-arrows">
                        <span class="dashicons dashicons-arrow-up"></span>
                        <span class="dashicons dashicons-arrow-down"></span>
                    </span>
                </th>
                <th class="sortable" data-sort="venue">
                    <?php esc_html_e( 'Stedsnavn', 'betait-letsreg' ); ?>
                    <span class="sort-arrows">
                        <span class="dashicons dashicons-arrow-up"></span>
                        <span class="dashicons dashicons-arrow-down"></span>
                    </span>
                </th>
                <th class="sortable" data-sort="registeredParticipants">
                    <?php esc_html_e( 'Registrerte', 'betait-letsreg' ); ?>
                    <span class="sort-arrows">
                        <span class="dashicons dashicons-arrow-up"></span>
                        <span class="dashicons dashicons-arrow-down"></span>
                    </span>
                </th>
                <th class="sortable" data-sort="maxAllowedRegistrations">
                    <?php esc_html_e( 'Max påmeldte', 'betait-letsreg' ); ?>
                    <span class="sort-arrows">
                        <span class="dashicons dashicons-arrow-up"></span>
                        <span class="dashicons dashicons-arrow-down"></span>
                    </span>
                </th>
                <th class="sortable" data-sort="hasWaitinglist">
                    <?php esc_html_e( 'Venteliste', 'betait-letsreg' ); ?>
                    <span class="sort-arrows">
                        <span class="dashicons dashicons-arrow-up"></span>
                        <span class="dashicons dashicons-arrow-down"></span>
                    </span>
                </th>
                <th class="sortable" data-sort="startDate">
                    <?php esc_html_e( 'Tidspunkt', 'betait-letsreg' ); ?>
                    <span class="sort-arrows">
                        <span class="dashicons dashicons-arrow-up"></span>
                        <span class="dashicons dashicons-arrow-down"></span>
                    </span>
                </th>
                <th class="sortable" data-sort="endDate">
                    <?php esc_html_e( 'Slutt Tidspunkt', 'betait-letsreg' ); ?>
                    <span class="sort-arrows">
                        <span class="dashicons dashicons-arrow-up"></span>
                        <span class="dashicons dashicons-arrow-down"></span>
                    </span>
                </th>
                <th class="sortable" data-sort="registrationStartDate">
                    <?php esc_html_e( 'Påmeldingsfrist', 'betait-letsreg' ); ?>
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
