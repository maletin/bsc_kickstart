<?php
/*
basecondition ~ get_it.inc.php
Copyright (C) 2011 ~ Joachim Doerr, hello@basecondition.com
Creative Commons Attribution 3.0 Unported License

Version: 3.1.2
*/

class get_file
{
  /*
   is css file exist
  */
  public function is_css_exist ($strFileName)
  {
    $arrFileName = explode ('.', $strFileName);
    $strLessFileName = str_replace ( array ($arrFileName[sizeof($arrFileName)-1],'.min'), array ('less',''), $strFileName);
    
    if ($this->is_file_exist ($strFileName, false) === true)
    {
      echo file_get_contents ('../'.$_REQUEST['file']);
      return false;
    }
    if ($this->is_file_exist ($strLessFileName, true) === true)
    {
      return true;
    }
  }
  
  
  /*
   is file exist
  */
  public function is_file_exist ($strFileName, $boolSet404 = false)
  {
    if (file_exists ('../'.$strFileName))
    {
      return true;
    }
    else
    {
      if ($boolSet404 === true)
      {
        header ('HTTP/1.0 404 Not Found');
      }
      return false;
    }
  }
  
  
  /*
   get file
  */
  public function get_it ($strFile)
  {
    $arrFileName = explode ('.', $strFile);
    
    if ($arrFileName[sizeof($arrFileName)-1] == 'css')
    {
      if ($this->is_css_exist ($strFile) === true)
      {
        // include core class
        require_once( "classes/getcss.inc.php" );
        
        // init css object
        $objCSS = new get_css();
        
        // get output
        echo $objCSS->get_parsed_file ($strFile);
      }
    }
    else
    {
      if ($this->is_file_exist ($strFile, true) === true)
      {
        // return file
        echo file_get_contents ('../' . $strFile);
      }
    }
  }
}