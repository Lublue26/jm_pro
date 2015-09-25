<?php

class Queldorei_ShopperSettings_Block_Product_List extends Mage_Catalog_Block_Product_List
{
    protected function _beforeToHtml()
    {
        /*
        $category = Mage::getModel('catalog/category');
        $category->load(204);
        $collection = $category->getProductCollection();
        $collection->addAttributeToSelect('*')->addAttributeToFilter('price', array(
            'lteq' => 50
        ));
        $a1 = array();
        foreach ($collection->getItems() as $p) {
            $id = $p->getId();
            $a1[] = $id;
        }
        if (count($a1) == 0) {
            $category = Mage::getModel('catalog/category');
            $category->load(204);
            $collection = $category->getProductCollection();
            $collection->addAttributeToSelect('*');
            foreach ($collection->getItems() as $p) {
                $id = $p->getId();
                $a1[] = $id;
            }
        }
        $category = Mage::getModel('catalog/category');
        $category->load(204);
        $collection = $category->getProductCollection();
        $collection->addAttributeToSelect('*')->addAttributeToFilter('price', array(
            'lt' => 200,
            'gt' => 50,
        ));
        $a2 = array();
        foreach ($collection as $p) {
            $id = $p->getId();
            $a2[] = $id;
        }
        if (count($a2) == 0) {
            $category = Mage::getModel('catalog/category');
            $category->load(204);
            $collection = $category->getProductCollection();
            $collection->addAttributeToSelect('*');
            foreach ($collection->getItems() as $p) {
                $id = $p->getId();
                $a2[] = $id;
            }
        }
        $category = Mage::getModel('catalog/category');
        $category->load(204);
        $collection = $category->getProductCollection();
        $collection->addAttributeToSelect('*')->addAttributeToFilter('price', array(
            'gteq' => 200,
            'lt' => 500,
        ));
        $a3 = array();
        foreach ($collection as $p) {
            $id = $p->getId();
            $a3[] = $id;
        }
        if (count($a3) == 0) {
            $category = Mage::getModel('catalog/category');
            $category->load(204);
            $collection = $category->getProductCollection();
            $collection->addAttributeToSelect('*');
            foreach ($collection->getItems() as $p) {
                $id = $p->getId();
                $a3[] = $id;
            }
        }
        $category = Mage::getModel('catalog/category');
        $category->load(204);
        $collection = $category->getProductCollection();
        $collection->addAttributeToSelect('*')->addAttributeToFilter('price', array(
            'gteq' => 500
        ));
        $a4 = array();
        foreach ($collection as $p) {
            $id = $p->getId();
            $a4[] = $id;
        }
        if (count($a4) == 0) {
            $category = Mage::getModel('catalog/category');
            $category->load(204);
            $collection = $category->getProductCollection();
            $collection->addAttributeToSelect('*');
            foreach ($collection->getItems() as $p) {
                $id = $p->getId();
                $a4[] = $id;
            }
        }
        $k1 = array_rand($a1, 1);
        $k2 = array_rand($a2, 1);
        $k3 = array_rand($a3, 1);
        $k4 = array_rand($a4, 1);
        $aa = array($a1[$k1], $a2[$k2], $a3[$k3], $a4[$k4]);
//        die;
//        for ($i = 0; $i < 8; $i++) {
//            $aa[$i][] = $a1[$i];
//            $aa[$i][] = $a2[$i];
//            $aa[$i][] = $a3[$i];
//        }
        */
        
        $catId = $this->getData("category_id");
        if ($catId && $catId > 0){
            //ok
        }else{
           $catId = 2; 
        }
        
        $category = Mage::getModel('catalog/category');
        $category->load($catId);
        $collection = $category->getProductCollection();
        $collection->addAttributeToSelect('*')->addAttributeToSort('price', 'ASC');
        $a = array();
        foreach ($collection->getItems() as $p) {
            $a[] = $p->getId();
        }
        $k2 = array_rand($a, 4);
        $aa = array($a[$k2[0]], $a[$k2[1]], $a[$k2[2]], $a[$k2[3]]);
        $category = Mage::getModel('catalog/category');
        $category->load($catId);
        $collection = $category->getProductCollection();
        $collection->addAttributeToSelect('*')->addAttributeToFilter('entity_id', array('in' => $aa))->addAttributeToSort('price', 'ASC');
//        $collection = $this->_getProductCollection();
//        $numProducts = $this->getNumProducts();
//        if ($numProducts) {
//            $collection->setPageSize($numProducts)->load();
//        }
        $this->setCollection($collection);
        return parent::_beforeToHtml();
    }

    public function getBlockTitle()
    {
        $title = $this->getTitle();
        if (empty($title)) {
            $title = 'Featured Products';
        }
        return $title;
    }
}