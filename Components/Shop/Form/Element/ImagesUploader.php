<?php

class ComEcommerce_Form_Element_ImagesUploader extends CMS_Form_Element_ImageUploader
{
    protected $product = null;

    public function __construct($name, $attributes = array())
    {
        parent::__construct($name, $attributes);
     
        $this->validAttribute('product_id');
        //$this->view(CMS_Bazalt::getComponent('ComEcommerce')->View);
        //$this->template('elements/images-uploader');
        //$this->javascriptTemplate('elements/javascript/images-uploader');
        
        $this->OnUploadComplete->add(array($this, 'onUploadComplete'));
        $this->size('150x150');
    }
    
    public function setProduct($product)
    {
        $this->product = $product;
        $this->product_id($product->id);
    }
    
    public function toString()
    {
        Scripts::addModule('FileUploader');
        Scripts::addModule('jQuery Tmpl');
        Scripts::addModule('Fancybox');

        $imagesArr = array();
        if($this->product) {
            $q = $this->product->Images->getQuery();
            $q->orderBy('ft.order DESC');
            $images = $q->fetchAll();
            foreach($images as $image) {
                $imagesArr []= json_encode($image->toArray($this->size()));
            }
        }

        $this->view()->assign('images', $imagesArr);
        $this->view()->assign('params', $this->buildParams());
        
        return parent::toString();
    }
    
    public function buildParams()
    {
        $params = array();
        $params['size'] = $this->size();
        if ($this->product) {
            $params['product_id'] = $this->product->id;
        }
        $params[$this->id()] = true;
        return json_encode($params);
    }
    
    public function onUploadComplete($result)
    {
        $product = null;
        if (is_numeric($_REQUEST['product_id'])) {
            $product = ComEcommerce_Model_Product::getById((int)$_REQUEST['product_id']);
            if (!$product) {
                throw new Exception('Product element with ID "' . $_REQUEST['product'] . '" not found');
            }
        }
        using('Framework.System.Drawing');
        if ($result['success']) {
            /*resize*/
            $img = WideImage::load(PUBLIC_DIR . $result['filename'])->asTrueColor();
            if ($img->getWidth() > ComEcommerce_Model_ProductsImages::MAX_WIDTH || $img->getHeight() > ComEcommerce_Model_ProductsImages::MAX_HEIGHT) {
                $img = $img->resize(ComEcommerce_Model_ProductsImages::MAX_WIDTH, ComEcommerce_Model_ProductsImages::MAX_HEIGHT);
                $img->saveToFile(PUBLIC_DIR . $result['filename']);
            }
            $size = isset($_REQUEST['size']) ? $_REQUEST['size'] : $this->size();

            $image = ComEcommerce_Model_ProductsImages::create();
            $image->image = $result['filename'];
            if($product) {
                $image->product_id = $product->id;
                $image->order = ComEcommerce_Model_ProductsImages::getMaxOrder($product) + 1;
            }
            $image->save();
            $result['item'] = $image->toArray();
            $result['item']['thumb'] = $image->getThumb($size);
            $result['thumb'] = $result['item']['thumb'];
            $result['url'] = $result['filename'];
        }
        print json_encode($result);
        exit;
    }
    
    public function save()
    {
        $ids = $this->value();
        if(count($ids) > 0) {
            foreach($ids as $id) {
                $image = ComEcommerce_Model_ProductsImages::getById((int)$id);
                if(!$image->product_id) {
                    $image->product_id = $this->form->DataBindedObject->id;
                    $image->order = ComEcommerce_Model_ProductsImages::getMaxOrder($this->form->DataBindedObject) + 1;
                    $image->save();
                }
            }
        }
    }
    
    public function ajaxDelete($id)
    {
        $obj = ComEcommerce_Model_ProductsImages::getById($id);
        $obj->delete();
    }

    public function ajaxUpdateOrder($order)
    {
        parse_str($order, $output);
        $orders = array_flip($output['p']);
        $img = ComEcommerce_Model_ProductsImages::getById((int)current(array_keys($orders)));
        if ($img->Product) {
            $count = $img->Product->Images->count();
            foreach ($orders as $id => $order) {
                ComEcommerce_Model_ProductsImages::updateOrder($id, $count - $order);
            }
        }
    }
}
