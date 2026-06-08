<?php

namespace WPForms\Admin\Base\Tables\DataObjects;

/**
 * Column data object base class.
 *
 * @since 1.8.6
 */
abstract class ColumnBase {

	/**
	 * Column ID.
	 *
	 * @since 1.8.6
	 *
	 * @var string|int
	 */
	protected $id;

	/**
	 * Column label.
	 *
	 * @since 1.8.6
	 *
	 * @var string
	 */
	protected $label;

	/**
	 * Label HTML markup.
	 *
	 * @since 1.8.6
	 *
	 * @var string
	 */
	protected $label_html;

	/**
	 * Is column draggable.
	 *
	 * @since 1.8.6
	 *
	 * @var bool
	 */
	protected $is_draggable;

	/**
	 * Column type.
	 *
	 * @since 1.8.6
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * Is column readonly.
	 *
	 * @since 1.8.6
	 *
	 * @var bool
	 */
	protected $readonly;

	/**
	 * Column constructor.
	 *
	 * @since 1.8.6
	 *
	 * @param int|string $id       Column ID.
	 * @param array      $settings Column settings.
	 */
	public function __construct( $id, array $settings ) {

		$this->id           = $id;
		$this->label        = $settings['label'] ?? '';
		$this->label_html   = empty( $settings['label_html'] ) ? $this->label : $settings['label_html'];
		$this->is_draggable = $settings['draggable'] ?? true;
		$this->type         = empty( $settings['type'] ) ? $id : $settings['type'];
		$this->readonly     = $settings['readonly'] ?? false;
	}

	/**
	 * Get column ID.
	 *
	 * @since 1.8.6
	 *
	 * @return string|int
	 */
	public function get_id() {

		return $this->id;
	}

	/**
	 * Get column label.
	 *
	 * @since 1.8.6
	 *
	 * @return string
	 */
	public function get_label(): string {

		return $this->label;
	}

	/**
	 * Get column label HTML.
	 *
	 * @since 1.8.6
	 *
	 * @return string
	 */
	public function get_label_html(): string {

		return $this->label_html;
	}

	/**
	 * Get the column type.
	 *
	 * @since 1.8.6
	 *
	 * @return string
	 */
	public function get_type(): string {

		return $this->type;
	}

	/**
	 * Is column draggable.
	 *
	 * @since 1.8.6
	 *
	 * @return bool
	 */
	public function is_draggable(): bool {

		return $this->is_draggable;
	}

	/**
	 * Is column readonly.
	 *
	 * @since 1.8.6
	 *
	 * @return bool
	 */
	public function is_readonly(): bool {

		return $this->readonly;
	}
}
