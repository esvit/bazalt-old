<?php

using('Framework.System.Html');

class ComEcommerce_Form_ProductEdit extends Html_Form
{
    protected $flash = null;

    protected $languageTabs = null;
    
    protected $categories = null;
    
    protected $variants = null;
    
    protected $productType = null;
    
    protected $fields = null;

    protected $images = null;
    
    protected $brands = null;

    protected $prices = null;

    protected $code = null;
    
    protected $hitCheckbox = null;
    
    protected $isLatestCheckbox = null;
    
    protected $isDiscountCheckbox = null;
    
    protected $inStockCheckbox = null;
    
    protected $isAuctionCheckbox = null;
    
    protected $isShopCheckbox = null;
    
    protected $canOrderCheckbox = null;
    
    protected $countField = null;

    public function __construct($name = null, $attributes = array())
    {
        parent::__construct('product', $attributes);

        CMS_Bazalt::getComponent('ComEcommerce')->addWebservice('ComEcommerce_Webservice_Related');

        CMS_Bazalt::getComponent('ComEcommerce')->addScript('js/ComEcommerce_Form_ProductEdit.js');

        $this->addElement('validationsummary', 'errors');
        
        $this->csrf(false);
        $this->flash = $this->addElement('flasher');

        $tabs =  $this->addElement('tabs', 'tabs');
        $infoTab = $tabs->addTab(__('Information', ComEcommerce::getName()));

        $paramsTab = $tabs->addTab(__('Parameters', ComEcommerce::getName()));

        $productTypes = ComEcommerce_Model_ProductTypes::getList();
        $this->productType = $infoTab->addElement('select', 'type_id')
                ->label(__('Type', ComEcommerce::getName()))
                ->addClass('ui-input')
                ->addRuleNonEmpty();
        
        $this->productType->addOption('-', '');
        foreach ($productTypes as $productType) {
            $str = str_repeat('&nbsp;&nbsp;&nbsp;', $productType->depth - 1);
            $this->productType->addOption($str . $productType->title, $productType->id);
        }

        if ($this->languageTabs == null) {
            $this->languageTabs = $infoTab->addElement('languageTabs');
        }
        $tab = $this->languageTabs;
        
        $tab->addElement('text', 'title')
            ->label(__('Title', ComEcommerce::getName()))
            ->addClass('ui-large-input')
            ->addRuleNonEmpty();
            
        $tab->addElement('wysiwyg', 'description')
            ->label(__('Description', ComEcommerce::getName()))
            ->addClass('ui-input')
            ->addRuleNonEmpty();

        $this->hitCheckbox = $infoTab->addElement('checkbox', 'hit')
                ->label(__('Hit', ComEcommerce::getName()));

        $this->isLatestCheckbox = $infoTab->addElement('checkbox', 'is_latest')
                ->label(__('Latest', ComEcommerce::getName()));

        $this->isDiscountCheckbox = $infoTab->addElement('checkbox', 'is_discount')
                ->label(__('Discount', ComEcommerce::getName()));

        $this->inStockCheckbox = $infoTab->addElement('checkbox', 'in_stock')
                ->label(__('In stock', ComEcommerce::getName()));

        $this->canOrderCheckbox = $infoTab->addElement('checkbox', 'can_order')
                ->label(__('Can order', ComEcommerce::getName()));

        $this->code = $infoTab->addElement('text', 'code')
            ->label(__('Article', ComEcommerce::getName()))
            ->addClass('ui-input');
            
        $brands = ComEcommerce_Model_Brands::getList();
        $this->brands = $infoTab->addElement('select', 'brand_id')
                ->label(__('Brand', ComEcommerce::getName()))
                ->addClass('ui-input');

        $this->brands->addOption(' - ', '');
        foreach ($brands as $b) {
            $this->brands->addOption($b->title, $b->id);
        }

        $categoriesTab = $tabs->addTab(__('Categories', ComEcommerce::getName()));
        $this->categories = $categoriesTab->addElement(new ComEcommerce_Form_Element_CategoriesSelect('categories'), 'categories')
                                ->label(__('Categories', ComEcommerce::getName()));
        
        $this->addElement('html')
             ->html('<div class="spacer"></div>');

        //$this->prices = $infoTab->addElement(new ComEcommerce_Form_Element_Prices('prices'), 'prices')
        //                        ->label(__('Prices', ComEcommerce::getName()));
        $this->prices = $infoTab->addElement('text', 'price')
                ->label(__('Price', ComEcommerce::getName()))
                ->addClass('ui-input');
                                
//        $this->images = $infoTab->addElement(new CMS_Form_Element_ImageUploader('images'), 'Images')
//                                ->label(__('Images', ComEcommerce::getName()));

        $this->images = $infoTab->addElement('imageuploader', 'Images')
                            ->label(__('Images', ComEcommerce::getName()));
                                
        $this->countField = $infoTab->addElement('text', 'count')
            ->label(__('The rest in the storage', ComEcommerce::getName()))
            ->addClass('ui-input');

        $this->fields = $paramsTab->addElement(new ComEcommerce_Form_Element_ProductsFieldsEditor('fields'), 'fields')
                                ->label(__('Fields', ComEcommerce::getName()));


        $this->isAuctionCheckbox = $infoTab->addElement('checkbox', 'is_auc')
                ->label(__('Auction', ComEcommerce::getName()));

        $this->isShopCheckbox = $infoTab->addElement('checkbox', 'is_shop')
                ->label(__('Shop', ComEcommerce::getName()));

        $relatedTab = $tabs->addTab(__('Related', ComEcommerce::getName()));

        $relatedTab->addElement('text', 'search')
            ->label(__('Search', ComEcommerce::getName()));

        $relatedTab->addElement('button', 'search_button')
            ->content(__('Search', ComEcommerce::getName()))
            ->addClass('btn btn-primary btn-large');

        $relatedTab->addElement('html')
            ->html(__('Search items', ComEcommerce::getName()).'<table id="related-search"></table>');

        $relatedTab->addElement('html')
            ->html(__('Related items', ComEcommerce::getName()).'<table id="related-list"></table>');

        $group = $this->addElement('group');

        $group->addElement('button', 'post')
              ->content(__('Save', ComEcommerce::getName()))
              ->addClass('btn btn-primary btn-large');

        $group->addElement('button', 'cancel')
              ->content(__('Cancel', ComEcommerce::getName()))
              ->reset();
    }

    public function dataBind($obj)
    {
        if ($obj->hit) $this->hitCheckbox->checked(true);
        if ($obj->in_stock) $this->inStockCheckbox->checked(true);
        if ($obj->can_order)  $this->canOrderCheckbox->checked(true);
        if ($obj->is_discount)  $this->isDiscountCheckbox->checked(true);
        if ($obj->is_latest)  $this->isLatestCheckbox->checked(true);
        if ($obj->id) {
            $this->isAuctionCheckbox->visible(false);
            $this->isShopCheckbox->visible(false);
        }
        if ($obj->id) {
            //$this->images->setProduct($obj);
            //$this->prices->setPrices($obj->Prices->get());
            $this->categories->setCategories($obj->Categories->get());
            $this->fields->dataBind($obj);
        }
//        if (!$this->isPostBack()) {
//            $images = array();
//            foreach ($obj->Images->get() as $image) {
//                $images []= $image->image;
//            }
//            $this->images->value($images);
//        }
        return parent::dataBind($obj);
    }

    public function save()
    {
        $text = __('Product successfully saved.', ComEcommerce::getName());
        $this->flash->text($text);
        
        $values = $this->value();
        $bId = $this->brands->value();
        if (is_numeric($bId)) {
            $this->DataBindedObject->brand_id = (int)$bId;
        } else {
            $this->DataBindedObject->brand_id = null;
        }
        $product = $this->DataBindedObject;
       //  print_r($this->images->value());exit;
        $product->type_id = $this->productType->value();
        $product->hit = $values['tab1']['hit'] == '1';
        $product->in_stock = $values['tab1']['in_stock'] == '1';
        $product->can_order = $values['tab1']['can_order'] == '1';
        $product->is_discount = $values['tab1']['is_discount'] == '1';
        $product->is_latest = $values['tab1']['is_latest'] == '1';
        $product->is_auction = $values['tab1']['is_auc'] == '1';
        $product->price = $this->prices->value();
        $product->count = $this->countField->value();
        $product->code = $this->code->value();
        $product->count_img = count($this->images->value());

        $res = parent::save();
        
        $this->categories->save();
        $this->fields->save();

//        $product->Images->removeAll();
//        foreach($this->images->value() as $n => $imageFile) {
//            $image = new ComEcommerce_Model_ProductsImages();
//            $image->image = $imageFile;
//            $image->order = $n;
//            $product->Images->add($image);
//        }
        //$this->prices->save();
        
        return $res;
    }
    
    public function validate()
    {
        $res = parent::validate();
        if($res) {
            $values = $this->value();
            if (!empty($values['code'])) {
                $product = ComEcommerce_Model_Product::getByCode($values['code']);
                if($product && $product->id != $this->DataBindedObject->id) {
                    $this['code']->addError(sprintf(
                        __('Product with code "%s" already exists', ComEcommerce::getName()),
                        $values['code']));
                    return false;
                }
            }
        }
        return $res;
    }
    public function getJavascript($params = array())
    {
        $params['productId'] = (int)$this->dataBindedObject->id;
        return parent::getJavascript($params);
    }
}
