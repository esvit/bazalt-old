<?php
/**
 * Uploader
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
 * @category Uploader
 * @package  BAZALT/Uploader
 * @author   Alex Slubsky <aslubsky@gmail.com>
 * @license  http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version  SVN: $Rev: 109 $
 * @link     http://www.php-solves.com/
 */

define('DEFAULT_MAX_SIZE', 10485760);

class Uploader_Base
{
    private $allowedExtensions = array();
    private $sizeLimit = DEFAULT_MAX_SIZE;
    private $file;

    public function __construct(array $allowedExtensions = array(), $sizeLimit = DEFAULT_MAX_SIZE)
    {
        $allowedExtensions = array_map("strtolower", $allowedExtensions);
            
        $this->allowedExtensions = $allowedExtensions;        
        $this->sizeLimit = $sizeLimit;       

        if (isset($_GET['qqfile'])) {
            $this->file = new Uploader_Ajax();
        } elseif (isset($_FILES['qqfile'])) {
            $this->file = new Uploader_Form();
        } else {
            $this->file = false; 
        }
    }
    
    /**
     * Returns array('success'=>true) or array('error'=>'error message')
     */
    public function handleUpload($uploadDirectory, $relativeDir = null)
    {
        if (!is_writable($uploadDirectory)){
            return array('error' => 'Server error. Upload directory isn\'t writable.');
        }
        
        if (!$this->file){
            return array('error' => 'No files were uploaded.');
        }
        
        $size = $this->file->getSize();
        
        if ($size == 0) {
            return array('error' => 'File is empty');
        }
        
        if ($size > $this->sizeLimit) {
            return array('error' => 'File is too large');
        }

        $pathinfo = pathinfo($this->file->getName());

        $filename = md5(uniqid());
        $ext = $pathinfo['extension'];

        if($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)){
            $these = implode(', ', $this->allowedExtensions);
            return array(
                        'error' => 'File has an invalid extension, it should be one of '. $these . '.'
                        );
        }

        $fullname = $uploadDirectory . '/' . $filename[0] . '/' . $filename[1] . '/' . $filename . '.' . $ext;
        mkdir(dirname($fullname), 0777, true);

        try {
            $res = $this->file->save($fullname);
        } catch(Exception $ex) {
            return array('error' => $ex->getMessage());
        }
        if ($relativeDir == null) {
            $relativeDir = $uploadDirectory;
        }
        return array(
            'success' => true, 
            'name' => $this->file->getName(), 
            'filename' => relativePath($fullname, $relativeDir)
        );
    }    
}