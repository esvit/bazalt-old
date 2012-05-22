<?php
/**
 * BAZALT Framework
 *
 * PHP versions 5
 *
 * LICENSE:
 *
 * This library is free software; you can redistribute it
 * and/or modify it under the terms of the GNU Lesser General
 * Public License as published by the Free Software Foundation;
 * either version 2.1 of the License, or (at your option) any
 * later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @category System
 * @package  Error
 * @author   Vitalii Savchuk <esvit666@gmail.com>
 * @author   Alex Slubsky <aslubsky@gmail.com>
 * @license  http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version  SVN: $Id: errorcatcher.class.inc 148 2010-11-16 08:57:09Z esvit $
 * @revision SVN: $Revision: 148 $
 * @link     http://bazalt.org.ua/
 */

if (!defined('E_DEPRECATED')) {
    define('E_DEPRECATED', 8192);
}

if (!defined('E_USER_DEPRECATED')) {
    define('E_USER_DEPRECATED', 16384);
}

/**
 * Handle logging errors in production mode
 *
 * @todo     add some documentation
 *
 * @category System
 * @package  ORM
 * @author   Vitalii Savchuk <esvit666@gmail.com>
 * @author   Alex Slubsky <aslubsky@gmail.com>
 * @license  http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link     http://bazalt.org.ua/
 */
class Error_Catcher
{
    /**
     * Web format output
     */
    const WEB_FORMAT = 'web';

    /**
     * Console format output
     */
    const CONSOLE_FORMAT = 'console';

    /**
     * Current format output
     */
    protected static $format = self::WEB_FORMAT;

    protected $prevExceptionHandler = null;

    protected $prevErrorHandler = null;

    protected static $instance = null;

    public static function getInstance()
    {
        if (self::$instance == null) {
            $class = __CLASS__;
            self::$instance = new $class();
        }
        return self::$instance;
    }

    /**
     * Init error reporting
     *
     * @return void
     */
    public function init()
    {
        if (CLI_MODE) {
            self::$format = self::CONSOLE_FORMAT;
        }

        ini_set('error_prepend_string', '<phpfatalerror>');
        ini_set('error_append_string', '</phpfatalerror>');

        if (!CLI_MODE) {
            ob_start(array($this, 'onFatalError'));
        }

        $this->prevExceptionHandler = set_exception_handler(array($this, 'onException'));
        $this->prevErrorHandler = set_error_handler(array($this, 'onError'));
    }

    public function stopCatch()
    {
        ob_end_flush();

        set_exception_handler($this->prevExceptionHandler);
        set_error_handler($this->prevErrorHandler);
    }

    /**
     * Get lines from file
     *
     * @param string $file File
     * @param int    $line Line number
     *
     * @return string Lines from file
     */
    public static function getFileLineForDebug($file, $line)
    {
        $content = file_get_contents($file);
        $content = explode("\n", $content);
        $start = $line - 6;
        $end = $line + 5;
        if ($start < 0) {
            $start = 0;
        }
        if ($end > count($content)) {
            $end = count($content);
        }

        $cont = '';
        for ($i = $start; $i < $end; $i++) {
            if ($i == $line - 1) {
                $cont .= '<span style="background-color: #CCC">';
            }
            $cont .= ($i + 1) . '|   ' . htmlentities(rtrim($content[$i]));
            if ($i == $line - 1) {
                $cont .= '</span>';
            }
            $cont .= '<br />';
        }
        return $cont;
    }

    /**
     * Set output format
     *
     * @param string $format WEB_FORMAT or CONSOLE_FORMAT
     *
     * @return void
     */
    public function setFormat($format)
    {
        self::$format = $format;
    }

    /**
     * Call when generated exception
     *
     * @param Exception $exception Exception
     *
     * @return void
     */
    public function onException($exception)
    {
        header('HTTP/1.0 500 Internal Server Error');
        error_log($exception->getMessage());
        if (class_exists('Logger')) {
            Logger::getInstance()->err($exception->getMessage(), Logger::ERROR);
        }

        die($this->getFormatError($exception));
    }

    public function onFatalError($content)
    {
        $matches = array();

        if (!ini_get('display_errors') && empty($content) && error_get_last()) {
            header('HTTP/1.0 500 Internal Server Error');

            return $this->showFatalError(null, null);
        }
        if (preg_match('#<phpfatalerror>(.*)</phpfatalerror>#s', $content, $matches)) {
            header('HTTP/1.0 500 Internal Server Error');

            $data = preg_replace('#<phpfatalerror>(.*)</phpfatalerror>#s', '', $content);
            $error = str_replace('Fatal error: ', '', strip_tags($matches[1]));
            return $this->showFatalError($error, $data);
        }
        return $content;
    }

    protected function showFatalError($error, $content)
    {
        if (preg_match('#.* in (.*) on line (\d+)#s', $error, $matches)) {
            $file = $matches[1];
            $line = intval($matches[2]);
            $errorText = '<pre>' . str_replace(' in ' . $file . ' on line ' . $line, '', $error) . '</pre>';
            $error = '<p><strong>File:</strong> ' . $file . ' : ' . $line;
            if ($line != '0') {
                $error .= '<pre style="background-color: #EEEEEE">' . self::getFileLineForDebug($file, $line) . '</pre>';
            }
            $error .= trim($errorText);
        }
        $out  = '<div style="padding: 10px">';
        $out .= '<strong style="color: red;">FATAL ERROR:</strong>';
        $out .= $error;

        $content = htmlentities($content, ENT_QUOTES, 'UTF-8');
        if (!empty($content)) {
            $out .= '<p>Output: </p><pre>';
            $out .= $content;
            $out .= '</pre>';
        }
        $out .= '</div>';
        return $out;
    }

    /**
     * Get output format for exception
     *
     * @param Exception $exception Exception
     *
     * @return string Output text
     */
    public function getFormatError($exception, $content = null)
    {
        $_GLOBAL['exception'] = $exception;
        ob_start();

        switch(self::$format) {
        case self::WEB_FORMAT:
            include_once 'templates/web.php';
            break;
        case self::CONSOLE_FORMAT:
            include_once 'templates/console.php';
            break;
        default:
            return __CLASS__ . ': Unknown format "' . self::$format . '"';
        }
        $data = ob_get_contents();
        ob_end_clean();
        return $data;
    }

    public static function renderTrace(Exception $exception)
    {
        $_GLOBAL['trace'] = $exception->getTrace();
        ob_start();
        switch(self::$format) {
        case self::WEB_FORMAT:
            include 'templates/trace.php';
            break;
        case self::CONSOLE_FORMAT:
            include 'templates/trace.php';
            break;
        default:
            return __CLASS__ . ': Unknown format "' . self::$format . '"';
        }
        $data = ob_get_contents();
        ob_end_clean();
        return $data;
    }

    /**
     * A function to directly log errors
     *
     * @param int    $errno   The error number
     * @param string $errstr  The error description
     * @param string $errfile The file where the error occured
     * @param int    $errline The line of the file where the error occured
     *
     * @return bool Success
     */
    public function onError($errno, $errstr, $errfile, $errline)
    {
        // Ignore suppressed errors
        if (!($errno & error_reporting())) {
            return;
        }
        if (!class_exists('Logger')) {
            return true;
        }

        // What type of error
        $logLevel = Logger::INFO;
        $errorText = 'Info';
        switch ($errno) {
        case E_PARSE:
        case E_ERROR:
        case E_CORE_ERROR:
        case E_COMPILE_ERROR:
        case E_USER_ERROR:
            $errorText = 'Error';
            $logLevel = Logger::ERROR;
            break;
        case E_WARNING:
        case E_STRICT:
        case E_USER_WARNING:
        case E_COMPILE_WARNING:
        case E_RECOVERABLE_ERROR:
            $errorText = 'Warning';
            $logLevel = Logger::WARN;
            break;
        case E_NOTICE:
        case E_USER_NOTICE:
        case E_DEPRECATED:
        case E_USER_DEPRECATED:
            $errorText = 'Notice';
            $logLevel = Logger::NOTICE;
            break;
        default:
            $errorText = 'Critical error';
            $logLevel = Logger::CRITICAL;
            break;
        }
        $error = sprintf('%s (%d): %s in [%s, line %d]', $errorText, $errno, $errstr, $errfile, $errline);

        Logger::getInstance()->log($error, $logLevel);
        
        return true;
    }
}