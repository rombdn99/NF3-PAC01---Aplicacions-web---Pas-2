<?php
class fileLoggerBackend{
  public function __construct($url) {
    echo "aaa";
    Config::addConfig('LOGGER_FILE', 'log/prova.log');
    //echo "aA";
    Config::addConfig('LOGGER_LEVEL', Escribir::INFO);
    //echo "AAAA";
    $log = Escribir::getInstance();

    if(isset($_GET['fooid'])) {
      //not written to the log - the log level is too high
      $log->logMessage('A fooid is present', Escribir::DEBUG);
       //LOG_INFO is the default so this would get printed
      $log->logMessage('The value of fooid is ' .  $_GET['fooid']);
    } else {
      //This will also be written, and includes a module name
      $log->logMessage('No fooid supplied', Escribir::CRITICAL, "Foo Module");

      throw new Exception('No foo id!');
    }
  }

}
class Escribir {
  private $hLogFile;
  private $logLevel;
  //Log Levels.  The higher the number, the less severe the message
  //Gaps are left in the numbering to allow for other levels
  //to be added later
  const DEBUG     = 100;
  const INFO      = 75;
  const NOTICE    = 50;
  const WARNING   = 25;
  const ERROR     = 10;
  const CRITICAL  = 5;
  
  //Note: private constructor.  Class uses the singleton pattern
  private function __construct() {
    //This is pseudo code that fetches a hash of configuration information
    //Implementation of this is left to the reader, but should hopefully
    //be quite straight-forward.
    $cfg = Config::getConfig();  
    /* If the config establishes a level, use that level,
       otherwise, default to INFO
    */
    $this->logLevel = isset($cfg['LOGGER_LEVEL']) ? 
    $cfg['LOGGER_LEVEL'] : 
    Escribir::INFO;
    //We must specify a log file in the config
    if(! ( isset($cfg['LOGGER_FILE']) && strlen($cfg['LOGGER_FILE'])) ) {
      throw new Exception('No log file path was specified ' .
      'in the system configuration.');
    }

    $logFilePath = $cfg['LOGGER_FILE'];

    //Open a handle to the log file.  Suppress PHP error messages.
    //We'll deal with those ourselves by throwing an exception.
    $this->hLogFile = @fopen($logFilePath, 'a+');

    if(! is_resource($this->hLogFile)) {
      throw new Exception("The specified log file $logFilePath " .
      'could not be opened or created for ' .
      'writing.  Check file permissions.');
    }

    //Set encoding type to ISO-8859-1
    //stream_encoding($this->hLogFile, 'iso-8859-1');
  }

  public function __destruct() {
    if(is_resource($this->hLogFile)) {
      fclose($this->hLogFile);
    }
  }

  public static function getInstance() {

    static $objLog;

    if(!isset($objLog)) {
      $objLog = new Escribir();
    }

    return $objLog;
  }

  public function logMessage($msg, $logLevel = Escribir::INFO, $module = null) {

    if($logLevel > $this->logLevel) {
      return;
    }

    /* If you haven't specifed your timezone using the 
       date.timezone value in php.ini, be sure to include
       a line like the following.  This can be omitted otherwise.
    */
    date_default_timezone_set('America/New_York');

    $time = strftime('%x %X', time());
    $msg = str_replace("\t", '    ', $msg);
    $msg = str_replace("\n", ' ', $msg);

    $strLogLevel = $this->levelToString($logLevel);

    if(isset($module)) {
      $module = str_replace("\t", '    ', $module);
      $module = str_replace("\n", ' ', $module);
    }

      //logs: date/time loglevel message modulename
      //separated by tabs, new line delimited
    $logLine = "$time\t$strLogLevel\t$msg\t$module\r\n";
    fwrite($this->hLogFile, $logLine);
  }

  public static function levelToString($logLevel) {
    switch ($logLevel) {
      case Escribir::DEBUG:
      return 'Escribir::DEBUG';
      break;
      case Escribir::INFO:
      return 'Escribir::INFO';
      break;
      case Escribir::NOTICE:
      return 'Escribir::NOTICE';
      break;
      case Escribir::WARNING:
      return 'Escribir::WARNING';
      break;
      case Escribir::ERROR:
      return 'Escribir::ERROR';
      break;
      case Escribir::CRITICAL:
      return 'Escribir::CRITICAL';
      break;
      default:
      return '[unknown]';
    }
  }
}

  ?>
