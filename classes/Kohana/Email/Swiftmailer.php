<?php

class Kohana_Email_Swiftmailer extends Email {

  /**
   * Initialize the mail transport for smtp.
   *
   * @param   array  $options configuration option parameters
   */
  protected function config_smtp($options = array())
  {
    $port = (int) Arr::get($options, 'port', 25);
    $hostname = Arr::get($options, 'hostname', NULL);

    $transport = Swift_SmtpTransport::newInstance($hostname, $port);

    // Encryption
    $encryption = Arr::get($options, 'encryption', '');
    if ( ! empty($encryption))
    {
      $transport->setEncryption($encryption);
    }

    // Authentication
    $username = Arr::get($options, 'username', '');
    $password = Arr::get($options, 'password', '');

    if ( ! empty($username))
    {
      $transport->setUsername($username);
    }
    if ( ! empty($password))
    {
      $transport->setPassword($password);
    }

    // Timeout
    $timeout = (int) Arr::get($options, 'timeout', 5);
    $transport->setTimeout($timeout);
    
    $this->_mailer = Swift_Mailer::newInstance($transport);
  }

  /**
   * Initialize the mail transport for sendmail.
   *
   * @param   array  $options configuration option parameters
   */
  protected function config_sendmail($options = array())
  {
    $executable = Arr::get($options, 'executable', '/usr/sbin/sendmail');
    $parameters = trim(Arr::get($options, 'parameters', 'bs'));

    if ($parameters[0] != '-')
    {
      $parameters = '-'.$parameters;
    }
    
    $this->_mailer = Swift_SendmailTransport::newInstance($executable.' '.$parameters);
  }
  
  /**
   * Initialize the mail transport for php's native mail.
   *
   * @param   array  $options configuration option parameters
   */
  protected function config_native($options = array())
  {
    $this->_mailer = Swift_MailTransport::newInstance();
  }

  /**
   * Returns the swiftmailer mailbox method name
   *
   * @return  string Method name
   */
  protected function add_recipient( & $message, $recipient)
  {
    $name = ( ! is_string($recipient->name)) ? NULL : $recipient->name;

    switch ($recipient->method)
    {
      case 'to':
        return $message->addTo($recipient->email, $name);

      case 'cc':
        return $message->addCc($recipient->email, $name);

      case 'bcc':
        return $message->addBcc($recipient->email, $name);

      case 'reply-to':
        return $message->addReplyto($recipient->email, $name);

      case 'from':
        return $message->setFrom($recipient->email, $name);
    }

    return $message->addTo($recipient->email, $name);
  }

  /**
   * Send the message
   *
   * @return integer number of messages sent
   */
  public function send_message()
  {
    // Create the message
    $message = Swift_Message::newInstance();

    // Add the receipients
    foreach ($this->_recipients as $recipient)
    {
      $this->add_recipient($message, $recipient);
    }

    // Add the from
    if ($this->_from !== NULL)
    {
      $this->add_recipient($message, $this->_from);
    }

    // Set the subject
    $message->setSubject($this->_subject);

    // Set the body
    if (is_string($this->_html))
    {
      $message->setBody($this->_html, 'text/html', $this->_charset);

      // Additional text part?
      if (is_string($this->_plain))
      {
        $message->addPart($this->_plain, 'text/plain', $this->_charset);
      }

      // Create plain text body from html
      else if ($this->_plain === TRUE)
      {
        $body = str_ireplace(array('<br />', '<br>', '<br/>'), '\r\n', $this->_html);
        $body = strip_tags($body);
 
        $message->addPart($body, 'text/plain', $this->_charset);
      }
    }
    elseif (is_string($this->_plain))
    {
      $message->setBody($this->_plain, 'text/plain', $this->_charset);
    }
    else
    {
      throw new Kohana_Exception('Empty email body');
    }

    // Additional headers
    $headers = $message->getHeaders();
    foreach ($this->_headers as $key => $value)
    {
      $headers->addIdHeader($key, $value);
    }
    
    // Add attachments
    foreach ($this->_attachments as $attachment)
    {
      $switch_attachment = Swift_Attachment::fromPath($attachment->file);

      // Set the name (if available)
      if (is_string($attachment->name))
      {
        $switch_attachment->setFilename($attachment->name);
      }

      // Set the mime type (if available)
      if (is_string($attachment->mime_type))
      {
        $switch_attachment->setContentType($attachment->mime_type);
      }

      $message->attach($switch_attachment);
    }

    // Try to send the message
    try
    {
      $result = $this->_mailer->send($message);
    }
    catch (Swift_SwiftException $ex)
    {
      throw new Email_Exception(':error', array(':error' => $ex->getMessage()), $ex->getCode(), $ex);
    }

    return $result;
  }
}