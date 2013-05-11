<?php

require_once('simple_html_dom.php');

require_once dirname(__FILE__) . '/../../bootstrap.php';

use Framework\CMS\Http\Request;
use Components\Shop\Model as Model;

define('ROUTING_NO_SCRIPT_NAME', true);

function parseProductPage($url, $category_id, $id)
{
    $html = file_get_html($url);

    $desc = explode("<p", $html->find('.catalog-item-description', 0)->__toString());
    array_shift($desc);
    array_shift($desc);
    while (strpos($desc[0], 'PropArticle') !== false) {
        array_shift($desc);
    }
    while (mb_strpos($desc[count($desc) - 1], 'Линия') === false) {
        array_pop($desc);
    }
    array_pop($desc);

    $desc = "<p" . implode("\n<p", $desc);

    $product = new Model\Product();
    //$product->id = $id;
    $product->shop_id = 1;
    $product->category_id = $category_id;
    $product->title = $html->find('.catalog-item-title', 0)->text();
    $product->description = $desc;
    $product->code = $html->find('.PropArticle nobr', 0)->text();
    $product->price = (float)$html->find('.redprice', 0)->text();
    $product->is_published = 1;

    $product->save();


    $img = $html->find('.imgZoom img', 0)->src;
    $c = file_get_contents('http://www.oodji.com' . $img);

    $uploadName = Framework\CMS\Bazalt::uploadFilename($img, 'shop');
    file_put_contents($uploadName, $c);

    $image = new Model\ProductImage();
    //$image->id = $id;
    $image->product_id = $product->id;
    $image->url = relativePath($uploadName);
    $image->save();
}
//$id = 10;


$html = file_get_html('http://www.oodji.com/ajax/catalog_section.php?&IBLOCK_ID=319&OFFERS[4396][0]=%D0%A1%D1%83%D0%BC%D0%BA%D0%B8&DETAIL_URL=%2Fwomens_collection%2F%23ELEMENT_ID%23%2F');
$products = $html->find('.catalog-section-item');
foreach ($products as $product) {
    $url = $product->find('.item-img-container', 0)->href;

    parseProductPage('http://www.oodji.com' . $url, 22, $id++);
}