<?php

class ComEcommerce_Controller_Admin_Default extends CMS_Component_Controller
{
    public function productsAction()
    {
        $form = new Html_Form('list');

        $this->component->addWebservice('ComEcommerce_Webservice_TreeCategories');

        $form->addElement(new ComEcommerce_Form_Element_CategoriesSelect('categories'), 'categories')
             ->checkboxes(false);

        $form->addElement(new ComEcommerce_Form_Table_Products('table'));
        $form['table']->collection(ComEcommerce_Model_Product::getCollection())
              ->pager('ComEcommerce.ProductsList');

        if (isset($_GET['selectId'])) {
            $category = ComEcommerce_Model_Category::getByIdAndCompanyId($categoryId);
            if (!$category) {
                throw new Exception('Category not found');
            }
            $form['table']->collection(ComEcommerce_Model_Product::getProductsCollection($category));
            $this->view->assign('selectId', (int)$_GET['selectId']);
        }
        $this->view->assign('form', $form);
        $this->view->display('admin/products');
    }

    public function productEditAction($id = null)
    {
        $this->component->ProductsMenu->activate();
        if (!empty($id)) {
            $obj = ComEcommerce_Model_Product::getById((int)$id);
            if (!$obj) {
                throw new CMS_Exception_PageNotFound();
            }
            $this->view->assign('edit_obj', $obj);
        } else {
            $obj = ComEcommerce_Model_Product::create();
        }

        $form = new ComEcommerce_Form_ProductEdit();

        $form->dataBind($obj);

        if ($form->isPostBack() && $form->validate()){
            $form->save();

            Url::redirect($this->component->urlFor('ComEcommerce.ProductEdit', array('id' => $form->DataBindedObject->id)));
        }

        $this->view->assign('selectId', (int)$_GET['selectId']);
        $this->view->assign('form', $form->toString());
        $this->view->display('admin/product-edit');
    }

    public function productCloneAction($id)
    {
        $product = ComEcommerce_Model_Product::getById((int)$id);
        if (!$product) {
            throw new CMS_Exception_PageNotFound();
        }
        $fields = $product->Fields->get();
        $categories = $product->Categories->get();

        $clonedProduct = clone $product;
        $clonedProduct->id = null;
        $clonedProduct->created_at = date('Y-m-d H:i:s');
        $clonedProduct->updated_at = null;
        $clonedProduct->save();
        $clonedProduct->title = $product->title;
        $clonedProduct->description = $product->description;
        $clonedProduct->save();

        foreach ($fields as $field) {
            if (!$clonedProduct->Fields->has($field)) {
                $clonedProduct->Fields->add($field, array('value' => $field->value));
            }
        }
        foreach ($categories as $category) {
            if (!$clonedProduct->Categories->has($category)) {
                $clonedProduct->Categories->add($category);
            }
        }

        Url::redirect($this->component->urlFor('ComEcommerce.ProductEdit', array('id' => $clonedProduct->id)));
    }

    public function categoriesAction()
    {
        $this->component->CategoriesMenu->activate();
        $this->component->addWebservice('ComEcommerce_Webservice_TreeCategories');

        $this->view->assign('rootCategory', ComEcommerce_Model_Category::getSiteRootCategory());

        $itemEditForm = new ComEcommerce_Form_CategoryEdit();
        $this->view->assign('form', $itemEditForm->toString());

        $this->view->assign('languages', CMS_Language::getLanguages());
        $this->view->assign('language', CMS_Language::getCurrentLanguage());
        $this->view->display('admin/categories');
    }
}
