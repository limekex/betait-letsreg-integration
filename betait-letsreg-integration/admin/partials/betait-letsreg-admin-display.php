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
 * Partial-filen for “Innstillinger” i Betait LetsReg-pluginet.
 * Viser en header, main, og footer for et rent oppsett.
 *
 * Du kan selv velge om du vil ha logikk for lagring av data her eller i admin-klassen.
 */

// 1) Hent eksisterende verdier fra WP options (plain text, ingen kryptering)
$username         = get_option('betait_letsreg_username', '');
$password         = get_option('betait_letsreg_password', '');
$affid            = get_option('betait_letsreg_affid', '1');
$access_token     = get_option('betait_letsreg_access_token', '');
$primary_org      = get_option('betait_letsreg_primary_org', '');
$debug_enabled    = get_option('betait_letsreg_debug', false);
$read_only_enabled = get_option('betait_letsreg_read_only', false);

// Avanserte endepunkter
$token_url         = get_option('betait_letsreg_token_url', 'https://integrate.deltager.no/token');
$arrangementer_url = get_option('betait_letsreg_events_url', 'https://integrate.deltager.no/organizers');

// Liste over arrangører (hentes og lagres av admin-kode)
$organizers_list   = get_option('betait_letsreg_organizers_list', []);


?>

<div class="wrap betait-letsreg-wrap">
    
    <header class="betait-letsreg-header">
        <h1><?php esc_html_e('LetsReg – Innstillinger', 'betait-letsreg'); ?></h1>
        <p class="description"><?php esc_html_e('Her kan du konfigurere tilkoblingen til Deltager / LetsReg.', 'betait-letsreg'); ?></p>
    </header>

    <main class="betait-letsreg-main">

        <!-- 
            STORT SKJEMA for å: 
            - Lagrer brukernavn/passord/affid
            - Access Token
            - Primær arrangør
            - Debug
            - Avanserte endepunkter
        -->
        <form method="post" action="">
            
            <?php 
            // Nonce for sikkerhet (anbefalt):
            wp_nonce_field( 'betait_letsreg_settings_save', 'betait_letsreg_nonce' );
            ?>

            <table class="form-table betait-letsreg-table">

                <!-- Brukernavn -->
                <tr>
                    <th><label for="betait_letsreg_username"><?php esc_html_e('Brukernavn', 'betait-letsreg'); ?></label></th>
                    <td>
                        <input type="text"
                               name="betait_letsreg_username"
                               id="betait_letsreg_username"
                               value="<?php echo esc_attr($username); ?>"
                               class="regular-text" />
                    </td>
                </tr>

                <!-- Passord -->
                <tr>
                    <th><label for="betait_letsreg_password"><?php esc_html_e('Passord', 'betait-letsreg'); ?></label></th>
                    <td>
                        <input type="password"
                               name="betait_letsreg_password"
                               id="betait_letsreg_password"
                               value="<?php echo esc_attr($password); ?>"
                               autocomplete="off"
                               class="regular-text" />
                        <p class="description">
                            <?php esc_html_e('Lagres i klartekst (for nå). Skriv på nytt hvis du vil endre.', 'betait-letsreg'); ?>
                        </p>
                    </td>
                </tr>

                <!-- AffID -->
                <tr>
                    <th><label for="betait_letsreg_affid"><?php esc_html_e('Affiliate ID (affid)', 'betait-letsreg'); ?></label></th>
                    <td>
                        <input type="text"
                               name="betait_letsreg_affid"
                               id="betait_letsreg_affid"
                               value="<?php echo esc_attr($affid); ?>"
                               class="small-text" />
                        <p class="description">
                            <?php esc_html_e('Hvilken affiliate du bruker. Vanligvis “1” for standard.', 'betait-letsreg'); ?>
                        </p>
                    </td>
                </tr>

                <!-- Access Token (les/skrivbart) -->
                <tr>
                    <th><label for="betait_letsreg_access_token"><?php esc_html_e('Access Token', 'betait-letsreg'); ?></label></th>
                    <td>
                        <textarea name="betait_letsreg_access_token"
                                  id="betait_letsreg_access_token"
                                  rows="3"
                                  cols="50" readonly><?php echo esc_textarea($access_token); ?></textarea>
                        <p class="description">
                            <?php esc_html_e('Access token genereres ved første gangs innlogging.', 'betait-letsreg'); ?>
                        </p>
                    </td>
                    <td>                <button type="submit" name="betait_letsreg_fetch_token" class="button button-secondary">
                    <?php esc_html_e('Hent Access Token', 'betait-letsreg'); ?>
                </button></td>
                </tr>

                <!-- Hent arrangører + velg primær -->
                <tr>
                    <th><label for="betait_letsreg_primary_org"><?php esc_html_e('Primær arrangør', 'betait-letsreg'); ?></label></th>
                    <td>
                        <?php if (! empty($organizers_list) && is_array($organizers_list)) : ?>
                            <select name="betait_letsreg_primary_org" id="betait_letsreg_primary_org">
                                <option value=""><?php esc_html_e('Velg arrangør', 'betait-letsreg'); ?></option>
                                <?php foreach ($organizers_list as $org) :
                                    // Anta $org har ->id og ->name  
                                    $selected = ($primary_org == $org->id) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo esc_attr($org->id); ?>" <?php echo $selected; ?>>
                                        <?php echo esc_html($org->id . ' – ' . $org->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description">
                                <?php esc_html_e('Arrangøren som brukes som “primær” i plugin-oppsett.', 'betait-letsreg'); ?>
                            </p>
                        <?php else : ?>
                            <p><?php esc_html_e('Ingen arrangør-liste. Klikk “Hent arrangører” etter at du har satt inn brukernavn/passord.', 'betait-letsreg'); ?></p>
                        <?php endif; ?>
                    </td>
                    <td>   <button type="submit" name="betait_letsreg_fetch_organizers" class="button button-secondary">
                    <?php esc_html_e('Hent arrangører', 'betait-letsreg'); ?>
                </button></td>
                </tr>

                <!-- Debug-modus -->
                <tr>
                    <th><?php esc_html_e('Debug-modus', 'betait-letsreg'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="betait_letsreg_debug" value="1"
                                <?php checked($debug_enabled, true); ?> />
                            <?php esc_html_e('Aktiver debug-logging i WP debug.log', 'betait-letsreg'); ?>
                        </label>
                    </td>
                </tr>
                 <!-- Read Only-modus -->
                 <tr>
                    <th><?php esc_html_e('Read Only-modus', 'betait-letsreg'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="betait_letsreg_read_only" value="1" <?php checked($read_only_enabled, 1); ?> />
                            <?php esc_html_e('Aktiver Read Only-modus for alle former', 'betait-letsreg'); ?>
                        </label>
                    </td>
                </tr>
            </table>


            <h2 class="title">
    <button type="button" class="toggle-advanced button-secondary" aria-expanded="false">
        <?php esc_html_e('Vis avanserte innstillinger', 'betait-letsreg'); ?>
    </button>
</h2>

<div class="betait-letsreg-advanced" style="display: none;">
    <div class="advanced-warning">
        <p><?php esc_html_e('OBS: Dette er avanserte innstillinger. Ikke endre med mindre du vet hva du gjør.', 'betait-letsreg'); ?></p>
    </div>

    <table class="form-table betait-letsreg-table">
        <tr>
            <th>
                <label for="betait_letsreg_base_url">
                    <?php esc_html_e('Base API URL', 'betait-letsreg'); ?>
                </label>
            </th>
            <td>
                <input type="text"
                       name="betait_letsreg_base_url"
                       id="betait_letsreg_base_url"
                       value="<?php echo esc_attr($base_url_value); ?>"
                       class="regular-text" />
            </td>
        </tr>

       <!-- Endepunktkonfigurasjon -->
       <tr>
                <th colspan="2">
                    <label><?php esc_html_e('Endepunktkonfigurasjon', 'betait-letsreg'); ?></label>
                </th>
            </tr>
            <?php 
            foreach ( $endpoints as $index => $ep ) : 
                $method_field = sprintf('betait_letsreg_endpoints[%d][method]', $index);
                $slug_field   = sprintf('betait_letsreg_endpoints[%d][slug]', $index);
                $url_field    = sprintf('betait_letsreg_endpoints[%d][url]', $index);
            ?>
                <tr>
                    <th>
                        <label>
                            <?php esc_html_e('Endepunkt:', 'betait-letsreg'); ?>
                            <?php echo esc_html($ep['slug']); ?>
                        </label>
                    </th>
                    <td>
                        <select name="<?php echo esc_attr($method_field); ?>">
                            <option value="GET"    <?php selected($ep['method'], 'GET'); ?>>GET</option>
                            <option value="POST"   <?php selected($ep['method'], 'POST'); ?>>POST</option>
                            <option value="PUT"    <?php selected($ep['method'], 'PUT'); ?>>PUT</option>
                            <option value="DELETE" <?php selected($ep['method'], 'DELETE'); ?>>DELETE</option>
                        </select>
                        <input type="text" 
                               name="<?php echo esc_attr($url_field); ?>" 
                               value="<?php echo esc_attr($ep['url']); ?>" 
                               class="regular-text"
                               placeholder="https://integrate.deltager.no/organizers" />
                        <p class="description">
                            <?php printf(
                                esc_html__('Slug: %s', 'betait-letsreg'),
                                esc_html($ep['slug'])
                            ); ?>
                        </p>
                    </td>
                </tr>
            <?php endforeach; ?>

            <!-- Reset to Default Button -->
            <tr>
                <th colspan="2">
                    <button type="submit" name="betait_letsreg_reset_defaults" class="button button-danger" onclick="return confirm('<?php esc_attr_e('Er du sikker på at du vil tilbakestille til standardinnstillinger?', 'betait-letsreg'); ?>');">
                        <?php esc_html_e('Tilbakestill til standard', 'betait-letsreg'); ?>
                    </button>
                </th>
            </tr>
        </table>
    </div>
    
    <?php submit_button( __('Lagre endringer', 'betait-letsreg'), 'primary', 'betait_letsreg_save_settings' ); ?>
</form>
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


