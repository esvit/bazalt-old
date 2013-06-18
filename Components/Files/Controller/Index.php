<?php

namespace Components\Files\Controller;

use Framework\CMS as CMS,
    Components\Files\Model\File,
    Bazalt\Routing\Route;

class Index extends CMS\AbstractController
{
    public function elFinderAction()
    {
        $connector = \Components\Files\elFinder::connector();
        $connector->run();
        exit;
    }

    public function viewFilesAction($category)
    {
        if (!$category) {
            throw new CMS\Exception\PageNotFound();
        }
        $files = File::getFilesCollection($category);

        $this->view->assign('folder', $category);
        $this->view->assign('files', $files->getPage());
        $this->view->assign('pageCount', $files->getPagesCount());

        $this->view->display('page.downloads');
    }

    protected static function outputNotFound()
    {
        $type = CMS\Request::getSupportedMimeType(array('image/*'));
        if ($type != null) {

            using('Framework.System.Drawing');
            $img = WideImage_TrueColorImage::create(300, 200);
            $canvas = $img->getCanvas();
            $canvas->fill(10, 10, $img->allocateColor(255, 255, 255));
            $path = dirname(__FILE__) . '/../assets/fonts/arial.ttf';
            $canvas->useFont($path, 16, $img->allocateColor(0, 0, 0));
            $canvas->writeText('center', 'center', __('Image not found', ComFileStorage::getName()), 0);

            $img->output('png');
        } else {
            throw new CMS\Exception\PageNotFound();
        }
        CMS\Response::notFound();
    }

    public function downloadFileAction($id)
    {
        $file = File::getById($id);
        if (!$file) {
            self::outputNotFound();
        }
        $filename = $file->name;
        $filepath = SITE_DIR . $file->path;

        if (preg_match( '/[ \(\)\<\>\@\,\;\:\\\"\/\[\]\?\=]/', $filename)) {
          // tsp : "(" ")" "<" ">" "@" "," ";" ":" "\" <"> "/" "[" "]" "?" "="
          $filename = "\"$filename\"";
        }

        if (!file_exists($filepath)) {
            self::outputNotFound();
        }
        $file->downloads++;
        $file->save();

        if (substr($file->mimetype, 0, 6) != 'image/' && substr($file->mimetype, 0, 5) != 'text/') {
            header("Content-Type: application/octet-stream");
            header("Accept-Ranges: bytes");
            header("Content-Length: " . filesize($filepath));
            header("Content-Disposition: attachment; filename=" . $filename);  
        } else {
            header("Content-Type: " . $file->mimetype);
        }
        readfile($filepath);
        exit;
    }
}