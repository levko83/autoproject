<?php
/**
 * This class can be used to connect to a FTP server and recursively list all 
 * available files and folders on that server. The crawling result can later be 
 * exported in CSV, HTML or XML format.
 * 
 * @package ftpcrawler
 */
/**
 * FTPCrawler
 *
 * @author windylea <www.windylea.com>
 * @copyright Copyright (c) 2012, WindyLea. All right reserved
 * @version 0.1
 */
class Ftpcrawler
{
    /**
     * The FTP server to connect to. Can be a full URL like
     * ftp://user:pass@example.com:21/folder/subfolder/
     *
     * @access public
     * @var string
     */

    public $server;

    /**
     * The connecting timeout value in seconds. Default is 90
     *
     * @access public
     * @var int
     */
    public $timeout = 90;

    /**
     * Turn secure SSL-FTP connection on or off. Default is FALSE
     *
     * @access public
     * @var boolean
     */
    public $ssl = false;

    /**
     * Turn passive mode on or off. Default is FALSE
     *
     * @access public
     * @var boolean
     */
    public $passive = true;

    /**
     * The maximum nested depth of directories to which the crawler will go. 
     * Default is 0, that is the crawler will try to list all the available 
     * files/folders on that server
     *
     * @access public
     * @var int
     */
    public $level = 0;

    /**
     * The FTP stream
     *
     * @access protected
     * @var resource
     */
    protected $connection;

    /**
     * Current operating system of the FTP server
     *
     * @access protected
     * @var resource
     */
    protected $os;

    /**
     * An associative array contains list of files/folders of the specified 
     * FTP server. The array key is the full path to this file/folder and the 
     * array value is the information (may varies depending on server OS)
     *
     * @access protected
     * @var array
     */
    protected $list = array();

    /**
     * File type indicator for UNIX FTP server
     *
     * @access protected
     * @var array
     */
    protected $types = array("-" => "file", "d" => "directory", "l" => "simlink");

    /**
     * File date (month) indicator for UNIX FTP server
     *
     * @access protected
     * @var array
     */
    protected $months = array(
        "Jan" => 1, "Feb" => 2, "Mar" => 3, "Apr" => 4, 
        "May" => 5, "Jun" => 6, "Jul" => 7, "Aug" => 8, 
        "Sep" => 9, "Oct" => 10, "Nov" => 11, "Dec" => 12
    );

    /**
     * Logging information
     *
     * @access protected
     * @var array
     */
    protected $log = array();

    /**
     * Class constructor
     *
     * @access public
     * @author windylea
     */
    public function __construct()
    {
        if (!extension_loaded("ftp"))
        {
            throw new Exception("Fatal error: FTP extension is not loaded!");
        }
    }

    /**
     * Opens an FTP connection to the FTP server
     *
     * @access protected
     * @author windylea
     * @return string Returns a new parsed URL of the FTP server
     */
    protected function connect()
    {
        if (strtolower(substr($this->server, 0, 6)) != "ftp://")
        {
            $this->server = "ftp://" . $this->server;
        }

        $ftp_connect = "ftp_connect";
        $parts = parse_url($this->server);
        $port = 21;
        $username = "anonymous";
        $password = "guest";
        $timeout = (is_int($this->timeout)) ? $this->timeout : 90; 
        $passive = ($this->passive !== false) ? true : false;

        if (isset($parts["port"]))
        {
            $port = $parts["port"];
            if ($parts["port"] == "990" || $this->ssl !== false)
            {
                $ftp_connect = "ftp_ssl_connect";
            } elseif($parts["port"] == "21")
            {
                unset($parts["port"]);
            }
        }

        if (isset($parts["user"]) && $parts["pass"])
        {
            if ($parts["user"] == "anonymous")
            {
                unset($parts["user"], $parts["pass"]);
            } else
            {
                $username = $parts["user"];
                $password = $parts["pass"];
            }
        }

        $message = "Information: Connecting and logging in...";
        $this->log($message);

        $this->connection = $ftp_connect($parts["host"], $port, $timeout);
        if($this->connection)
        {
            $login = ftp_login($this->connection, $username, $password);
        }

        if ((!$this->connection) || (!$login))
        {
            $message = "Fatal error: FTP connection has failed! "
                . "Attempted to connect to '" . $parts["host"]
                . "' on port $port for user '" . $username . "'";
            $this->log($message);
            throw new Exception($message);
        }

        ftp_pasv($this->connection, $passive); 

        if (isset($parts["path"]))
        {
            if(!ftp_chdir($this->connection, $parts["path"]))
            {
                $message = "Fatal error: Unable to change working directory";
                $this->log($message);
                throw new Exception($message);
            }
        }

        $this->os = ftp_systype($this->connection);
        $message = "Information: Successfully connected to '" . $parts["host"]
                . "' on port $port for user '" . $username
                . "'. FTP server's OS is '" . $this->os . "'";
        $this->log($message);

        $url = "ftp://" .
            (isset($parts["user"]) ? $parts["user"] . ":" : "") . 
            (isset($parts["pass"]) ? $parts["pass"] . "@" : "") . 
            (isset($parts["host"]) ? $parts["host"] : "") . 
            (isset($parts["port"]) ? ":" . $parts["port"] : "");

        return $url;
    }
    
    public function savefile($local_file=null, $server_file=null){

    	$this->connect();
    	if (!ftp_get($this->connection, $local_file, $server_file, FTP_BINARY)){
    		echo "There was a problem while download\n";
    		exit();
    	}
    	ftp_close($this->connection);
    	
    	return basename($local_file);
    } 

    /**
     * Recursively lists all files/folders within a path
     *
     * @access protected
     * @author windylea
     * @param string $path Current path for scanning
     * @param string $depth Current depth of $path. If $depth is equal to 
        $level, then this path's sub-folders will not be scanned
     */
    protected function rawlist($path, $depth = 0)
    {
        if (!ftp_chdir($this->connection, $path))
        {
            $message = "Warning: Unable to change working directory to \"$path\"";
            $this->log($message);
            return false;
        }

        $buffer = ftp_rawlist($this->connection, "-A");

        $depth += 1;
        if(!empty($buffer))
        {
            /*
             * Parse file/folder information on UNIX FTP server
             */
            if ($this->os == "UNIX")
            {
                foreach($buffer as $line)
                {
                    $parts = preg_split("/[\s]+/", $line, 9);

                    $hour = $minute = 0;
                    $year = $parts[7];
                    if (strpos($parts[7], ":"))
                    {
                        list($hour, $minute) = explode(":", $parts[7]);
                        $year = date("Y");
                    }

                    $info = array(
// 						"permissions" => self::convert_perm($parts[0]), 
// 						"children" => $parts[1], 
// 						"owner" => $parts[2], 
// 						"usergroup" => $parts[3], 
						"size" => self::convert_size($parts[4]), 
						"date" => date("D, d M Y H:i:s", mktime($hour, $minute, 0, $this->months[$parts[5]], $parts[6], $year)), 
						"name" => $parts[8],
						"path" => $path . "/" . $parts[8],
						"type" => $this->types[$parts[0]{0}], 
// 						"raw" => $line,
						"Поставить в обработку" => "",
                    );

                    if ($info["type"] == "directory")
                    {
                        $info["path"] .= "/";
                        if (($this->level == 0) || ($this->level > 0 && $this->level > $depth))
                        {
                            $this->rawlist($info["path"], $depth);
                        }
                    }

                    $this->list[self::path_encode($info["path"])] = $info;
                }
            } else
            {
            /*
             * Parse file/folder information on WINDOWS FTP server
             */
                foreach($buffer as $line)
                {
                    $parts = preg_split("/[\s]+/", $line, 4);
                    list($month, $day, $year) = explode("-", $parts[0]);

                    sscanf($parts[1], "%2d:%2d%s", $hour, $minute, $suffix);
                    if ($suffix == "PM")
                    {
                        $hour += 12;
                    }

                    $info = array(
                        "size" => ($parts[2] == "<DIR>") ? "0" : self::convert_size($parts[2]), 
                        "date" => date("D, d M Y H:i:s", mktime($hour, $minute, 0, $month, $day, $year)), 
                        "name" => $parts[3], 
                        "path" => $path . $parts[3], 
                        "type" => ($parts[2] == "<DIR>") ? "directory" : "file", 
                        "raw" => $line
                    );

                    if ($info["type"] == "directory")
                    {
                        $info["path"] .= "/";
                        if (($this->level == 0) || ($this->level > 0 && $this->level > $depth))
                        {
                            $this->rawlist($info["path"], $depth);
                        }
                    }

                    $this->list[self::path_encode($info["path"])] = $info;
                }
            }
        }

        $message = "Information: Found " . count($buffer) . " files and folders in \"$path\"";
        $this->log($message);

        return;
    }

    /**
     * Write/print logging information
     *
     * @access public
     * @author windylea
     * @param array $message Logging message. If this argument is not set then 
        the entire logging information will be returned
     * @return array|string
     */
    public function log($message = null)
    {
        if($message)
        {
            $this->log[] = array(time(), $message);
            return true;
        }

        return $this->log;
    }

    /**
     * Crawls and returns the result
     *
     * @access public
     * @author windylea
     * @param string $format Can be "csv" or "html" or NULL (Default)
     * @return array|string
     */
    public function crawl($format = null)
    {
        $this->list = array();
        $url = $this->connect();

        $cwd = ftp_pwd($this->connection);
        $message = "Information: Started crawling under '" . $cwd . "'";
        $this->log($message);
        $this->rawlist($cwd, 0);

        $message = "Information: Crawling done. Connection to server is now closed.";
        $this->log($message);
        ftp_close($this->connection);

        if(empty($this->list))
        {
            return null;
        }

        /*
         * Export the result to specified format. The array keys of the first 
         * element of $this->list will be used as output header
         */
        $headers = array_keys(reset($this->list));

        switch(strtolower($format))
        {
            case "csv":
                $output = "url," . implode($headers, ",") . "\r\n";

                foreach($this->list as $key => $e)
                {
                    $output .= "\"" . $url . $key . "\"";
                    foreach ($e as $value)
                    {
                        $value = ",\"" . str_replace('"', '""', $value) . "\"";
                        $output .= $value;
                    }
                    $output .= "\r\n";
                }
                break;

            case "html":
                $output = "<table class=\"details-list ftpcrawler\"><thead><tr><th>" . implode($headers, "</th><th>") . "</th></thead><tbody>\r\n";

                foreach($this->list as $key => $e)
                {
                    $output .= "<tr>";
                    foreach ($e as $header => $value)
                    {
                        if($header == "name") {
							$string = "<td><a href='" . $url . $key . "'>" . $value . "</a></td>";
                        } elseif ($header == 'Поставить в обработку') {
                        	$string = "<td><a href='?do=".(isset($_GET['do'])?$_GET['do']:0)."&save_file=".$key."'>Поставить в обработку файл ".$key."</a></td>";
                        } else {
                            $string = "<td>" . str_replace('"', '""', $value) . "</td>";
                        }

                        $output .= $string;
                    }

                    $output .= "</tr>\r\n";
                }
                $output .= "</tbody></table>";
                break;

            case "xml":
                $output = "<?xml version=\"1.0\"?>\r\n";
                $output .= "<server path=\"$url\">\r\n";
                foreach($this->list as $key => $e)
                {
                    $output .= "<" . $e["type"] . " path=\"" . $url . $key . "\">\r\n";
                    foreach($e as $header => $value)
                    {
                        switch($header)
                        {
                            case "name":
                            case "path":
                            case "raw":
                                $output .= "<$header><![CDATA[$value]]></$header>\r\n";
                                break;
                            default:
                                $output .= "<$header>$value</$header>\r\n";
                        }
                    }
                    $output .= "</" . $e["type"] . ">\r\n";
                }
                $output .= "</server>";
                break;

            default:
                return $this->list;
        }

        return $output;
    }

    /**
     * Encodes file path (skips forward slashes)
     *
     * @access public
     * @author windylea
     * @param string $string The string to be encoded
     * @return string
     * @static
     */
    public static function path_encode($string)
    {
        return implode("/", array_map("rawurlencode", explode("/", $string)));
    }

    /**
     * Convert UNIX files/folders permissions to octal format
     *
     * @access public
     * @author paul maybe at squirrel mail org
     * @link http://www.php.net/manual/it/function.ftp-rawlist.php#82752
     * @param string $string The string to be converted
     * @return string
     * @static
     */
    public static function convert_perm($string)
    {
        $mode = 0;
 
        if ($string[1] == "r") $mode += 0400;
        if ($string[2] == "w") $mode += 0200;
        if ($string[3] == "x") $mode += 0100;
        else if ($string[3] == "s") $mode += 04100;
        else if ($string[3] == "S") $mode += 04000;

        if ($string[4] == "r") $mode += 040;
        if ($string[5] == "w") $mode += 020;
        if ($string[6] == "x") $mode += 010;
        else if ($string[6] == "s") $mode += 02010;
        else if ($string[6] == "S") $mode += 02000;

        if ($string[7] == "r") $mode += 04;
        if ($string[8] == "w") $mode += 02;
        if ($string[9] == "x") $mode += 01;
        else if ($string[9] == "t") $mode += 01001;
        else if ($string[9] == "T") $mode += 01000;
 
        return sprintf("%04o", $mode); 
    }

    /**
     * Convert number of bytes to human-readable format
     *
     * @access public
     * @param int $bytes The number of bytes to be converted
     * @return string
     * @static
     */
    public static function convert_size($bytes)
    {
        $size = array("bytes", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB");
        return $bytes ? round($bytes / pow(1024, ($i = floor(log($bytes, 1024)))), 2) 
            . " " . $size[$i] : "0 bytes";
    }
}
?>