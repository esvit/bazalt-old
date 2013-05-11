<?php

class ComEcommerce_Controller_AdminExportImport extends CMS_Component_Controller
{
    public function exportImportAction()
    {
        if (isset($_POST['export'])) {
            ini_set("memory_limit","128M");
            set_time_limit( 1800 );

            $filename = 'export.csv';

            header("Content-type: text/csv; charset=utf-8");
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
            header("Pragma: must-revalidate");
            ob_end_clean();
            
            $categoryId = (int)$_POST['category'];
            $category = ComEcommerce_Model_Categories::getByIdAndSiteId($categoryId);
            $collection = ComEcommerce_Model_Product::getProductsCollection($category);
            parse_str($_POST['export'], $output);
            $fields = $output['field'];
            echo 'id';
            foreach ($fields as $k => $field) {
                $fields[$k] = DataType_String::fromCamelCase($field);
                echo ';' . $fields[$k];
            }
            echo "\n";
            flush();
            $products = $collection->orderBy('p.id')->fetchAll();
            foreach ($products as $product) {
                echo $product->id;
                foreach ($fields as $field) {
                    echo ';"' . str_replace('"', '""', $product->{$field}) . '"';
                }
                echo "\n";
            }
            flush();
            exit;
        }

        $importForm = new ComEcommerce_Form_ImportForm();
        $importFileForm = new ComEcommerce_Form_ImportFileForm();
        if ($importFileForm->isPostBack()) {
            $fields = array();
            $file = $importFileForm['file']->value();
            $importFileForm->setFile($file);
            foreach ($importFileForm->getFields() as $field) {
                $fields[$field->label()] = DataType_String::fromCamelCase($field->value());
            }

            $kFields = array_keys($fields);
            if (($handle = fopen(PUBLIC_DIR . $file, "r")) !== FALSE) {
                while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                    if ($row++ > 0) {
                        $n = 0;
                        $item = null;
                        foreach ($data as $k => $field) {
                            if ($n++ == 0) {
                                $item = ComEcommerce_Model_Product::getById((int)$field);
                                if (!$item) {
                                    throw new Exception($field);
                                }   
                            } else {
                                $item->{$kFields[$k-1]} = $field;
                            }
                        }
                        $item->save();
                    }
                }
                fclose($handle);
            }
        }

        $this->view->assign('importForm', $importForm->toString());
        if ($importForm->isPostBack()) {
            $file = $importForm->getFile();

            $importFileForm->setFile($file[0]);
            $this->view->assign('onlyImport', true);
            $this->view->assign('importForm', $importFileForm);
            $this->view->assign('data', $importFileForm->getData());
            $this->view->assign('fields', $importFileForm->getFields());
        }

        $this->view->assign('categories', ComEcommerce_Model_Categories::getBySiteId());
        $this->view->assign('exportFields', ComEcommerce::getExportFields());
        $this->view->assign('exportFields', ComEcommerce::getExportFields());
        $this->view->display('admin/export_import');
    }

    /*
    public function settingsAction()
    {
        $form = new ComPages_Form_Settings();

        $this->view->assign('form', $form->__toString());
        $this->view->display('admin/settings');
    }*/
}
