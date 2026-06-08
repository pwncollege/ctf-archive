<?php
/**
 * Summary footer template.
 *
 * This template can be overridden by copying it to yourtheme/wpforms/emails/summary-footer.php.
 *
 * @since 1.6.2.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

									</td>
								</tr>
								<tr>
									<td align="<?php echo is_rtl() ? 'right' : 'left'; ?>" valign="top" class="footer">
										<?php
										printf(
											wp_kses( /* translators: %1$s - site URL, %2$s - link to Settings -> Misc tab in plugin, %3$s - link to the documentation. */
												__( 'This email was auto-generated and sent from %1$s. <br> If you want to disable these weekly emails, click <a href="%2$s">here</a>, or read the guide <a href="%3$s">here</a>.', 'wpforms-lite' ),
												[
													'a'  => [
														'href' => [],
													],
													'br' => [],
												]
											),
											'<a href="' . esc_url( home_url() ) . '">' . esc_html( wp_specialchars_decode( get_bloginfo( 'name' ) ) ) . '</a>',
											esc_url( admin_url( 'admin.php?page=wpforms-settings&view=misc' ) ),
											'https://wpforms.com/docs/how-to-use-email-summaries/#disable-email-summaries'
										);
										?>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</div>
		</td>
		<td><!-- Deliberately empty to support consistent sizing and layout across multiple email clients. --></td>
	</tr>
</table>
</body>
</html>
