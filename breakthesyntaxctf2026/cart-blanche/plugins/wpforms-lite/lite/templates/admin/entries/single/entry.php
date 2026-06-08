<?php
/**
 * Entry single page.
 *
 * @since 1.8.9
 *
 * @var array $utm UTM links.
 */

// Exit if accessed directly.
use WPForms\Admin\Education\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="wpforms-entries-single" class="wrap wpforms-admin-wrap wpforms-entries-single-sample">
	<h1 class="page-title">
		View Entry
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpforms-entries' ) ); ?>" class="page-title-action wpforms-btn wpforms-btn-light-grey">
			<svg viewBox="0 0 16 14" class="page-title-action-icon">
				<path d="M16 6v2H4l4 4-1 2-7-7 7-7 1 2-4 4h12Z"/>
			</svg>
			<span class="page-title-action-text"><?php esc_html_e( 'Back to All Entries', 'wpforms-lite' ); ?></span>
		</a>

		<div class="wpforms-admin-single-navigation">
			<div class="wpforms-admin-single-navigation-text">
				Entry 1 of 12
			</div>
			<div class="wpforms-admin-single-navigation-buttons wpforms-input-disabled">
				<div id="wpforms-admin-single-navigation-prev-link" class="wpforms-btn-grey inactive">
					<span class="dashicons dashicons-arrow-left-alt2"></span>
				</div>
				<span class="wpforms-admin-single-navigation-current"> 1 </span>
				<div id="wpforms-admin-single-navigation-next-link" class="wpforms-btn-grey">
					<span class="dashicons dashicons-arrow-right-alt2"></span>
				</div>
			</div>

			<div class="wpforms-entries-settings-container">
				<button id="wpforms-entries-settings-button" class="wpforms-entries-settings-button button" type="button">
					<span class="dashicons dashicons-admin-generic"></span>
				</button>
				<div class="wpforms-entries-settings-menu" aria-expanded="false" style="display: none;">
					<div class="wpforms-entries-settings-menu-wrap wpforms-entries-settings-menu-items wpforms-input-disabled" aria-disabled="true">
						<div class="wpforms-settings-title">Field Settings</div>
						<span class="wpforms-toggle-control">
							<input type="checkbox" id="wpforms-entry-setting-show_field_descriptions" name="show_field_descriptions" value="1">
							<label class="wpforms-toggle-control-icon" for="wpforms-entry-setting-show_field_descriptions"></label>
							<label for="wpforms-entry-setting-show_field_descriptions" class="wpforms-toggle-control-label">Field Descriptions</label>
						</span>
						<span class="wpforms-toggle-control">
							<input type="checkbox" id="wpforms-entry-setting-show_empty_fields" name="show_empty_fields" value="1" checked="checked">
							<label class="wpforms-toggle-control-icon" for="wpforms-entry-setting-show_empty_fields"></label>
							<label for="wpforms-entry-setting-show_empty_fields" class="wpforms-toggle-control-label">Empty Fields</label>
						</span>
						<span class="wpforms-toggle-control">
							<input type="checkbox" id="wpforms-entry-setting-show_unselected_choices" name="show_unselected_choices" value="1">
							<label class="wpforms-toggle-control-icon" for="wpforms-entry-setting-show_unselected_choices"></label>
							<label for="wpforms-entry-setting-show_unselected_choices" class="wpforms-toggle-control-label">Unselected Choices</label>
						</span>
						<span class="wpforms-toggle-control">
							<input type="checkbox" id="wpforms-entry-setting-show_html_fields" name="show_html_fields" value="1">
							<label class="wpforms-toggle-control-icon" for="wpforms-entry-setting-show_html_fields"></label>
							<label for="wpforms-entry-setting-show_html_fields" class="wpforms-toggle-control-label">HTML/Content Fields</label>
						</span>
						<span class="wpforms-toggle-control">
							<input type="checkbox" id="wpforms-entry-setting-show_section_dividers" name="show_section_dividers" value="1" checked="checked">
							<label class="wpforms-toggle-control-icon" for="wpforms-entry-setting-show_section_dividers"></label>
							<label for="wpforms-entry-setting-show_section_dividers" class="wpforms-toggle-control-label">Section Dividers</label>
						</span>
						<span class="wpforms-toggle-control">
							<input type="checkbox" id="wpforms-entry-setting-show_page_breaks" name="show_page_breaks" value="1" checked="checked">
							<label class="wpforms-toggle-control-icon" for="wpforms-entry-setting-show_page_breaks"></label>
							<label for="wpforms-entry-setting-show_page_breaks" class="wpforms-toggle-control-label">Page Breaks</label>
						</span>
						<div class="wpforms-settings-title">Display Settings</div>
						<span class="wpforms-toggle-control">
							<input type="checkbox" id="wpforms-entry-setting-maintain_layouts" name="maintain_layouts" value="1">
							<label class="wpforms-toggle-control-icon" for="wpforms-entry-setting-maintain_layouts"></label>
							<label for="wpforms-entry-setting-maintain_layouts" class="wpforms-toggle-control-label">Maintain Layouts</label>
						</span>
						<span class="wpforms-toggle-control">
							<input type="checkbox" id="wpforms-entry-setting-compact_view" name="compact_view" value="1">
							<label class="wpforms-toggle-control-icon" for="wpforms-entry-setting-compact_view"></label>
							<label for="wpforms-entry-setting-compact_view" class="wpforms-toggle-control-label">Compact View</label>
						</span>
					</div>
				</div>
			</div>
		</div>

	</h1>

	<?php
	echo wpforms_render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		'admin/entries/notice',
		[
			'btn_utm'  => $utm['entry_single_button'],
			'link_utm' => $utm['entry_single_link'],
		],
		true
	);
	?>
	<div class="wpforms-admin-content">
		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">
				<!-- Left column -->
				<div id="post-body-content" style="position: relative;">
					<!-- Entry Fields metabox -->
					<div id="wpforms-entry-fields" class="postbox">

						<div class="postbox-header">
							<h2>General Inquiry Form</h2>
						</div>

						<div class="inside">

							<div class="wpforms-entries-fields-wrapper wpforms-entry-maintain-layout">
								<div class="wpforms-entry-field-item field entry-field-item wpforms-field-name wpforms-field-entry-name wpforms-field-entry-fields">
									<p class="wpforms-entry-field-name">Name</p>
									<div class="wpforms-entry-field-value">Michael Johnson</div>
								</div>
								<div class="wpforms-entry-field-item field entry-field-item wpforms-field-email wpforms-field-entry-email wpforms-field-entry-fields wpforms-entry-field-row-alt">
									<p class="wpforms-entry-field-name">Email</p>
									<div class="wpforms-entry-field-value">michael.johnson@example.com</div>
								</div>
								<div class="wpforms-entry-field-item field entry-field-item wpforms-field-phone wpforms-field-entry-phone wpforms-field-entry-fields wpforms-entry-field-row-alt">
									<p class="wpforms-entry-field-name">Phone</p>
									<div class="wpforms-entry-field-value">+1-206-555-6789</div>
								</div>
								<div class="wpforms-entry-field-item field entry-field-item wpforms-field-textarea wpforms-field-entry-textarea wpforms-field-entry-fields">
									<p class="wpforms-entry-field-name">Comment or Message</p>
									<div class="wpforms-entry-field-value">I really enjoyed your insightful posts on your blog! Your writing is engaging and makes complex topics easy to understand. I'm eager to dive deeper into your passion. Keep up the fantastic work! Oh BTW, I uploaded an illustration I created that’s inspired by your writing. I hope you like it!</div>
								</div>
								<div class="wpforms-entry-field-item field entry-field-item wpforms-field-file-upload wpforms-field-entry-file-upload wpforms-field-entry-fields wpforms-entry-field-row-alt">
									<p class="wpforms-entry-field-name">File Upload</p>
									<div class="wpforms-entry-field-value">
										<span class="dashicons dashicons-media-document"></span>
										<span class="file-name">illustration.jpg</span>
									</div>
								</div>
								<div class="wpforms-entry-field-item field entry-field-item wpforms-field-signature wpforms-field-entry-signature wpforms-field-entry-fields">
									<p class="wpforms-entry-field-name">Signature</p>
									<div class="wpforms-entry-field-value">
										<img
											src="<?php echo esc_url( WPFORMS_PLUGIN_URL . 'assets/lite/images/sample/signature.png' ); ?>"
											srcset="<?php echo esc_url( WPFORMS_PLUGIN_URL . 'assets/lite/images/sample/signature@2x.png' ); ?> 2x"
											style="max-width:100%;margin:0"
											alt=""
										>
									</div>
								</div>
							</div>
						</div>

					</div>
					<!-- Geolocation metabox -->
					<div id="wpforms-entry-geolocation" class="postbox">
						<div class="postbox-header">
							<h2>Location<?php Helpers::print_badge( 'Pro', 'sm', 'inline', 'platinum' ); ?></h2>
						</div>

						<div class="inside">
							<img
								class="wpforms-input-disabled"
								aria-disabled="true"
								src="<?php echo esc_url( WPFORMS_PLUGIN_URL . 'assets/lite/images/sample/map.png' ); ?>"
								srcset="<?php echo esc_url( WPFORMS_PLUGIN_URL . 'assets/lite/images/sample/map@2x.png' ); ?> 2x"
								style="width: 100%"
								alt=""
							>
							<ul>
								<li>
									<span class="wpforms-geolocation-meta">Location</span>
									<span class="wpforms-geolocation-value">Seattle, Washington</span>
								</li>
								<li>
									<span class="wpforms-geolocation-meta">Postal</span>
									<span class="wpforms-geolocation-value">98125</span>
								</li>
								<li>
									<span class="wpforms-geolocation-meta">Country</span>
									<span class="wpforms-geolocation-value">
										<img
											class="wpforms-geolocation-flag"
											src="<?php echo esc_url( WPFORMS_PLUGIN_URL . 'assets/lite/images/sample/flag.png' ); ?>"
											srcset="<?php echo esc_url( WPFORMS_PLUGIN_URL . 'assets/lite/images/sample/flag@2x.png' ); ?> 2x"
											alt=""
										>
										US
									</span>
								</li>
								<li>
									<span class="wpforms-geolocation-meta">Lat/Long</span>
									<span class="wpforms-geolocation-value">47.6061, -122.3328</span>
								</li>
							</ul>
						</div>
					</div>
					<!-- User Journey metabox -->
					<div id="wpforms-entry-user-journey" class="postbox">
						<div class="postbox-header">
							<h2>User Journey<?php Helpers::print_badge( 'Pro', 'sm', 'inline', 'platinum' ); ?></h2>
						</div>
						<div class="inside">
							<table width="100%" cellspacing="0" cellpadding="0">
								<thead>
									<tr>
										<th colspan="3" class="date">April 22, 2024</th>
									</tr>
								</thead>
								<tbody>
									<tr class="visit">
										<td class="time">12:23 pm</td>
										<td class="title-area">
											<span class="title">Search Results</span>
											<i class="fa fa-circle" aria-hidden="true"></i>
											<span class="path">/ <em>(Homepage)</em></span>
											<span class="go wpforms-input-disabled" aria-disabled="true">
												<i class="fa fa-external-link" aria-hidden="true"></i>
											</span>
										</td>
										<td class="duration"></td>
									</tr>

									<tr class="submit">
										<td class="time">12:23 pm</td>
										<td class="title-area">
											<span class="title">Homepage</span>
											<i class="fa fa-circle" aria-hidden="true"></i>
											<span class="path"><em>https://www.google.com/</em></span>
											<span class="go wpforms-input-disabled" aria-disabled="true">
												<i class="fa fa-external-link" aria-hidden="true"></i>
											</span>
										</td>

										<td class="duration">56 seconds</td>
									</tr>

									<tr class="submit">
										<td class="time">12:24 pm</td>
										<td class="title-area">
											<span class="title">Frequently Asked Questions</span>
											<i class="fa fa-circle" aria-hidden="true"></i>
											<span class="path"><em>/faq/</em></span>
											<span class="go wpforms-input-disabled" aria-disabled="true">
												<i class="fa fa-external-link" aria-hidden="true"></i>
											</span>
										</td>

										<td class="duration">1 min</td>
									</tr>

									<tr class="submit">
										<td class="time">12:25 pm</td>
										<td class="title-area">
											<span class="title">About Us</span>
											<i class="fa fa-circle" aria-hidden="true"></i>
											<span class="path"><em>/about-us/</em></span>
											<span class="go wpforms-input-disabled" aria-disabled="true">
												<i class="fa fa-external-link" aria-hidden="true"></i>
											</span>
										</td>

										<td class="duration">3 mins</td>
									</tr>

									<tr class="submit">
										<td class="time">12:28 pm</td>
										<td class="title-area">
											<span class="title">Meet The Team</span>
											<i class="fa fa-circle" aria-hidden="true"></i>
											<span class="path"><em>/about-us/meet-the-team/</em></span>
											<span class="go wpforms-input-disabled" aria-disabled="true">
												<i class="fa fa-external-link" aria-hidden="true"></i>
											</span>
										</td>

										<td class="duration">3 mins</td>
									</tr>

									<tr class="submit">
										<td class="time">12:31 pm</td>
										<td class="title-area">
											<span class="title">Testimonials</span>
											<i class="fa fa-circle" aria-hidden="true"></i>
											<span class="path"><em>/testimonials/</em></span>
											<span class="go wpforms-input-disabled" aria-disabled="true">
												<i class="fa fa-external-link" aria-hidden="true"></i>
											</span>
										</td>

										<td class="duration">2 mins</td>
									</tr>

									<tr class="submit">
										<td class="time">12:33 pm</td>
										<td class="title-area">
											<span class="title">General Inquiry Form</span>
											<i class="fa fa-circle" aria-hidden="true"></i>
											<span class="path"><em>/general-inquiry-form/</em></span>
											<span class="go wpforms-input-disabled" aria-disabled="true">
												<i class="fa fa-external-link" aria-hidden="true"></i>
											</span>
										</td>

										<td class="duration">4 mins</td>
									</tr>

									<tr class="submit">
										<td class="time">12:37 pm</td>
										<td class="title-area">
											<i class="fa fa-check" aria-hidden="true"></i>
											<span class="title">General Inquiry Form Submitted</span>
											<i class="fa fa-circle" aria-hidden="true"></i>
											<span class="path">User took 7 steps over 14 mins</span>
										</td>

										<td class="duration"></td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
					<!-- Entry Notes metabox -->
					<div id="wpforms-entry-notes" class="postbox">

						<div class="postbox-header">
							<h2>Notes</h2>
						</div>

						<div class="inside">
							<div class="wpforms-entry-sample-notes-new wpforms-input-disabled" aria-disabled="true">
								<span class="button add">Add Note</span>
							</div>

							<div class="wpforms-entry-notes-list">
								<div class="wpforms-entry-notes-single odd">
									<div class="wpforms-entry-notes-byline">
										Added by <span class="note-user wpforms-input-disabled" aria-disabled="true">Barry Huggins</span> on April 29, 2024 at 4:42 pm <span class="sep">|</span> <span class="sample-note-delete wpforms-input-disabled"> Delete </span>
									</div>
									<p><span>My illustration of the ones that have been submitted so far. We should reach out to him and offer a t-shirt.</span></p>
								</div>
								<div class="wpforms-entry-notes-single even">
									<div class="wpforms-entry-notes-byline">
										Added by <span class="note-user wpforms-input-disabled" aria-disabled="true">Teddy Bearington</span> on April 25, 2024 at 2:17 pm <span class="sep">|</span> <span class="sample-note-delete wpforms-input-disabled"> Delete </span>
									</div>
									<p><span>This person went above and beyond.</span></p>
								</div>
							</div>
						</div>

					</div>
				</div>

				<!-- Right column -->
				<div id="postbox-container-1" class="postbox-container">
					<!-- Entry Details metabox -->
					<div id="wpforms-entry-details" class="postbox">
						<div class="postbox-header">
							<h2>Entry Details</h2>
						</div>

						<div class="inside">
							<div class="wpforms-entry-details-meta">
								<p class="wpforms-entry-id">
									<span class="dashicons dashicons-admin-network"></span> Entry ID:
									<strong>544</strong>
								</p>

								<p class="wpforms-entry-date">
									<span class="dashicons dashicons-calendar"></span> Submitted:
									<strong class="date-time"> April 22, 2024 at 12:37 PM </strong>
								</p>

								<p class="wpforms-entry-modified">
									<span class="dashicons dashicons-calendar-alt"></span> Modified:
									<strong class="date-time"> April 22, 2024 at 12:37 PM </strong>
								</p>

								<p class="wpforms-entry-ip">
									<span class="dashicons dashicons-location"></span> User IP:
									<strong>192.168.1.100</strong>
								</p>
							</div>

							<div id="major-publishing-actions">
								<div id="publishing-action" class="wpforms-input-disabled" aria-disabled="true">
									<span class="button button-primary button-large">Edit</span>
								</div>
								<div id="delete-action" class="wpforms-input-disabled" aria-disabled="true">
									<span class="trash-sample">Trash Entry</span>
								</div>

								<div class="clear"></div>
							</div>
						</div>
					</div>

					<!-- Entry Actions metabox -->
					<div id="wpforms-entry-actions" class="postbox">
						<div class="postbox-header">
							<h2>Actions</h2>
						</div>

						<div class="inside">
							<div class="wpforms-entry-actions-meta">
								<p class="wpforms-entry-print-sample"><a href="https://wpforms.com/docs/how-to-print-form-entries/?utm_campaign=liteplugin&utm_source=WordPress&utm_medium=entries&utm_content=Print%20-%20Single%20Entry" target="_blank" rel="noopener noreferrer"><i class="dashicons dashicons-media-text"></i>Print</a></p>
								<p class="wpforms-entry-export"><a href="https://wpforms.com/docs/how-to-export-form-entries-to-csv-in-wpforms/?utm_campaign=liteplugin&utm_source=WordPress&utm_medium=entries&utm_content=Export%20CSV%20-%20Single%20Entry" target="_blank" rel="noopener noreferrer"><i class="dashicons dashicons-migrate"></i>Export (CSV)</a></p>
								<p class="wpforms-entry-export_xlsx"><a href="https://wpforms.com/docs/how-to-export-form-entries-to-csv-in-wpforms/?utm_campaign=liteplugin&utm_source=WordPress&utm_medium=entries&utm_content=Export%20XLSX%20-%20Single%20Entry" target="_blank" rel="noopener noreferrer"><i class="dashicons dashicons-media-spreadsheet"></i>Export (XLSX)</a></p>
								<p class="wpforms-entry-notifications"><i class="dashicons dashicons-email-alt"></i><span>Resend Notifications</span></p>
								<p class="wpforms-entry-star wpforms-input-disabled" aria-disabled="true"><i class="dashicons dashicons-star-filled"></i><span>Star</span></p>
								<p class="wpforms-entry-read wpforms-input-disabled" aria-disabled="true"><i class="dashicons dashicons-hidden"></i><span>Mark as Unread</span></p>
								<p class="wpforms-entry-spam wpforms-input-disabled" aria-disabled="true"><i class="dashicons dashicons-shield"></i><span>Mark as Spam</span></p>
								<p class="wpforms-entry-delete-sample wpforms-input-disabled" aria-disabled="true"><i class="dashicons dashicons-trash"></i><span>Delete Entry</span></p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
