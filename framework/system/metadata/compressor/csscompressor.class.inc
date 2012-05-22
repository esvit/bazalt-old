<?php
/**
 * cssmin.php - A simple CSS minifier.
 * --
 * 
 * <code>
 * include("cssmin.php");
 * file_put_contents("path/to/target.css", cssmin::minify(file_get_contents("path/to/source.css")));
 * </code>
 * --
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING 
 * BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND 
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, 
 * DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 * --
 *
 * @package     cssmin
 * @author              Joe Scylla <joe.scylla@gmail.com>
 * @copyright   2008 Joe Scylla <joe.scylla@gmail.com>
 * @license     http://opensource.org/licenses/mit-license.php MIT License
 * @version     1.0.1.b3 (2008-10-02)
 */
class CSSCompressor
{
    const REMOVE_LAST_SEMICOLON = 1;

    const REPLACE_RELATIVE_PATH = 2;

    const REPLACE_WITH_COOKIELESS_DOMAIN = 4;

    const REPLACE_DATA_URI = 8;

    const COMPRESS_CSS = 16;

    protected static $cssPath;

    protected static $currentFile = null;

    protected static $currentOptions = 5;

    protected static $cookieLessDomain = null;

    public static function setCssPath($path)
    {
        self::$cssPath = $path;
    }

    public static function getCssPath()
    {
        return self::$cssPath;
    }

    public static function setCookieLessDomain($domain)
    {
        self::$cookieLessDomain = $domain;
    }

    public static function getCookieLessDomain()
    {
        return self::$cookieLessDomain;
    }

    /**
     * Minifies stylesheet definitions
     *
     * <code>
     * $cssMinified = CSSCompressor::minify(file_get_contents("path/to/target/file.css"));
     * </code>
     * 
     * @param string       $css     Stylesheet definitions as string
     * @param array|string $options Array or comma speperated list of options:
     *                              - REMOVE_LAST_SEMICOLON: Removes the last semicolon in 
     *                                the style definition of an element (activated by default).
     *
     *                              - REPLACE_RELATIVE_PATH: Replace relative image path with
     *                                absolute path;
     * @return string Minified stylesheet definitions
     */
    public static function minify($css, $cssFile, $options = null)
    {
        //$css = file_get_contents($cssFile);
        if ($options == null) {
            $options = self::REPLACE_RELATIVE_PATH | self::REMOVE_LAST_SEMICOLON | self::COMPRESS_CSS;
            if (self::$cookieLessDomain != null) {
                $options |= self::REPLACE_WITH_COOKIELESS_DOMAIN;
            }
        }
        self::$currentOptions = $options;
        self::$currentFile = $cssFile;

        if ($options & self::REPLACE_DATA_URI && !CMS_Request::isIE()) {
            # Encode url() to base64
            $cssReplaced = preg_replace_callback("/url\s*\((.*)\)/siU", array('self', 'encodeUrl'), $css);
            if ($cssReplaced !== null) {
                $css = $cssReplaced;
            }
        } else if ($options & self::REPLACE_RELATIVE_PATH) {
            # Replace relative path
            $cssReplaced = preg_replace_callback("/url\s*\((.*)\)/siU", array('self', 'replaceImageRelativePath'), $css);
            if ($cssReplaced !== null) {
                $css = $cssReplaced;
            }
        }
        if ($options & self::COMPRESS_CSS) {
            # Replace CR, LF and TAB to spaces
            $css = str_replace(array("\n", "\r", "\t"), " ", $css);

            # Replace multiple to single space
            $cssReplaced = preg_replace("/\s\s+/", " ", $css);
            if ($cssReplaced !== null) {
                $css = $cssReplaced;
            }
            # Remove unneeded spaces
            $cssReplaced = preg_replace("/\s*({|}|\[|\]|=|~|\+|>|\||;|:|,)\s*/", "$1", $css);
            if ($cssReplaced !== null) {
                $css = $cssReplaced;
            }
            if ($options & self::REMOVE_LAST_SEMICOLON) {
                # Removes the last semicolon of every style definition
                $css = str_replace(";}", "}", $css);
            }
            # Remove comments
            $cssReplaced = preg_replace("/\/\*[\d\D]*?\*\/|\t+/", " ", $css);
            if ($cssReplaced !== null) {
                $css = $cssReplaced;
            }
        }
        return trim($css);
    }

    public static function compress($file, $toFile = null)
    {
        if ($toFile == null) {
            $toFile = $file . '.gz';
        }
        if (OS == OS_WIN) {
            $str = dirname(__FILE__) . '/gzip.exe --best ' . $file . ' -c > ' . $toFile;
        } else {
            $str = '/usr/bin/gzip --best ' . $file . ' -c > ' . $toFile;
        }
        exec($str);
    }

    protected static function getFullPath($file, $forUrl = true)
    {
        $file = trim($file, '"\'');
        if ($file[0] != '/') {
            if ($forUrl) {
                $file = self::$cssPath . '/' . $file;
            } else {
                $file = dirname(self::$currentFile) . '/' . $file;
            }
        } else if (!$forUrl) {
            $file = SITE_DIR . $file;
        }
        return $file;
    }

    protected static function replaceImageRelativePath(array $match)
    {
        $match[1] = trim($match[1], '"\'');
        if (substr($match[1], 0, 4) == 'http') {
            return 'url("' . $match[1] . '")';
        }
        $file = self::getFullPath($match[1], false);
        $additions = '';
        if (($pos = strpos($file, '?')) !== false || ($pos = strpos($file, '?')) !== false) {
            $additions = substr($file, $pos);
            $file = substr($file, 0, $pos);
        }

        $filePath = StaticFiles::save($file, file_get_contents($file));
        
        if (self::$currentOptions & self::REPLACE_WITH_COOKIELESS_DOMAIN && self::$cookieLessDomain != null) {
            $filePath = 'http://' . self::$cookieLessDomain . relativePath($filePath) . $additions;
        }
        return 'url("' . relativePath($filePath) . $additions . '")';
    }

    /**
     * Encodes a url() expression.
     *
     * @param       array   $match
     * @return      string
     */
    protected static function encodeUrl($match)
    {
        $data = $match[1];
        if (substr($match[1], 0, 4) != 'data') {
            $file = self::getFullPath($match[1], false);
            if (file_exists($file)) {
                if(filesize($file) < 10240) {// 10 Kb
                    $info = getimagesize($file);
                    $mime = image_type_to_mime_type($info[2]);

                    $content = base64_encode(file_get_contents($file));
                    if (!empty($content)) {
                        $data = 'data:' . $mime . ';base64;00,' . $content;
                    }
                } else {
                    return self::replaceImageRelativePath($match);
                }
            } else {
                return $match[0];
            }
        }
        return 'url(\'' . $data . '\')';
    }
}