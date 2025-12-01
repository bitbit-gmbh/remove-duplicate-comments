<?php
/**
 * Admin page template for Remove Duplicate Comments plugin
 *
 * This is the admin page template for the duplicate comment removal interface.
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap" id="rdc-admin-page">
	<h1><?php echo esc_html( __( 'Remove Duplicate Comments', 'remove-duplicate-comments' ) ); ?></h1>
	<hr class="wp-header-end">

	<div class="rdc-backup-warning">
		<span class="dashicons dashicons-warning"></span>
		<strong><?php esc_html_e( 'Important: Backup Your Database!', 'remove-duplicate-comments' ); ?></strong>
		<p><?php esc_html_e( 'This action moves duplicate comments to the trash. Please backup your database before proceeding.', 'remove-duplicate-comments' ); ?></p>
	</div>

	<form id="rdc-duplicate-form" method="post">
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'Comment Status', 'remove-duplicate-comments' ); ?></th>
				<td>
					<fieldset>
						<legend class="screen-reader-text"><span><?php esc_html_e( 'Comment Status', 'remove-duplicate-comments' ); ?></span></legend>
						<label><input type="checkbox" name="statuses[]" value="1" checked="checked"> <?php esc_html_e( 'Approved', 'remove-duplicate-comments' ); ?></label><br>
						<label><input type="checkbox" name="statuses[]" value="0"> <?php esc_html_e( 'Pending', 'remove-duplicate-comments' ); ?></label><br>
						<label><input type="checkbox" name="statuses[]" value="spam"> <?php esc_html_e( 'Spam', 'remove-duplicate-comments' ); ?></label><br>
						<label><input type="checkbox" name="statuses[]" value="trash"> <?php esc_html_e( 'Trash', 'remove-duplicate-comments' ); ?></label>
					</fieldset>
					<p class="description"><?php esc_html_e( 'Select which comment statuses to check for duplicates.', 'remove-duplicate-comments' ); ?></p>
				</td>
			</tr>
		</table>

		<p class="submit">
			<button type="submit" id="rdc-start-button" class="button button-primary"><?php esc_html_e( 'Find & Move Duplicates to Trash', 'remove-duplicate-comments' ); ?></button>
			<span class="spinner"></span>
		</p>
	</form>

	<div id="rdc-progress" style="display: none;">
		<h2><?php esc_html_e( 'Processing...', 'remove-duplicate-comments' ); ?></h2>
		<div class="rdc-progress-stats">
			<p><strong><?php esc_html_e( 'Processed:', 'remove-duplicate-comments' ); ?></strong> <span id="rdc-processed-count">0</span> <span id="rdc-processed-label"><?php esc_html_e( 'comments', 'remove-duplicate-comments' ); ?></span></p>
			<p><strong><?php esc_html_e( 'Trashed:', 'remove-duplicate-comments' ); ?></strong> <span id="rdc-trashed-count">0</span> <span id="rdc-trashed-label"><?php esc_html_e( 'duplicates', 'remove-duplicate-comments' ); ?></span></p>
		</div>
		<div class="rdc-progress-bar">
			<div class="rdc-progress-bar-fill"></div>
		</div>
	</div>

	<div id="rdc-results" style="display: none;"></div>
</div><!-- .wrap -->