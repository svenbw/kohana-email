<?php

class Kohana_Email_Phpmailer extends Email {

  /**
   * Initialize the mail transport for smtp.
   *
   * @param   array  $options configuration option parameters
   */
  protected function config_smtp($options = array())
  {
    $this->_mailer = new PHPMailer();

    $this->_mailer->isSMTP();
    $this->_mailer->SMTPDebug = 0;
    $this->_mailer->Debugoutput = 'html';

    $this->_mailer->Port = (int) Arr::get($options, 'port', 25);
    $this->_mailer->Host = Arr::get($options, 'hostname', NULL);

    // Encryption
    $encryption = Arr::get($options, 'encryption', '');
    if ( ! empty($encryption))
    {
      $this->_mailer->SMTPSecure = $encryption;
    }

    // Authentication
    $username = Arr::get($options, 'username', '');
    if ( ! empty($username))
    {
      $this->_mailer->SMTPAuth = FALSE;
      $this->_mailer->Username = $username;
    }

    $password = Arr::get($options, 'password', '');
    if ( ! empty($password))
    {
      $this->_mailer->SMTPAuth = FALSE;
      $this->_mailer->Password = $password;
    }

    // Timeout
    $this->_mailer->Timeout = (int) Arr::get($options, 'timeout', 5);
  }

  /**
   * Initialize the mail transport for sendmail.
   *
   * @param   array  $options configuration option parameters
   */
  protected function config_sendmail($options = array())
  {
    $this->_mailer = new PHPMailer();
    $this->_mailer->isSendmail();

    $executable = Arr::get($options, 'executable', '/usr/sbin/sendmail');
    $this->_mailer->Sendmail = $executable;
  }

  /**
   * Initialize the mail transport for php's native mail.
   *
   * @param   array  $options configuration option parameters
   */
  protected function config_native($options = array())
  {
    $this->_mailer = new PHPMailer();
  }
  
  /**
   * Returns the phpmailer mailbox method name
   *
   * @return  string Method name
   */
  protected function add_recipient($recipient)
  {
    $name = ( ! is_string($recipient->name)) ? '' : $recipient->name;

    switch ($recipient->method)
    {
      case 'to':
        return $this->_mailer->addAddress($recipient->email, $name);

      case 'cc':
        return $this->_mailer->addCC($recipient->email, $name);

      case 'bcc':
        return $this->_mailer->addBCC($recipient->email, $name);

      case 'reply-to':
        return $this->_mailer->addReplyTo($recipient->email, $name);

      case 'from':
        return $this->_mailer->setFrom($recipient->email, $name);
    }

    return 'addAddress';
  }

  /**
   * Send the message
   *
   * @return integer number of messages sent
   */
  public function send_message()
  {
    // Add the receipients
    foreach ($this->_recipients as $recipient)
    {
      $this->add_recipient($recipient);
    }

    // Add the from
    if ($this->_from !== NULL)
    {
      $this->add_recipient($this->_from);
    }

    // Set the subject
    $this->_mailer->Subject = $this->_subject;

    // Set the char set
    $this->_mailer->CharSet = $this->_charset;

    // Set the body
    if (is_string($this->_html))
    {
      $this->_mailer->Body = $this->_html;
      $this->_mailer->isHTML(TRUE);

      // Additional text part?
      if (is_string($this->_plain))
      {
        $this->_mailer->AltBody = $this->_plain;
      }
      
      // Create plain text body from html
      else if ($this->_plain === TRUE)
      {
        $body = str_ireplace(array('<br />', '<br>', '<br/>'), '\r\n', $this->_html);
        $body = strip_tags($body);
        
        $this->_mailer->AltBody = $body;
      }
    }
    elseif (is_string($this->_plain))
    {
      $this->_mailer->AltBody = $this->_plain;
    }
    else
    {
      throw new Kohana_Exception('Empty email body');
    }

    // Additional headers
    foreach ($this->_headers as $key => $value)
    {
      $this->_mailer->addCustomHeader($key.': '.$value);
    }

    // Add attachments
    foreach ($this->_attachments as $attachment)
    {
      $filename = is_string($attachment->name) ? $attachment->name : '';
      $mime_type = is_string($attachment->mime_type) ? $attachment->mime_type : '';

      $this->_mailer->addAttachment($attachment->file, $filename, 'base64', $mime_type);
    }

    // Try to send the message
    try
    {
      $result = $this->_mailer->send();
    }
    catch (phpmailerException $ex)
    {
      throw new Email_Exception(':error', array(':error' => $ex->getMessage()), $ex->getCode(), $ex);
    }

    return $result;
  }
}