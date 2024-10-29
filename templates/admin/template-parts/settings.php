<div class="wrap">
    <form method="post" action="">
        <?php wp_nonce_field( 'update_settings', 'settings_nonce' ); ?>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="api_key">OpenAI API Key</label></th>
                <td>
                    <?php aiwriter_api_field(); ?>
                </td>
            </tr>
        </table>
        <?php submit_button( 'Save' ); ?>
    </form>
</div>
<?php
