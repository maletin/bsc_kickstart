<?php
/*
basecondition ~ getcss.inc.php
Copyright (C) 2011 ~ Joachim Doerr, hello@basecondition.com
Creative Commons Attribution 3.0 Unported License

Version: 3.1.1
*/

// load lessc class
require_once( 'lessc.inc.php' );

// get_css class
class get_css
{
  /*
    define defaults 
  */
  public $strCopyright = "/*
basecondition ~ {csskey}
Copyright (C) {year} ~ Joachim Doerr, hello@basecondition.com
Creative Commons Attribution 3.0 Unported License
*/\n";
  public $strCompressCopyright = "/* basecondition ~ {csskey} ~ Copyright (C) {year}, Joachim Doerr hello@basecondition.com, Creative Commons Attribution 3.0 Unported License */\n";
  public $strCommentStart = "\n\n/* ----------------------------------------------------- ~ ";
  public $strCommentEnd = " */\n\n";
  public $strNormalizeLizenz = "\n/*! normalize.css v2.0.1 | MIT License | git.io/normalize */\n";
  public $strNormalizeUrl = 'https://raw.github.com/necolas/normalize.css/master/normalize.css';
  public $strCacheFolder = 'files/';
  public $strLessFolder = '../';
  public $strCacheFileName = NULL;
  public $strLessFileName = NULL;
  public $intLastModificationTime = NULL;
  public $boolNormalizeReload = false;
  public $boolMinimalizer = false;
  public $boolFileIsCss = false;
  public $boolFileIsNormalize = false;
  public $boolCacheFileGenerate = false;
  
  
  /*
   check last modification function
  */
  public function file_check_modification_time ( $strFile, $strTimeUpTo = 3600 )
  {
    if ($strTimeUpTo == 0)
    {
      if (file_exists($strFile))
      {
        return filemtime($strFile);
      }
      else
      {
        return 0;
      }
    }
    else
    {
      return (!file_exists($strFile) or (time() - filemtime($strFile)) > $strTimeUpTo) ? true : false;
    }
  }
  
  
  /*
   change detection function
  */
  public function file_cache_change_detection  ( )
  {
    $strLessFileModificationTime = $this->file_check_modification_time($this->strLessFolder . $this->strLessFileName, 0);
    $strCacheFileModificationTime = $this->file_check_modification_time($this->strCacheFolder . $this->strCacheFileName, 0);
    
    $this->intLastModificationTime = ($strLessFileModificationTime > mktime(0,0,0,21,5,1980)) ? $strLessFileModificationTime : mktime(0,0,0,21,5,1980);
    
    if ($strCacheFileModificationTime > 0)
    {
      if ($strCacheFileModificationTime < $strLessFileModificationTime)
      {
        $this->boolCacheFileGenerate = true;
        $this->intLastModificationTime = $strCacheFileModificationTime;
      }
      else
      {
        $this->boolCacheFileGenerate = false;
      }
    }
    else
    {
      $this->boolCacheFileGenerate = true;
    }
  }
  
  
  /*
   file name geneartion function
  */
  public function file_name_generation ( $strFile )
  {
    $arrFile = explode('/', $strFile);
    $arrFileName = explode('.', $arrFile[sizeof($arrFile)-1]);
    
    $this->strCacheFileName = 'cache_' . $strFile;
    $this->strLessFileName = str_replace(array('.min','.css'), array('','.less'), $strFile);
    
    foreach ($arrFileName as $strFileNamePart)
    {
      switch ($strFileNamePart)
      {
        case 'normalize' :
          $this->boolFileIsNormalize = true;
          if (file_exists($this->strLessFolder . $this->strLessFileName))
          {
            if ($this->file_check_modification_time($this->strLessFolder . $this->strLessFileName) === true && $this->boolNormalizeReload === true)
            {
              $this->strLessFileName = '';
            }
          }
          else
          {
            $this->strLessFileName = '';
          }
          break;
        case 'min' :
          $boolFileHasMin = true;
          $this->boolMinimalizer = true;
          break;
          
        case 'css' :
          $this->boolFileIsCss = true;
          break;
      }
    }
  }
  
  
  /*
   curl download function
  */
  public function process_curl_download( $strUrl )
  {
    $arrOptions = array(
      CURLOPT_URL             => $strUrl,
      CURLOPT_HEADER          => 0,
      CURLOPT_RETURNTRANSFER  => 1
    );
    
    $objCurl = curl_init($strUrl);
    curl_setopt_array($objCurl, $arrOptions);
    
    // get the result from url
    $strResult = curl_exec($objCurl);
    if (curl_exec($objCurl) === false)
    {
      $strResult = curl_error($objCurl);
    }
    curl_close($objCurl);
    return $strResult;
  }
  
  
  /*
   generate normalize less file function
  */
  public function generate_normalize_less ()
  {
    $strData = str_replace(array('/* =','= */','="','=',": ","\n\n","}",'^"'), array('<!!','!!>','^"','',':','',"}\n",'="'), $this->process_curl_download($this->strNormalizeUrl));
    $strData = preg_replace("#/\*[^*]*\*+(?:[^/*][^*]*\*+)*/#msx", '', $strData);
    $strData = str_replace(array("<!!\n   ","!!>","\n    */","\n/*",'    '), array($this->strCommentStart,$this->strCommentEnd," */","/*",'  '), $strData);
    
    $strNormalizeOutput = str_replace(array('{csskey}','{year}'), array('normalize.less',date(Y)), $this->strCopyright).$this->strNormalizeLizenz;
    $strNormalizeOutput .= $strData;
    
    if (file_put_contents($this->strLessFolder . 'normalize.less', $strNormalizeOutput))
    {
      return true;
    }
    else
    {
      return false;
    }
  }
  
  
  /*
   minimalizer function
  */
  public function process_minimalize ($strBuffer)
  {
    /* remove comments */
    $strBuffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $strBuffer);
    /* remove tabs, spaces, newlines, etc. */
    $strBuffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $strBuffer);
    return $strBuffer;
  }
  
  
  /*
   file parser function
  */
  public function parse_file ( $strFile )
  {
    $this->file_name_generation($strFile);
    
    if ($this->boolFileIsCss === true)
    {
      if ($this->boolFileIsNormalize === true && $this->strLessFileName == '')
      {
        // generate normalize lessfile
        if ($this->generate_normalize_less() === true)
        {
          $this->strLessFileName = 'normalize.less';
        }
      }
      
      $this->file_cache_change_detection();
      
      if($this->boolCacheFileGenerate === true)
      {
        // init less object
        $objLess = new lessc($this->strLessFolder . $this->strLessFileName);
        
        // check compression name
        if ($this->boolMinimalizer === true)
        {
          file_put_contents($this->strCacheFolder . $this->strCacheFileName, str_replace(array('{csskey}','{year}'), array(str_replace('cache_', '', $this->strCacheFileName),date('Y')), $this->strCompressCopyright) . $this->process_minimalize($objLess->parse()));
        }
        else
        {
          file_put_contents($this->strCacheFolder . $this->strCacheFileName, str_replace(array('{csskey}','{year}'), array(str_replace('cache_', '', $this->strCacheFileName),date('Y')), $this->strCopyright) . $objLess->parse());
        }
      }
      
    }
  }
  
  
  /*
   get parsed file
  */
  public function get_parsed_file ( $strFile )
  {
    // init process
    $this->parse_file($strFile);
    
    // check gzhandler
    if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip'))
    {
      ob_start('ob_gzhandler');
    }
    else
    {
      ob_start();
    }
    if (!$this->boolCacheFileGenerate && isset($_SERVER['If-Modified-Since']) && strtotime($_SERVER['If-Modified-Since']) >= $this->intLastModificationTime)
    {
      header('HTTP/1.0 304 Not Modified');
    }
    else
    {
      header('Content-type: text/css');
      header('Last-Modified: ' . gmdate("D, d M Y H:i:s",$this->intLastModificationTime) . " GMT");
      
      return file_get_contents($this->strCacheFolder . $this->strCacheFileName);
    }
  }
}