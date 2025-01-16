<?php
/**
 * Partial-filen for “Organisator” i Betait LetsReg-pluginet.
 * Viser et skjema for å vise og redigere organisasjonsinformasjon.
 */

// Anta at $organizer_data er definert i den inkluderte filen
?>

<div class="wrap betait-letsreg-wrap">

    <header class="betait-letsreg-header">
        <h1><?php esc_html_e('Organisator - Rediger', 'betait-letsreg'); ?></h1>
        <p class="description"><?php esc_html_e('Her kan du vise og redigere informasjon om den primære organisasjonen.', 'betait-letsreg'); ?></p>
    </header>

    <main class="betait-letsreg-main">

    <?php
    $is_read_only = get_option('betait_letsreg_read_only', 0);
    $form_attributes = '';

    if ($is_read_only) {
        $form_attributes = 'form-is-readonly=true';
    }?>
        <form method="post" action="" <?php echo esc_attr($form_attributes); ?>>
            <?php
            // Nonce for sikkerhet
            wp_nonce_field( 'betait_letsreg_update_organizer_action', 'betait_letsreg_update_organizer_nonce' );
            ?>
       <?php if ($is_read_only): ?>
            <div class="readonly-message">
                <?php esc_html_e('Dette skjema er i Read Only-modus og kan ikke lagres.', 'betait-letsreg'); ?>
            </div>
        <?php endif; ?>
            <table class="form-table betait-letsreg-table">

                <!-- Organisasjonsnavn -->
                <tr>
                    <th><label for="betait_letsreg_org_name"><?php esc_html_e('Organisasjonsnavn', 'betait-letsreg'); ?></label></th>
                    <td>
                        <input type="text"
                               name="betait_letsreg_org_name"
                               id="betait_letsreg_org_name"
                               value="<?php echo esc_attr( $organizer_data['name'] ?? '' ); ?>"
                               class="regular-text" />
                    </td>
                </tr>

                <!-- Beskrivelse -->
                <tr>
                    <th><label for="betait_letsreg_org_description"><?php esc_html_e('Beskrivelse', 'betait-letsreg'); ?></label></th>
                    <td>
                        <textarea name="betait_letsreg_org_description"
                                  id="betait_letsreg_org_description"
                                  rows="4"
                                  cols="50"
                                  class="large-text"><?php echo esc_textarea( $organizer_data['description'] ?? '' ); ?></textarea>
                    </td>
                </tr>

                <!-- Organisasjonsnummer -->
                <tr>
                    <th><label for="betait_letsreg_org_number"><?php esc_html_e('Organisasjonsnummer', 'betait-letsreg'); ?></label></th>
                    <td>
                        <input type="text"
                               name="betait_letsreg_org_number"
                               id="betait_letsreg_org_number"
                               value="<?php echo esc_attr( $organizer_data['organisationNumber'] ?? '' ); ?>"
                               class="regular-text" />
                    </td>
                </tr>

                <!-- Kontaktperson -->
                <tr>
                    <th colspan="2">
                        <h2><?php esc_html_e('Kontaktperson', 'betait-letsreg'); ?></h2>
                    </th>
                </tr>

                <!-- Kontaktperson - Navn -->
                <tr>
                    <th><label for="betait_letsreg_contact_name"><?php esc_html_e('Navn', 'betait-letsreg'); ?></label></th>
                    <td>
                        <input type="text"
                               name="betait_letsreg_contact_name"
                               id="betait_letsreg_contact_name"
                               value="<?php echo esc_attr( $organizer_data['contactPerson']['name'] ?? '' ); ?>"
                               class="regular-text" />
                    </td>
                </tr>

                <!-- Kontaktperson - Telefon -->
                <tr>
                    <th><label for="betait_letsreg_contact_phone"><?php esc_html_e('Telefon', 'betait-letsreg'); ?></label></th>
                    <td>
                        <input type="text"
                               name="betait_letsreg_contact_phone"
                               id="betait_letsreg_contact_phone"
                               value="<?php echo esc_attr( $organizer_data['contactPerson']['telephone'] ?? '' ); ?>"
                               class="regular-text" />
                    </td>
                </tr>

                <!-- Kontaktperson - Mobil -->
                <tr>
                    <th><label for="betait_letsreg_contact_mobile"><?php esc_html_e('Mobil', 'betait-letsreg'); ?></label></th>
                    <td>
                        <input type="text"
                               name="betait_letsreg_contact_mobile"
                               id="betait_letsreg_contact_mobile"
                               value="<?php echo esc_attr( $organizer_data['contactPerson']['mobile'] ?? '' ); ?>"
                               class="regular-text" />
                    </td>
                </tr>

                <!-- Kontaktperson - E-post -->
                <tr>
                    <th><label for="betait_letsreg_contact_email"><?php esc_html_e('E-post', 'betait-letsreg'); ?></label></th>
                    <td>
                        <input type="email"
                               name="betait_letsreg_contact_email"
                               id="betait_letsreg_contact_email"
                               value="<?php echo esc_attr( $organizer_data['contactPerson']['email'] ?? '' ); ?>"
                               class="regular-text" />
                    </td>
                </tr>

                <!-- Adresse -->
                <tr>
                    <th colspan="2">
                        <h2><?php esc_html_e('Adresse', 'betait-letsreg'); ?></h2>
                    </th>
                </tr>

                <!-- Adresse 1 -->
                <tr>
                    <th><label for="betait_letsreg_address1"><?php esc_html_e('Adresse 1', 'betait-letsreg'); ?></label></th>
                    <td>
                        <input type="text"
                               name="betait_letsreg_address1"
                               id="betait_letsreg_address1"
                               value="<?php echo esc_attr( $organizer_data['address']['address1'] ?? '' ); ?>"
                               class="regular-text" />
                    </td>
                </tr>

                <!-- Adresse 2 -->
                <tr>
                    <th><label for="betait_letsreg_address2"><?php esc_html_e('Adresse 2', 'betait-letsreg'); ?></label></th>
                    <td>
                        <input type="text"
                               name="betait_letsreg_address2"
                               id="betait_letsreg_address2"
                               value="<?php echo esc_attr( $organizer_data['address']['address2'] ?? '' ); ?>"
                               class="regular-text" />
                    </td>
                </tr>

                <!-- Postnummer -->
                <tr>
                    <th><label for="betait_letsreg_postcode"><?php esc_html_e('Postnummer', 'betait-letsreg'); ?></label></th>
                    <td>
                        <input type="text"
                               name="betait_letsreg_postcode"
                               id="betait_letsreg_postcode"
                               value="<?php echo esc_attr( $organizer_data['address']['postCode'] ?? '' ); ?>"
                               class="regular-text" />
                    </td>
                </tr>

                <!-- By -->
                <tr>
                    <th><label for="betait_letsreg_city"><?php esc_html_e('By', 'betait-letsreg'); ?></label></th>
                    <td>
                        <input type="text"
                               name="betait_letsreg_city"
                               id="betait_letsreg_city"
                               value="<?php echo esc_attr( $organizer_data['address']['city'] ?? '' ); ?>"
                               class="regular-text" />
                    </td>
                </tr>

                <!-- Bankkontoer -->
                <tr>
                    <th colspan="2">
                        <h2><?php esc_html_e('Bankkontoer', 'betait-letsreg'); ?></h2>
                    </th>
                </tr>

                <?php
                if ( ! empty( $organizer_data['bankAccounts'] ) && is_array( $organizer_data['bankAccounts'] ) ) :
                    foreach ( $organizer_data['bankAccounts'] as $index => $bank ) :
                        ?>
                        <tr>
                            <th>
                                <label><?php printf( esc_html__('Bankkonto %d', 'betait-letsreg'), $index + 1 ); ?></label>
                            </th>
                            <td>
                                <input type="hidden" name="betait_letsreg_bank_accounts[<?php echo esc_attr($index); ?>][id]" value="<?php echo esc_attr( $bank['id'] ); ?>" />
                                <input type="hidden" name="betait_letsreg_bank_accounts[<?php echo esc_attr($index); ?>][organizerId]" value="<?php echo esc_attr( $bank['organizerId'] ); ?>" />

                                <!-- Kontonummer -->
                                <p>
                                    <label for="betait_letsreg_bank_accounts_<?php echo esc_attr($index); ?>_account_number"><?php esc_html_e('Kontonummer', 'betait-letsreg'); ?></label><br />
                                    <input type="text"
                                           name="betait_letsreg_bank_accounts[<?php echo esc_attr($index); ?>][account_number]"
                                           id="betait_letsreg_bank_accounts_<?php echo esc_attr($index); ?>_account_number"
                                           value="<?php echo esc_attr( $bank['accountNumber'] ); ?>"
                                           class="regular-text" />
                                </p>

                                <!-- Sorteringskode -->
                                <p>
                                    <label for="betait_letsreg_bank_accounts_<?php echo esc_attr($index); ?>_sort_code"><?php esc_html_e('Sorteringskode', 'betait-letsreg'); ?></label><br />
                                    <input type="text"
                                           name="betait_letsreg_bank_accounts[<?php echo esc_attr($index); ?>][sort_code]"
                                           id="betait_letsreg_bank_accounts_<?php echo esc_attr($index); ?>_sort_code"
                                           value="<?php echo esc_attr( $bank['sortCode'] ); ?>"
                                           class="regular-text" />
                                </p>

                                <!-- Alias -->
                                <p>
                                    <label for="betait_letsreg_bank_accounts_<?php echo esc_attr($index); ?>_alias"><?php esc_html_e('Alias', 'betait-letsreg'); ?></label><br />
                                    <input type="text"
                                           name="betait_letsreg_bank_accounts[<?php echo esc_attr($index); ?>][alias]"
                                           id="betait_letsreg_bank_accounts_<?php echo esc_attr($index); ?>_alias"
                                           value="<?php echo esc_attr( $bank['alias'] ); ?>"
                                           class="regular-text" />
                                </p>

                                <!-- Standardkonto -->
                                <p>
                                    <label for="betait_letsreg_bank_accounts_<?php echo esc_attr($index); ?>_is_default">
                                        <input type="checkbox"
                                               name="betait_letsreg_bank_accounts[<?php echo esc_attr($index); ?>][is_default]"
                                               id="betait_letsreg_bank_accounts_<?php echo esc_attr($index); ?>_is_default"
                                               value="1"
                                               <?php checked( $bank['isDefault'], true ); ?> />
                                        <?php esc_html_e('Sett som standardkonto', 'betait-letsreg'); ?>
                                    </label>
                                </p>
                            </td>
                        </tr>
                        <?php
                    endforeach;
                else :
                    ?>
                    <tr>
                        <th colspan="2">
                            <p><?php esc_html_e('Ingen bankkontoer funnet.', 'betait-letsreg'); ?></p>
                        </th>
                    </tr>
                    <?php
                endif;
                ?>

            </table>

            <p>
                <button type="submit" name="betait_letsreg_update_organizer" class="button button-primary">
                    <?php esc_html_e('Oppdater Organisator', 'betait-letsreg'); ?>
                </button>
            </p>
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
