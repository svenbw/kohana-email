Kohana Email module
===================

Kohana 3.3 and Koseven compatible email module using SwiftMailer or PHPMailer.

## How to install

### Direct download method
1. Download to modules directory.
2. Fetch dependencies:
```
composer install
```
3. Include it in `APPPATH/bootstrap.php` modules list:
```php
Kohana::modules(array(
	...
	'email' => MODPATH.'email',
	...
));
```

### Composer module method 
1. Include with composer:
```
composer require svenbw/kohana-email:dev-master
```
2. Enable vendor autoload in `APPPATH/bootstrap.php` if not already:
```php
require DOCROOT.'/vendor/autoload.php';
```
3. In the same file include it in your modules list:
```php
Kohana::modules(array(
	...
	'email' => DOCROOT.'/vendor/svenbw/kohana-email',
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
