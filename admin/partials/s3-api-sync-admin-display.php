<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       zekeswepson.com
 * @since      1.0.0
 *
 * @package    S3_Api_Sync
 * @subpackage S3_Api_Sync/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<form method="post" action="options.php">
    <?php settings_fields( 's3-api-sync-settings-group' ); ?>
    <?php do_settings_sections( 's3-api-sync-settings-group' ); ?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row">Only S3 Content</th>
        <td><input type="checkbox" name="only-save-s3" value="true" <?php 
                if (get_option('only-save-s3')==true) echo 'checked="checked" '; 
            ?>/>
        </td>
        </tr>
    
        <tr valign="top">
        <th scope="row">AWS Access Key ID</th>
        <td><input type="text" name="aws-access-key-id" value="<?php echo esc_attr( get_option('aws-access-key-id') ); ?>" /></td>
        </tr>
         
        <tr valign="top">
        <th scope="row">AWS Secret Acces Key</th>
        <td><input type="text" name="aws-secret-access-key" value="<?php echo esc_attr( get_option('aws-secret-access-key') ); ?>" /></td>
        </tr>
        
        <tr valign="top">
        <th scope="row">AWS Region</th>
        <td><input type="text" name="aws-region" value="<?php echo esc_attr( get_option('aws-region') ); ?>" /></td>
        </tr>

        <tr valign="top">
        <th scope="row">AWS Bucket</th>
        <td><input type="text" name="aws-bucket" value="<?php echo esc_attr( get_option('aws-bucket') ); ?>" /></td>
        </tr>

        <tr valign="top">
        <th scope="row">S3 Post Type</th>
        <td><input type="text" name="s3-post-type" value="<?php 
            echo esc_attr( get_option('s3-post-type')); 
            ?>" 
            <?php if (!get_option('only-save-s3')==true) echo 'disabled '; ?>
            />
        </td>
        </tr>
    </table>
    <?php submit_button(); ?>

</form>
</div>