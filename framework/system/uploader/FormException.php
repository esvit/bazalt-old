<?php
/**
 * UploaderException
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
 * @category  Uploader
 * @package   BAZALT/Uploader
 * @author    Alex Slubsky <aslubsky@gmail.com>
 * @license   http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version   SVN: $Rev: 20 $
 * @link      http://www.php-solves.com/
 */
 
 
/**
 * UploaderException
 *
 * @category Uploader
 * @package  BAZALT/Uploader
 * @author   Alex Slubsky <aslubsky@gmail.com>
 * @license  http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version  Release: $Rev: 20 $
 * @link     http://www.php-solves.com/
 */
class Uploader_FormException extends Exception
{
    /**
     * __construct
     *
     * @param int $code Код помилки
     */
    public function __construct($code)
    {
        $message = $this->codeToMessage($code);
        parent::__construct($message, $code);
    }

    /**
     *
     * Повертає текст повідомлення відповідно коду помилки
     *
     * @param int $code Код помилки
     *
     * @return string
     */
    private function codeToMessage($code)
    {
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
                $message = 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $message = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
                break;
            case UPLOAD_ERR_PARTIAL:
                $message = 'The uploaded file was only partially uploaded';
                break;
            case UPLOAD_ERR_NO_FILE:
                $message = 'No file was uploaded';
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $message = 'Missing a temporary folder';
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $message = 'Failed to write file to disk';
                break;
            case UPLOAD_ERR_EXTENSION:
                $message = 'File upload stopped by extension';
                break;

            default:
                $message = 'Could not save uploaded file. The upload was cancelled, or server error encountered';
                break;
        }
        return $message;
    }
}
