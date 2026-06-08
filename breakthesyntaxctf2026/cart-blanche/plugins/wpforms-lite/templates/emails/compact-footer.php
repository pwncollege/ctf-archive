<?php
/**
 * Compact footer template.
 *
 * This template can be overridden by copying it to yourtheme/wpforms/emails/compact-footer.php.
 *
 * @since 1.8.5
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
										/** This filter is documented in /includes/emails/templates/footer-default.php */
										echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
											'wpforms_email_footer_text',
											sprintf(
												wp_kses( /* translators: %1$s - site URL; %2$s - site title. */
													__( 'Sent from <a href="%1$s">%2$s</a>', 'wpforms-lite' ),
													[
														'a' => [
															'href' => [],
														],
													]
												),
												esc_url( home_url() ),
												wp_specialchars_decode( get_bloginfo( 'name' ) )
											)
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
