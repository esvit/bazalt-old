<?php
/**
 * Logger, get idea from Kohana and Doo framework
 *
 * @category  Core
 * @package   Core
 * @copyright 2010 Equalteam
 * @license   GPLv3
 * @version   SVN: $Revision: 178 $
 * @link      http://bazalt-cms.com/
 *
 * PHP Version 5
 */

namespace Framework\Core;

if (!defined('DISABLE_LOG')) {
    define('DISABLE_LOG', !DEBUG);
}

if (!defined('BAZALT_START_TIME')) {
    define('BAZALT_START_TIME', microtime(true));
}

/**
 * Define the memory usage at the start of the application, used for profiling.
 */
if (!defined('BAZALT_START_MEMORY')) {
    define('BAZALT_START_MEMORY', memory_get_usage());
}

define ('DEFAULT_LOGGER_CATEGORY', 'application');

class Logger
{
    /**
     * Critical - critical conditions
     */
    const CRITICAL = 1;

    /**
     * Error - error conditions
     */
    const ERROR    = 2;

    /**
     * Warning - warning conditions
     */
    const WARN     = 3;

    /**
     * Notice - normal but significant condition
     */
    const NOTICE   = 4;

    /**
     * Informational - informational messages
     */
    const INFO     = 5;

    /**
     * @var  integer   maximium number of application stats to keep
     */
    public static $rollover = 1000;
    
    /**
     * Singleton flag
     *
     * @var boolean
     */
    protected $isSingleton = false;

    /**
     * @var  array  collected benchmarks
     */
    protected static $_marks = array();

    private static $_logs = array();

    protected static $instance = null;

    /**
     * Unique request id
     */
    protected static $requestUniqueId = null;

    protected $category = DEFAULT_LOGGER_CATEGORY;
    
    protected function getSavePath()
    {
        $fileName = sprintf('runtime_%s.log', date('Y.m.d'));
        if (!defined('TEMP_DIR')) {
            return dirname(__FILE__).'/../../tmp/logs/'.$fileName;
        } else {
            return TEMP_DIR . '/logs/'.$fileName;
        }
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Logger();
            self::$instance->isSingleton = true;
        }
        return self::$instance;
    }

    public function __construct($category = DEFAULT_LOGGER_CATEGORY)
    {
        $this->category = $category;
    }

    public function startLog()
    {
        if(DISABLE_LOG) {
            return;
        }
        if (isset($_SERVER['UNIQUE_ID'])) {
            self::$requestUniqueId = $_SERVER['UNIQUE_ID'];
        }
        if (empty(self::$requestUniqueId)) {
            self::$requestUniqueId = Helper\Guid::newGuid();
        }

        if (CLI_MODE) {
            $content  = '/**' . "\n";
            $content .= ' *         ID: ' . self::$requestUniqueId . "\n";
            $content .= ' * CLI PARAMS: ' . http_build_query($_SERVER['argv']) . "\n";
            $content .= ' */ ' . "\n";
        } else {
            $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'Unknown';
            $params = ($method == 'POST') ? $_POST : $_GET;

            $content  = '/**' . "\n";
            $content .= ' *         ID: ' . self::$requestUniqueId . "\n";
            $content .= sprintf(' * %10s: %s', $method, (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '')) . "\n";
            $content .= ' *     PARAMS: ' . http_build_query($params) . "\n";
            $content .= ' *    FROM IP: ' . Helper\Url::getRemoteIp() . "\n";
            $content .= ' * USER AGENT: ' . (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Empty') . "\n";
            $content .= ' *     COOKIE: ' . http_build_query($_COOKIE) . "\n";
            $content .= ' */ ' . "\n";
        }

        file_put_contents($this->getSavePath(), $content, FILE_APPEND);
    }

    /**
     * Logs a message.
     * Messages logged by this method may be retrieved back via {@link getLogs}.
     * @param string $msg
     * @param int $level default DooLog::TRACE
     * @param string $category
     */
    public function log($msg, $level = self::INFO, $category = null)
    {
        if(DISABLE_LOG) {
            return;
        }
        if ($category === null) {
            $category = $this->category;
        }
        self::$_logs[] = array($msg, $level, $category, microtime(true));

        //if ($level == self::INFO) return;
        $msg = self::formatLog($msg, $level, $category, time());
        file_put_contents($this->getSavePath(), $msg, FILE_APPEND);
    }
    
    /**
     * Get log messages.
     *
     * <p>Log messages can be filtered by levels and/or categories.
     * A level filter is specified by a list of levels or a single level
     * A category filter is specified in the same way.</p>
     *
     * <p>If you do not specify level filter, it will bring back logs at all levels.
     * The same applies to category filter.</p>
     * 
     * @param int level filter
     * @param string category filter
     * @return array list of messages. Each array element represents one message
     * with the structure:
     * <code>
     * array(
     *   [0] => message (string)
     *   [1] => level (int)
     *   [2] => category (string)
     *   [3] => timestamp (float, microtime(true)
     * );
     * </code>
     */
    // public function getLogs($levels = null, $categories = null)
    // {
        // if (empty($levels) && empty($categories)) {
            // return $this->_logs;
        // }
    // }

    /**
     * Shorthand for logging messages with level Crtical
     * @param string $msg
     * @param string $category 
     */
    public function crit($msg, $category = null)
    {
        $this->log($msg, self::CRITICAL, $category);
    }

    /**
     * Shorthand for logging messages with level Error
     * @param string $msg
     * @param string $category
     */
    public function err($msg, $category = null)
    {
        $this->log($msg, self::ERROR, $category);
    }

    /**
     * Shorthand for logging messages with level Warning
     * @param string $msg
     * @param string $category
     */
    public function warn($msg, $category = null)
    {
        $this->log($msg, self::WARN, $category);
    }

    /**
     * Shorthand for logging messages with level Notice
     * @param string $msg
     * @param string $category
     */
    public function notice($msg, $category = null)
    {
        $this->log($msg, self::NOTICE, $category);
    }

    /**
     * Shorthand for logging messages with level Info
     * @param string $msg
     * @param string $category
     */
    public function info($msg, $category = null)
    {
        $this->log($msg, self::INFO, $category);
    }

    /**
     * Starts a new benchmark and returns a unique token. The returned token
     * _must_ be used when stopping the benchmark.
     *
     *     $token = Logger::start('test', 'Logger');
     *
     * @param   string  group name
     * @param   string  benchmark name
     * @return  string
     */
    public static function start($group, $name)
    {
        static $counter = 0;

        // Create a unique token based on the counter
        $token = 'bzl/' . base_convert($counter++, 10, 32);

        self::$_marks[$token] = array(
            'group' => $group,
            'name'  => (string) $name,

            // Start the benchmark
            'start_time'   => microtime(TRUE),
            'start_memory' => memory_get_usage(),

            // Set the stop keys without values
            'stop_time'    => FALSE,
            'stop_memory'  => FALSE,
        );

        return $token;
    }

    /**
     * Stops a benchmark.
     *
     *     Logger::stop($token);
     *
     * @param   string  token
     * @return  void
     */
    public static function stop($token)
    {
        // Stop the benchmark
        self::$_marks[$token]['stop_time']   = microtime(TRUE);
        self::$_marks[$token]['stop_memory'] = memory_get_usage();
    }

    /**
     * Deletes a benchmark. If an error occurs during the benchmark, it is
     * recommended to delete the benchmark to prevent statistics from being
     * adversely affected.
     *
     *     Logger::delete($token);
     *
     * @param   string  token
     * @return  void
     */
    public static function delete($token)
    {
        // Remove the benchmark
        unset(self::$_marks[$token]);
    }

    /**
     * Returns all the benchmark tokens by group and name as an array.
     *
     *     $groups = Logger::groups();
     *
     * @return  array
     */
    public static function groups()
    {
        $groups = array();

        foreach (self::$_marks as $token => $mark)
        {
            // Sort the tokens by the group and name
            $groups[$mark['group']][$mark['name']][] = $token;
        }

        return $groups;
    }

    /**
     * Gets the min, max, average and total of a set of tokens as an array.
     *
     *     $stats = Logger::stats($tokens);
     *
     * @param   array  Logger tokens
     * @return  array  min, max, average, total
     * @uses    Logger::total
     */
    public static function stats(array $tokens)
    {
        // Min and max are unknown by default
        $min = $max = array(
            'time' => NULL,
            'memory' => NULL);

        // Total values are always integers
        $total = array(
            'time' => 0,
            'memory' => 0);

        foreach ($tokens as $token)
        {
            // Get the total time and memory for this benchmark
            list($time, $memory) = self::total($token);

            if ($max['time'] === NULL OR $time > $max['time']) {
                // Set the maximum time
                $max['time'] = $time;
            }

            if ($min['time'] === NULL OR $time < $min['time']) {
                // Set the minimum time
                $min['time'] = $time;
            }

            // Increase the total time
            $total['time'] += $time;

            if ($max['memory'] === NULL OR $memory > $max['memory']) {
                // Set the maximum memory
                $max['memory'] = $memory;
            }

            if ($min['memory'] === NULL OR $memory < $min['memory']) {
                // Set the minimum memory
                $min['memory'] = $memory;
            }

            // Increase the total memory
            $total['memory'] += $memory;
        }

        // Determine the number of tokens
        $count = count($tokens);

        // Determine the averages
        $average = array(
            'time' => $total['time'] / $count,
            'memory' => $total['memory'] / $count
        );

        return array(
            'min' => $min,
            'max' => $max,
            'total' => $total,
            'average' => $average
        );
    }

    /**
     * Gets the min, max, average and total of Logger groups as an array.
     *
     *     $stats = Logger::group_stats('test');
     *
     * @param   mixed  single group name string, or array with group names; all groups by default
     * @return  array  min, max, average, total
     * @uses    Logger::groups
     * @uses    Logger::stats
     */
    public static function group_stats($groups = NULL)
    {
        // Which groups do we need to calculate stats for?
        $groups = ($groups === NULL)
            ? self::groups()
            : array_intersect_key(Logger::groups(), array_flip( (array) $groups));

        // All statistics
        $stats = array();

        foreach ($groups as $group => $names) {
            foreach ($names as $name => $tokens) {
                // Store the stats for each subgroup.
                // We only need the values for "total".
                $_stats = self::stats($tokens);
                $stats[$group][$name] = $_stats['total'];
            }
        }

        // Group stats
        $groups = array();

        foreach ($stats as $group => $names) {
            // Min and max are unknown by default
            $groups[$group]['min'] = $groups[$group]['max'] = array(
                'time' => NULL,
                'memory' => NULL);

            // Total values are always integers
            $groups[$group]['total'] = array(
                'time' => 0,
                'memory' => 0);

            foreach ($names as $total) {
                if ( ! isset($groups[$group]['min']['time']) OR $groups[$group]['min']['time'] > $total['time']) {
                    // Set the minimum time
                    $groups[$group]['min']['time'] = $total['time'];
                }
                if ( ! isset($groups[$group]['min']['memory']) OR $groups[$group]['min']['memory'] > $total['memory']) {
                    // Set the minimum memory
                    $groups[$group]['min']['memory'] = $total['memory'];
                }

                if ( ! isset($groups[$group]['max']['time']) OR $groups[$group]['max']['time'] < $total['time']) {
                    // Set the maximum time
                    $groups[$group]['max']['time'] = $total['time'];
                }
                if ( ! isset($groups[$group]['max']['memory']) OR $groups[$group]['max']['memory'] < $total['memory']) {
                    // Set the maximum memory
                    $groups[$group]['max']['memory'] = $total['memory'];
                }

                // Increase the total time and memory
                $groups[$group]['total']['time']   += $total['time'];
                $groups[$group]['total']['memory'] += $total['memory'];
            }

            // Determine the number of names (subgroups)
            $count = count($names);

            // Determine the averages
            $groups[$group]['average']['time']   = $groups[$group]['total']['time'] / $count;
            $groups[$group]['average']['memory'] = $groups[$group]['total']['memory'] / $count;
        }

        return $groups;
    }

    /**
     * Gets the total execution time and memory usage of a benchmark as a list.
     *
     *     list($time, $memory) = Logger::total($token);
     *
     * @param   string  token
     * @return  array   execution time, memory
     */
    public static function total($token)
    {
        // Import the benchmark data
        $mark = self::$_marks[$token];

        if ($mark['stop_time'] === false) {
            // The benchmark has not been stopped yet
            $mark['stop_time']   = microtime(true);
            $mark['stop_memory'] = memory_get_usage();
        }

        return array(
            // Total time in seconds
            $mark['stop_time'] - $mark['start_time'],

            // Amount of memory in bytes
            $mark['stop_memory'] - $mark['start_memory'],
        );
    }

    /**
     * Gets the total application run time and memory usage. Caches the result
     * so that it can be compared between requests.
     *
     *     list($time, $memory) = Logger::application();
     *
     * @return  array  execution time, memory
     * @uses    Kohana::cache
     */
    public static function application($app = null)
    {
        if (empty($app)) {
            $app = 'default';
        }
        $cacheKey = 'Logger_application_stats_' . $app;
        // Load the stats from cache, which is valid for 1 day
        $stats = Cache::Singleton()->getCache($cacheKey);

        if (!is_array($stats) OR $stats['count'] > self::$rollover) {
            // Initialize the stats array
            $stats = array(
                'min'   => array('time' => NULL, 'memory' => NULL),
                'max'   => array('time' => NULL, 'memory' => NULL),
                'total' => array('time' => NULL, 'memory' => NULL),
                'count' => 0
            );
        }

        // Get the application run time
        $time = microtime(true) - BAZALT_START_TIME;

        // Get the total memory usage
        $memory = memory_get_usage() - BAZALT_START_MEMORY;

        // Calculate max time
        if ($stats['max']['time'] === NULL OR $time > $stats['max']['time']) {
            $stats['max']['time'] = $time;
        }

        // Calculate min time
        if ($stats['min']['time'] === NULL OR $time < $stats['min']['time']) {
            $stats['min']['time'] = $time;
        }

        // Add to total time
        $stats['total']['time'] += $time;

        // Calculate max memory
        if ($stats['max']['memory'] === NULL OR $memory > $stats['max']['memory']) {
            $stats['max']['memory'] = $memory;
        }

        // Calculate min memory
        if ($stats['min']['memory'] === NULL OR $memory < $stats['min']['memory']) {
            $stats['min']['memory'] = $memory;
        }

        // Add to total memory
        $stats['total']['memory'] += $memory;

        // Another mark has been added to the stats
        $stats['count']++;

        // Determine the averages
        $stats['average'] = array(
            'time'   => $stats['total']['time'] / $stats['count'],
            'memory' => $stats['total']['memory'] / $stats['count']
        );

        // Cache the new stats
        Cache::Singleton()->setCache($cacheKey, $stats, 3600 * 24);

        // Set the current application execution time and memory
        // Do NOT cache these, they are specific to the current request only
        $stats['current']['time']   = $time;
        $stats['current']['memory'] = $memory;

        // Return the total application run time and memory usage
        return $stats;
    }

    /**
     * Format a single log message
     * Example formatted message:
     * <code>2009-6-22 15:21:30 [INFO (6)] [application] User johnny has logined from 60.30.142.85</code>
     *
     * @param string $msg Log message
     * @param int $level Log level
     * @param string $category
     * @param float $time Time used in second
     *
     * @return string A formatted log message
     */
    protected static function formatLog($msg, $level, $category, $time)
    {
        $sLevel = '';
        switch ($level) {
            case self::CRITICAL: $sLevel = 'CRITICAL'; break;
            case self::ERROR:    $sLevel = 'ERROR';    break;
            case self::WARN:     $sLevel = 'WARN';     break;
            case self::NOTICE:   $sLevel = 'NOTICE';   break;
            case self::INFO:     $sLevel = 'INFO';     break;
        }
        $mtime = microtime(true) - BAZALT_START_TIME;
        $mtime *= 1000;
        $log = sprintf("%s %.2f: %s (%s) [%s]: %s\n", date('Y-m-d H:i:s', $time), $mtime, self::$requestUniqueId, $sLevel, $category, $msg);
        return $log;
    }

    /**
     * Return a neatly formatted XML log view, filtered by level or category.
     * @param int $level
     * @param string $category 
     * @return string formatted XML log view
     */
    public static function showLogs($level = NULL, $category = NULL)
    {
        $msg = "\n<!-- Generate on " . date('Y-m-d H:i:s', time()) . " -->\n";
        $keep = $msg;
        if ($level == NULL && $category == NULL) {
            foreach (self::$_logs as $k => $p) {
                if ($p[0] != ''){
                    $msg .= self::formatLog($p[0], $p[1], $p[2], $p[3]);
                }
            }
        }
        else if ($category == NULL) {
            foreach (self::$_logs as $k => $p) {
                if ($p[0]!='' && $p[1] == $level) {
                    $msg .= self::formatLog($p[0], $p[1], $p[2], $p[3]);
                }
            }
        } else if ($level == NULL){
            foreach (self::$_logs as $k => $p){
                if ($p[0] != '' && $p[2] == $category){
                   $msg .= self::formatLog($p[0], $p[1], $p[2], $p[3]);
                }
             }
        } else {
            foreach (self::$_logs as $k => $p){
                if ($p[0] != '' && $p[1] == $level && $p[2] == $category) {
                    $msg .= self::formatLog($p[0], $p[1], $p[2], $p[3]);
                }
             }
        }

        if ($keep != $msg) {
            return $msg;
        }
        return;
    }
}