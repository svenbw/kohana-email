<?php

class Kohana_Email_Recipient {

  /**
   * @var  string  Recipient email
   */
  public $email;
  
  /**
   * @var  string  Recipient name
   */
  public $name;
  
  /**
   * @var  string  Method used for sending
   */
  public $method;
  
	/**
	 * Creates a new recipient
	 *
	 * @return  void
	 */
	public function __construct($email, $name = NULL, $method = 'to')
	{
    $this->email = $email;
    $this->name = $name;
    
    switch (strtolower($method))
    {
      case 'to':
      case 'cc':
      case 'bcc':
        $this->method = $method;
        break;

      case 'from':
      case 'reply-to':
        $this->method = $method;
        break;

      default:
        throw new Kohana_Exception('Unknown method :method', array(':method' => $method));
    }

    $this->method = $method;
  }
}