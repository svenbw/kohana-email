Kohana Email module
===================

Kohana 3.3 email module using SwiftMailer or PHPMailer

## How to install
Before using the email module, we must enable it first on `APPPATH/bootstrap.php`:
```php
Kohana::modules(array(
	...
	'email' => MODPATH.'email',
	...
));
```

## Usage
Send a message to a recipient
```php
$mailer = Email::connect();
$mailer->send(
    array('to-recipient@example.com', 'To recipient'),
    array('the-sender@example.com', 'The sender'),
    'Test-email',
    '<i>Test email</i>',
    TRUE);
```

## Advanced usage
It is possible to create a message with chaining calls.
```php
$mailer = Email::factory();
$mailer
  ->to('to-recipient@example.com', 'To recipient')
  ->from('the-sender@example.com', 'The sender')
  ->subject('Test-email')
  ->html('<i>Test email body</i>')
  ->send();
```

