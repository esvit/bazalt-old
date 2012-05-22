<?php
/**
 * File relativepath.func.inc
 *
 * @category   Core
 * @package    BAZALT
 * @subpackage Functions
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    SVN: $Id$
 * @link       http://bazalt-cms.com/
 */

/**
 * Get relative path of file on site
 *
 * @param  string $path    Absolute file path
 * @return string $relPath Relative file path
 */
function relativePath($path, $relPath = PUBLIC_DIR)
{
    $siteDir = str_replace('\\', '/', $relPath);
    $path = str_replace('\\', '/', $path);

    $path = str_replace($siteDir, '', $path);
    return $path;
}