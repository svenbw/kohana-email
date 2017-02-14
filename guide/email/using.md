# Basic Usage

## Creating Instance

[Email::factory()] creates an instance of the email object. It is possible to pass the configuration as first argument, by default the config file is used.
Refer to the [configure](config) section for more information.

~~~
$email = Email::factory();
~~~

Once an instance is created, you can add recipients, set the body, subject, ...

## Recipients

The number of recipients that can be added is unlimited. There are different types of recipients that can be added, for each method there is one required argument (the email address) and optional the mail.

### To
~~~
$email
  // With name
  ->to('test@example.com', 'Test')

  // Email only
  ->to('test@example.com');
~~~

### CC
~~~
$email
  // With name
  ->cc('test@example.com', 'Test')

  // Email only
  ->cc('test@example.com');
~~~

### BCC
~~~
$email
  // With name
  ->bcc('test@example.com', 'Test')

  // Email only
  ->bcc('test@example.com');
~~~

## Sender

Both sender and reply-to can also be set using a dedicated call.

### From / Sender
~~~
$email
  // With name
  ->from('test@example.com', 'Test')

  // Email only
  ->from('test@example.com');
~~~

### Reply to
~~~
$email
  // With name
  ->reply_to('test@example.com', 'Test')

  // Email only
  ->reply_to('test@example.com');
~~~

## Content

The class supports two email bodies: A plain text body and a formatted HTML body.

### HTML body
~~~
$email
  ->html('<i>Nice content</i>');
~~~

### plain text body
The `plain` call can take a string that will set the content of the plain text body in the message.
~~~
$email
  ->plain('Nice content');
~~~
Calling `plain` with TRUE will generate the text body by removing the tags and replacing `<br/>` to newlines.
~~~
$email
  ->plain(TRUE);
~~~

### Subject
The subject can be set using the subject call
~~~
$email
  ->subject('Sample message');
~~~

## Sending
Sending the message can be done using the `send` call.
~~~
$email
  ->send();
~~~


## Static Call Method

For compatibility reasons it is possible to send a messages using the `Email::send($to, $from, $subject, $message, $html, $attachment)` method.

$to can be any of the following:

*  a single string email address e.g. "test@example.com"
*  an array specifying an email address and a name e.g. `array('test@example.com', 'John Doe')`
*  an array of recipients in either above format, keyed by type e.g. `array('to' => 'test@example.com', 'cc' => array('test2@example.com', 'Jane Doe'), 'bcc' => 'another@example.com')`

$from can be either a string email or array of email and name as above

        $mailer = Email::connect();

        $mailer->send(
          array('to' => 'test@example.com'),
          array('myname@example.com', 'My name'),
          'Hello',
          'Hello world');
