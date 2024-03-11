<div class="metabox-holder">
    <div class="postbox">
        <h3><span><?php esc_html_e( 'Export Settings', 'userswp' ); ?></span></h3>
        <div class="inside">
            <p><?php esc_html_e( 'Export the plugin settings for this site as a .json file. This allows you to easily import the configuration into another site.', 'userswp' ); ?></p>
            <form method="post">
                <p><input type="hidden" name="uwp_ie_action" value="export_settings" /></p>
                <p>
                    <?php wp_nonce_field( 'uwp_export_nonce', 'uwp_export_nonce' ); ?>
                    <?php submit_button( __( 'Export', 'userswp' ), 'primary', 'submit', false ); ?>
                </p>
            </form>
        </div>
    </div>

    <div class="postbox">
        <h3><span><?php esc_html_e( 'Import Settings', 'userswp' ); ?></span></h3>
        <div class="inside">
            <p><?php esc_html_e( 'Import the plugin settings from a .json file. This file can be obtained by exporting the settings on another site using the form above.', 'userswp' ); ?></p>
            <form method="post" enctype="multipart/form-data">
                <p>
                    <input type="file" name="import_file" class="uwp-import-setting-file"/>
                </p>
                <p>
                    <input type="hidden" name="uwp_ie_action" value="import_settings" />
                    <?php wp_nonce_field( 'uwp_import_nonce', 'uwp_import_nonce' ); ?>
                    <?php submit_button( __( 'Import', 'userswp' ), 'primary', 'submit', false ); ?>
                </p>
            </form>
        </div>
    </div>
</div>