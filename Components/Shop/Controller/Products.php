<?php

class ComEcommerce_Controller_Products extends CMS_Component_Controller
{
    public function productViewAction($id, $alias)
    {
        $product = ComEcommerce_Model_Product::getById((int)$id);
        if (!$product) {
            throw new CMS_Exception_PageNotFound();
        }

        $fields = $product->getReadableFields(true);

        Metatags::set('PRODUCT_TITLE', $product->title);

        $categories = $product->PublicCategories->get();

        $this->view->assign('product', $product);
        $this->view->assign('categories', $categories);
        $this->view->assign('fields', $fields);
        if (count($categories) > 0) {
            $this->view->assignGlobal('productCategory', $categories[0]);
            ComEcommerce::productCategory($categories[0]);
        }
        $this->view->display('page.product');
    }

    public function productsAction($id = null, $category = null, $filter = null, $brandId = null, $brandAlias = null)
    {
        $query = new ComEcommerce_SearchQuery();
        if (!$id) {
            // По всіх категоріях
            Metatags::set('CATEGORY_TITLE', __('Products', ComEcommerce::getName()));

            $category = ComEcommerce_Model_Category::getSiteRootCategory();
        } else {
            // По заданій категорії
            $category = ComEcommerce_Model_Category::getByIdAndCompanyId($id);
            if (!$category || !$category->is_publish) {
                throw new CMS_Exception_PageNotFound();
            }

            Metatags::set('CATEGORY_TITLE', $category->title);

            $query->setFilter('category_id', $category->id);
            foreach ($category->Elements->get() as $childCategory) {
                $query->setFilter('category_id', $childCategory->id);
            }
        }

        $filterObj = new ComEcommerce_Filter($filter);
        $q = $filterObj->addToCollection($query);
        ComEcommerce::currentFilter($filterObj);

        $collection = ComEcommerce_Model_Product::getCollection($category, true);
        //$collection = $query->search('', array('ComEcommerce_Model_Product'));

        $countPerPage = CMS_Option::get(ComEcommerce::PRODUCTS_PAGECOUNT_OPTION, 10);

        $this->view->assign('products', $collection->getPage(null, $countPerPage));

        if ($filter != null) {
            $this->view->assign('pager', $collection->getPager('ComEcommerce.ProductsCategoryFilter', array('id' => $category->id, 'category' => $category->alias, 'filter' => $filter)));
        } else {
            if (!$id) {
                $this->view->assign('pager', $collection->getPager('ComEcommerce.Products'));
            } else {
                $this->view->assign('pager', $collection->getPager('ComEcommerce.ProductsCategory', array('id' => $category->id, 'category' => $category->alias)));
            }
        }

        if ($category) {
            $this->view->assign('category', $category);
        }
        $this->view->assignGlobal('productCategory', $category);
        ComEcommerce::productCategory($category);
        $this->view->display('page.products');
    }
}