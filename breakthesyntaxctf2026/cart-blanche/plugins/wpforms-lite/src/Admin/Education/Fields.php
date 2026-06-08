<?php

namespace WPForms\Admin\Education;

/**
 * Fields data holder.
 *
 * @since 1.6.6
 */
class Fields {

	/**
	 * All fields data.
	 *
	 * @since 1.6.6
	 *
	 * @var array
	 */
	protected $fields;

	/**
	 * All fields data.
	 *
	 * @since 1.6.6
	 *
	 * @return array All possible fields.
	 */
	private function get_all(): array {

		if ( ! empty( $this->fields ) ) {
			return $this->fields;
		}

		$this->fields = [
			[
				'icon'    => 'fa-phone',
				'name'    => esc_html__( 'Phone', 'wpforms-lite' ),
				'name_en' => 'Phone',
				'type'    => 'phone',
				'group'   => 'fancy',
				'order'   => '50',
			],
			[
				'icon'    => 'fa-map-marker',
				'name'    => esc_html__( 'Address', 'wpforms-lite' ),
				'name_en' => 'Address',
				'type'    => 'address',
				'group'   => 'fancy',
				'order'   => '70',
			],
			[
				'icon'    => 'fa-map-location-dot',
				'name'    => esc_html__( 'Map', 'wpforms-lite' ),
				'name_en' => 'Map',
				'type'    => 'Map',
				'group'   => 'fancy',
				'addon'   => 'wpforms-geolocation',
				'order'   => '75',
			],
			[
				'icon'    => 'fa-calendar-o',
				'name'    => esc_html__( 'Date / Time', 'wpforms-lite' ),
				'name_en' => 'Date / Time',
				'type'    => 'date-time',
				'group'   => 'fancy',
				'order'   => '80',
			],
			[
				'icon'    => 'fa-link',
				'name'    => esc_html__( 'Website / URL', 'wpforms-lite' ),
				'name_en' => 'Website / URL',
				'type'    => 'url',
				'group'   => 'fancy',
				'order'   => '90',
			],
			[
				'icon'    => 'fa-upload',
				'name'    => esc_html__( 'File Upload', 'wpforms-lite' ),
				'name_en' => 'File Upload',
				'type'    => 'file-upload',
				'group'   => 'fancy',
				'order'   => '100',
			],
			[
				'icon'    => 'fa-camera',
				'name'    => esc_html__( 'Camera', 'wpforms-lite' ),
				'name_en' => 'Camera',
				'type'    => 'camera',
				'group'   => 'fancy',
				'order'   => '105',
			],
			[
				'icon'    => 'fa-lock',
				'name'    => esc_html__( 'Password', 'wpforms-lite' ),
				'name_en' => 'Password',
				'type'    => 'password',
				'group'   => 'fancy',
				'order'   => '95',
			],
			[
				'icon'    => 'fa-columns',
				'name'    => esc_html__( 'Layout', 'wpforms-lite' ),
				'name_en' => 'Layout',
				'type'    => 'layout',
				'group'   => 'fancy',
				'order'   => '140',
			],
			[
				'icon'    => 'fa-list',
				'name'    => esc_html__( 'Repeater', 'wpforms-lite' ),
				'name_en' => 'Repeater',
				'type'    => 'repeater',
				'group'   => 'fancy',
				'order'   => '150',
			],
			[
				'icon'    => 'fa-files-o',
				'name'    => esc_html__( 'Page Break', 'wpforms-lite' ),
				'name_en' => 'Page Break',
				'type'    => 'pagebreak',
				'group'   => 'fancy',
				'order'   => '160',
			],
			[
				'icon'    => 'fa-arrows-h',
				'name'    => esc_html__( 'Section Divider', 'wpforms-lite' ),
				'name_en' => 'Section Divider',
				'type'    => 'divider',
				'group'   => 'fancy',
				'order'   => '170',
			],
			[
				'icon'    => 'fa-pencil-square-o',
				'name'    => esc_html__( 'Rich Text', 'wpforms-lite' ),
				'name_en' => 'Rich Text',
				'type'    => 'richtext',
				'group'   => 'fancy',
				'order'   => '170',
			],
			[
				'icon'    => 'fa-file-image-o',
				'name'    => esc_html__( 'Content', 'wpforms-lite' ),
				'name_en' => 'Content',
				'type'    => 'content',
				'group'   => 'fancy',
				'order'   => '180',
			],
			[
				'icon'    => 'fa-code',
				'name'    => esc_html__( 'HTML', 'wpforms-lite' ),
				'name_en' => 'HTML',
				'type'    => 'html',
				'group'   => 'fancy',
				'order'   => '185',
			],
			[
				'icon'    => 'fa-file-text-o',
				'name'    => esc_html__( 'Entry Preview', 'wpforms-lite' ),
				'name_en' => 'Entry Preview',
				'type'    => 'entry-preview',
				'group'   => 'fancy',
				'order'   => '190',
			],
			[
				'icon'    => 'fa-star',
				'name'    => esc_html__( 'Rating', 'wpforms-lite' ),
				'name_en' => 'Rating',
				'type'    => 'rating',
				'group'   => 'fancy',
				'order'   => '310',
			],
			[
				'icon'    => 'fa-eye-slash',
				'name'    => esc_html__( 'Hidden Field', 'wpforms-lite' ),
				'name_en' => 'Hidden Field',
				'type'    => 'hidden',
				'group'   => 'fancy',
				'order'   => '98',
			],
			[
				'icon'     => 'fa-question-circle',
				'name'     => esc_html__( 'Custom Captcha', 'wpforms-lite' ),
				'keywords' => esc_html__( 'spam, math, maths, question', 'wpforms-lite' ),
				'name_en'  => 'Custom Captcha',
				'type'     => 'captcha',
				'group'    => 'fancy',
				'addon'    => 'wpforms-captcha',
				'order'    => '300',
			],
			[
				'icon'     => 'fa-pencil',
				'name'     => esc_html__( 'Signature', 'wpforms-lite' ),
				'keywords' => esc_html__( 'user, e-signature', 'wpforms-lite' ),
				'name_en'  => 'Signature',
				'type'     => 'signature',
				'group'    => 'fancy',
				'addon'    => 'wpforms-signatures',
				'order'    => '200',
			],
			[
				'icon'     => 'fa-ellipsis-h',
				'name'     => esc_html__( 'Likert Scale', 'wpforms-lite' ),
				'keywords' => esc_html__( 'survey, rating scale', 'wpforms-lite' ),
				'name_en'  => 'Likert Scale',
				'type'     => 'likert_scale',
				'group'    => 'fancy',
				'addon'    => 'wpforms-surveys-polls',
				'order'    => '400',
			],
			[
				'icon'     => 'fa-tachometer',
				'name'     => esc_html__( 'Net Promoter Score', 'wpforms-lite' ),
				'keywords' => esc_html__( 'survey, nps', 'wpforms-lite' ),
				'name_en'  => 'Net Promoter Score',
				'type'     => 'net_promoter_score',
				'group'    => 'fancy',
				'addon'    => 'wpforms-surveys-polls',
				'order'    => '410',
			],
			[
				'icon'     => 'fa-credit-card',
				'name'     => esc_html__( 'Authorize.Net', 'wpforms-lite' ),
				'keywords' => esc_html__( 'store, ecommerce, credit card, pay, payment, debit card', 'wpforms-lite' ),
				'name_en'  => 'Authorize.Net',
				'type'     => 'authorize_net',
				'group'    => 'payment',
				'addon'    => 'wpforms-authorize-net',
				'order'    => '95',
			],
			[
				'icon'     => 'fa-ticket',
				'name'     => esc_html__( 'Coupon', 'wpforms-lite' ),
				'keywords' => esc_html__( 'discount, sale', 'wpforms-lite' ),
				'name_en'  => 'Coupon',
				'type'     => 'payment-coupon',
				'group'    => 'payment',
				'addon'    => 'wpforms-coupons',
				'order'    => '100',
			],
		];

		$captcha = $this->get_captcha();

		if ( ! empty( $captcha ) ) {
			$this->fields[] = $captcha;
		}

		return $this->fields;
	}

	/**
	 * Get Captcha field data.
	 *
	 * @since 1.6.6
	 *
	 * @return array Captcha field data.
	 */
	private function get_captcha(): array {

		$captcha_settings = wpforms_get_captcha_settings();

		if ( empty( $captcha_settings['provider'] ) ) {
			return [];
		}

		$captcha = [
			'hcaptcha'  => [
				'name' => 'hCaptcha',
				'icon' => 'fa-question-circle-o',
			],
			'recaptcha' => [
				'name' => 'reCAPTCHA',
				'icon' => 'fa-google',
			],
			'turnstile' => [
				'name' => 'Turnstile',
				'icon' => 'fa-question-circle-o',
			],
		];

		if ( ! empty( $captcha_settings['site_key'] ) || ! empty( $captcha_settings['secret_key'] ) ) {
			$captcha_name = $captcha[ $captcha_settings['provider'] ]['name'];
			$captcha_icon = $captcha[ $captcha_settings['provider'] ]['icon'];
		} else {
			$captcha_name = 'CAPTCHA';
			$captcha_icon = 'fa-question-circle-o';
		}

		return [
			'icon'     => $captcha_icon,
			'name'     => $captcha_name,
			'name_en'  => $captcha_name,
			'keywords' => esc_html__( 'captcha, spam, antispam', 'wpforms-lite' ),
			'type'     => 'captcha_' . $captcha_settings['provider'],
			'group'    => 'standard',
			'order'    => 180,
			'class'    => 'not-draggable',
		];
	}

	/**
	 * Get filtered fields data.
	 *
	 * Usage:
	 *      get_filtered( [ 'group' => 'payment' ] )       - fields from the 'payment' group.
	 *      get_filtered( [ 'addon' => 'surveys-polls' ] ) - fields of the addon 'surveys-polls'.
	 *      get_filtered( [ 'type' => 'payment-total' ] )  - field 'payment-total'.
	 *
	 * @since 1.6.6
	 *
	 * @param array $args Arguments array.
	 *
	 * @return array Fields data filtered according to given arguments.
	 */
	private function get_filtered( array $args = [] ): array {

		$default_args = [
			'group' => '',
			'addon' => '',
			'type'  => '',
		];

		$args = array_filter( wp_parse_args( $args, $default_args ) );

		$fields          = $this->get_all();
		$filtered_fields = [];

		foreach ( $args as $prop => $prop_val ) {
			foreach ( $fields as $field ) {
				if ( ! empty( $field[ $prop ] ) && $field[ $prop ] === $prop_val ) {
					$filtered_fields[] = $field;
				}
			}
		}

		return $filtered_fields;
	}

	/**
	 * Get fields by group.
	 *
	 * @since 1.6.6
	 *
	 * @param string $group Fields group (standard, fancy or payment).
	 *
	 * @return array.
	 */
	public function get_by_group( string $group ): array {

		return $this->get_filtered( [ 'group' => $group ] );
	}

	/**
	 * Get fields by addon.
	 *
	 * @since 1.6.6
	 *
	 * @param string $addon Addon slug.
	 *
	 * @return array.
	 */
	public function get_by_addon( string $addon ): array {

		return $this->get_filtered( [ 'addon' => $addon ] );
	}

	/**
	 * Get field by type.
	 *
	 * @since 1.6.6
	 *
	 * @param string $type Field type.
	 *
	 * @return array Single field data. Empty array if field is not available.
	 */
	public function get_field( string $type ): array {

		$fields = $this->get_filtered( [ 'type' => $type ] );

		return ! empty( $fields[0] ) ? $fields[0] : [];
	}

	/**
	 * Set key value of each field (conditionally).
	 *
	 * @since 1.6.6
	 *
	 * @param array  $fields    Fields data.
	 * @param string $key       Key.
	 * @param string $value     Value.
	 * @param string $condition Condition.
	 *
	 * @return array Updated field data.
	 */
	public function set_values( array $fields, string $key, string $value, string $condition ): array {

		if ( empty( $fields ) || empty( $key ) ) {
			return $fields;
		}

		foreach ( $fields as $f => $field ) {

			switch ( $condition ) {
				case 'empty':
					$fields[ $f ][ $key ] = empty( $field[ $key ] ) ? $value : $field[ $key ];
					break;

				default:
					$fields[ $f ][ $key ] = $value;
			}
		}

		return $fields;
	}
}
