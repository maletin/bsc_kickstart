<?php
/*
basecondition ~ getfile.php
Copyright (C) 2011 ~ Joachim Doerr, hello@basecondition.com
Creative Commons Attribution 3.0 Unported License

Version: 3.1.2
*/

if ($_REQUEST['file'] != '')
{
  // include getfile class
  require_once( "classes/getfile.inc.php" );
  
  // init object
  $objFile = new get_file();
  
  // get output
  echo $objFile->get_it ($_REQUEST['file']);
}
