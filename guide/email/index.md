# Email

The email module allows sending of messages using SwiftMailer or PHPMailer. 

This module offers an interface that is matches the interface of other Kohana modules that supported sending email.

## Getting Started

Before using the email module, we must enable it first on `APPPATH/bootstrap.php`:

~~~
Kohana::modules(array(
    ...
    'email' => MODPATH.'email',  // Sending email
    ...
));
~~~

Next: [Using the module](using).
