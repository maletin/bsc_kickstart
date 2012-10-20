<?php
/*
basecondition ~ getfile.php
Copyright (C) 2011 ~ Joachim Doerr, hello@basecondition.com
Creative Commons Attribution 3.0 Unported License

Version: 3.1.1
*/


function isCssExist ($strFileName)
{
  $arrFileName = explode ('.', $strFileName);
  $strLessFileName = str_replace ( array ($arrFileName[sizeof($arrFileName)-1],'.min'), array ('less',''), $strFileName);
  
  if (isFileExist ($strFileName, false) === true)
  {
    echo file_get_contents ('../'.$_REQUEST['file']);
    return false;
  }
  if (isFileExist ($strLessFileName, true) === true)
  {
    return true;
  }
}

function isFileExist ($strFileName, $boolSet404 = false)
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

if ($_REQUEST['file'] != '')
{
  $arrFileName = explode ('.', $_REQUEST['file']);
  
  if ($arrFileName[sizeof($arrFileName)-1] == 'css')
  {
    if (isCssExist ($_REQUEST['file']) === true)
    {
      // include core class
      require_once( "classes/getcss.inc.php" );
      
      // init css object
      $objCSS = new get_css();
      
      // get output
      echo $objCSS->get_parsed_file ($_REQUEST['file']);
    }
  }
  else
  {
    if (isFileExist ($_REQUEST['file'], true) === true)
    {
      echo file_get_contents ('../'.$_REQUEST['file']);
    }
  }
}