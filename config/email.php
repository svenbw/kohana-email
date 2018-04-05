<?php
return array(
	/**
	 * Mailer type
	 *
	 * Valid drivers are: SwiftMailer, PHPMailer
	 */
  'mailer' => 'SwiftMailer',
	
	/**
	 * Email driver
	 *
	 * Valid drivers are: native, sendmail, smtp
	 */
	'driver' => '',
	
	/**
	 * Driver options
   *
	 * @param   null    native: no options
	 * @param   string  sendmail: executable path, with -bs or equivalent attached
	 * @param   array   smtp: hostname, (username), (password), (port), (encryption)
	 */
	'options' => NULL,
  
	/**
	 * Force to: Send all email to
	 */
   'force_to' => NULL,

	/**
	 * Whitelist: array with emails of recipients which can receive mail
	 */
   'whitelist' => NULL
);