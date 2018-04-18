<?php
/**
 * Kohana Email abstraction module for Swiftmailer
 *
 * @uses       Swiftmailer (v4.1)
 * @package    Core
 * @author     Kohana Team
 * @author     Lieuwe Jan Eilander
 * @copyright  (c) 2007-2010 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
abstract class Kohana_Email {

  /**
   * @var  $array  Configuration
   */
  protected $_config = NULL;

  /**
   * @var  Swift_mailer  Email transport object
   */
  protected $_mailer;

  /**
   * @var  Email_Recipient  List with receipients
   */
  protected $_recipients = array();

  /**
   * @var  Email_Recipient  Sender email (and name);
   */
  protected $_from = NULL;

  /**
   * @var  string  Subject of the message
   */
  protected $_subject = '';

  /**
   * @var  string  HTML body of the message
   */
  protected $_html = NULL;

  /**
   * @var  string  Plain text body of the message
   */
  protected $_plain = NULL;

  /**
   * @var  string  Char set to use for the message
   */
  protected $_charset = 'utf-8';

  /**
   * @var  array  Headers
   */
  protected $_headers = array();

  /**
   * @var  array  Attachments
   */
  protected $_attachments = array();

  /**
   * @var  Email  Email instance
   */
  protected static $instance;

  /**
   * Create an Email instance.
   *
   * @param   string  $subject Email subject
   * @param   string  $body Email body
   * @param   string  $mime_type Body mime type
   *
   * @return  Email
   */
  public static function factory($subject = NULL, $body = NULL, $mime_type = NULL)
  {
    $config = Kohana::$config->load('email');

    // Set the mailer class name
    $mailer = 'Email_'.ucfirst(strtolower(Arr::get($config, 'mailer', 'swiftmailer')));

    return new $mailer($subject, $body, $mime_type);
  }

  /**
   * Create the email instance (compatiblity with config)
   *
   * @return  Email
   */
  public static function connect($config = NULL)
  {
    if ($config === NULL)
    {
      $config = Kohana::$config->load('email');
    }
    
    // Set the mailer class name
    $mailer = 'Email_'.ucfirst(strtolower(Arr::get($config, 'mailer', 'swiftmailer')));

    return new $mailer($subject, $body, $mime_type, $config);
  }

  /**
   * Creates an Email.
   *
   * @param   string  $subject Email subject
   * @param   string  $body Email body
   * @param   string  $mime_type Body mime type
   *
   * @return  void
   */
  public function __construct($subject = NULL, $body = NULL, $mime_type = NULL, $config = NULL)
  {
    if ($config === NULL)
    {
      $this->_config = Kohana::$config->load('email');
    }
    else
    {
      $this->_config = $config;
    }

    if ( ! isset($this->_config['driver']))
    {
      throw new Kohana_Exception('Email driver not defined in configuration file');
    }

    $options = Arr::get($this->_config, 'options', array());

    switch (Arr::get($this->_config, 'driver', ''))
    {
      case 'smtp':
        $this->config_smtp($options);
        break;

      case 'sendmail':
        $this->config_sendmail($options);
        break;

      default:
        $this->config_native($options);
        break;
    }

    $this->charset(Kohana::$charset);

    if ($subject !== NULL)
    {
      $this->subject($subject);
    }

    if ($body !== NULL)
    {
      $this->message($body, $mime_type);
    }
  }

  abstract protected function config_smtp($options = array());
  abstract protected function config_sendmail($options = array());
  abstract protected function config_native($options = array());
  
  /**
   * Specifies the addresses of the intended recipients.
   *
   * @param   string  $email Email of recipient
   * @param   string  $name Name of the recipient
   *
   * @return  $this
   */
  public function from($email, $name = NULL, $type = 'from')
  {
    $this->_from = new Kohana_Email_Recipient($email, $name, 'from');

    return $this;
  }

  /**
   * Specifies the addresses of the intended recipients.
   *
   * @param   string  $email Email of recipient
   * @param   string  $name Name of the recipient
   * @param   string  $type Recipient type (to, cc, bcc, ...)
   *
   * @return  $this
   */
  public function to($email, $name = NULL, $type = 'to')
  {
    $this->_recipients[] = new Kohana_Email_Recipient($email, $name, $type);

    return $this;
  }
  
  /**
   * Specifies the addresses of recipients who will be copied in on the message
   *
   * @param   string  $email Email of recipient
   * @param   string  $name Name of the recipient
   *
   * @return  $this
   */
  public function cc($email, $name = NULL)
  {
    $this->_recipients[] = new Kohana_Email_Recipient($email, $name, 'cc');

    return $this;
  }

  /**
   * Specifies the addresses of recipients who the message will be blind-copied to.
   * Other recipients will not be aware of these copies.
   *
   * @param   string  $email Email of recipient
   * @param   string  $name Name of the recipient
   *
   * @return  $this
   */
  public function bcc($email, $name = NULL)
  {
    $this->_recipients[] = new Kohana_Email_Recipient($email, $name, 'bcc');

    return $this;
  }

  /**
   * Specifies the address where replies are sent to.
   *
   * @param   string  $email Email of recipient
   * @param   string  $name Name of the recipient
   *
   * @return  $this
   */
  public function reply_to($email, $name = NULL)
  {
    $this->_recipients[] = new Kohana_Email_Recipient($email, $name, 'reply-to');

    return $this;
  }

  /**
   * Subject of the message.
   *
   * @param   mixed  $subject Subject of the message or nothing for current subject
   *
   * @return  $this or string when reading the subject
   */
  public function subject($subject = NULL)
  {
    if ($subject === NULL)
    {
      return $this->_subject;
    }
    
    $this->_subject = $subject;

    return $this;
  }

  /**
   * HTML body of the message.
   *
   * @param   mixed  $body Body of the message or nothing for current body
   *
   * @return  $this or string when reading the body
   */
  public function html($body = NULL)
  {
    if ($body === NULL)
    {
      return $this->_html;
    }
    
    $this->_html = $body;

    return $this;
  }

  /**
   * Plain text body of the message.
   * The body can be a string or a boolean: a boolean will result in a 
   * plain text version that is based on the HTML body
   *
   * @param   mixed  $body Body of the message or nothing for current body
   *
   * @return  $this or string when reading the body
   */
  public function plain($body = NULL)
  {
    if ($body === NULL)
    {
      return $this->_plain;
    }
    
    $this->_plain = $body;

    return $this;
  }

  /**
   * Set the message body. Multiple bodies with different types can be added
   * by calling this method multiple times. Every email is required to have
   * a "text/plain" message body.
   *
   * @param   string  new message body
   * @param   string  mime type: text/html, etc
   * @return  Email
   */
  public function message($body, $type = NULL)
  {
    if (( ! $type) OR ($type === 'text/plain'))
    {
      // Set the main text/plain body
      $this->_plain = $body;
    }
    else
    {
      $this->_html = $body;
    }

    return $this;
  }

  /**
   * Add an additional header to the email
   *
   * @param   string  $key Header key
   * @param   string  $value Header value
   *
   * @return  $this
   */
  public function add_header($key, $value)
  {
    // Silently replace spaces to '-'
    $key = str_replace(' ', '-', $key);
    
    $this->_headers[$key] = $value;

    return $this;
  }

  /**
   * Attaches an file to the email
   *
   * @param   string  $file Filename of the file
   * @param   string  $name Name of the file
   *
   * @return  $this
   */
  public function attach_content($file, $name = NULL, $mime = NULL)
  {
    $this->_attachments[] = new Email_Attachment($file, $name, $mime);

    return $this;
  }

  /**
   * Register a new plugin
   *
   * @param   string  $plugin SwiftMailer plugin
   *
   * @return  $this
   */
  public function register_plugin($plugin)
  {
    //TODO: $this->_mailer->registerPlugin($plugin);
    
    return $this;
  }
  
  /**
   * Sets the charset to use
   *
   * @param   string  $charset Charset
   *
   * @return  $this
   */
  public function charset($charset)
  {
    $this->_charset = $charset;

    return $this;
  }
  
  /**
   * Gets the 'to' based on whitelist and force_to
   *
   * @return mixed Recipient
   */
  private function get_to($set)
  {
    $force_to = Arr::get($this->_config, 'force_to' , FALSE);

    if ($force_to === FALSE)
      return $set;

    $whitelist = Arr::get($this->_config, 'whitelist', FALSE);
    if (is_array($whitelist))
    {
      if ( ! in_array($set, $whitelist))
        return $force_to;
    }
    else
    {
      return $force_to;
    }
    
    return $set;
  }

  /**
   * Send the message
   *
   * @return integer number of messages sent
   */
  abstract public function send_message();

  /**
   * Send an email message.
   *
   * @param   mixed         $to         Recipient email (and name), or an array of To, Cc, Bcc names
   * @param   mixed         $from       Sender email (and name)
   * @param   string        $subject    Message subject
   * @param   string        $body       Message body
   * @param   boolean       $html       Send email as HTML
   * @param   string        $attachment Message attachment
   * @return  integer       Number of emails sent
   * @throws  Http_Exception_408  If connecting to the mailserver is timed-out
   */
  public function send($to = FALSE, $from = FALSE, $subject = FALSE, $body = FALSE, $html = FALSE, $attachment = FALSE)
  {
    // If to is FALSE, send with given data
    if ($to !== FALSE)
    {
      // Set the body
      if ($html === TRUE)
      {
        $this->html($body);
      }
      else
      {
        $this->plain($body);
      }

      $this->subject($subject);
      
      // Recipients
      if (is_string($to))
      {
        // Single recipient
        $this->to($this->get_to($to));
      }
      elseif (is_array($to))
      {
        if (isset($to[0]) AND isset($to[1]))
        {
          //To: address and name
          $to = array('to' => $to);
        }

        foreach ($to as $method => $set)
        {
          if ( ! in_array($method, array('to', 'cc', 'bcc', 'reply-to'), TRUE))
          {
            // Use To: by default
            $method = 'to';
          }

          // Create method name
          $method = str_replace('-', '_', $method);
          if (is_array($set))
          {
            // Add a recipient with name
            $this->$method($this->get_to($set[0]), $set[1]);
          }
          else
          {
            // Add a recipient without name
            $this->$method($this->get_to($set));
          }
        }
      }

      if (is_string($from))
      {
        // From without a name
        $this->from($from);
      }
      elseif (is_array($from))
      {
        // From with a name
        $this->from($from[0], $from[1]);
      }

      if (is_string($attachment))
      {
        $this->attach_content($attachment);
      }
      elseif (is_array($attachment))
      {
        $this->attach_content($attachment['file'], $attachment['name']);
      }
    }

    try
    {
      return $this->send_message();
    }
    catch (Swift_SwiftException $e)
    {
      throw $e;
    }
  }
} // End email
