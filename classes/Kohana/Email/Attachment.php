<?php

class Kohana_Email_Attachment {

  /**
   * @var  string  Attachment file
   */
  public $file;

  /**
   * @var  string  Attachment filename
   */
  public $name;

  /**
   * @var  string  Attachment mime type
   */
  public $mime_type;

	/**
	 * Creates a new attachment
	 *
	 * @return  void
	 */
	public function __construct($file, $name = NULL, $mime_type = NULL)
	{
    if ( ! file_exists($file) OR ! is_readable($file))
    {
      throw new Kohana_Exception('Cannot attach file :file (not found of not readable)', array(':file' => $file));
    }

    $this->file = $file;
    $this->name = $name;
    
    // Get the mime type from the filename
    if ( ! $mime_type)
    {
      $this->mime_type = File::mime_by_ext(pathinfo($file, PATHINFO_EXTENSION));
    }
    else
    {
      $this->mime_type = $mime_type;
    }
  }
}