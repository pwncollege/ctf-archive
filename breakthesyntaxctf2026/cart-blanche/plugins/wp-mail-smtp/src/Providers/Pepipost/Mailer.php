<?php

namespace WPMailSMTP\Providers\Pepipost;

use WPMailSMTP\Providers\MailerAbstract;

/**
 * Class Mailer inherits everything from parent abstract class.
 * This file is required for a proper work of Loader and \ReflectionClass.
 *
 * @package WPMailSMTP\Providers\Pepipost
 */
class Mailer extends MailerAbstract {

	/**
	 * @inheritdoc
	 */
	public function is_mailer_complete() {

		$options = $this->options->get_group( $this->mailer );

		// Host and Port are the only really required options.
		if (
			! empty( $options['host'] ) &&
			! empty( $options['port'] )
		) {
			return true;
		}

		return false;
	}
}
