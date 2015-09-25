<?php
require_once 'app/Mage.php';
Mage::app();
// $products = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('*');
// foreach($products as $rpo)
// {
	// echo $rpo->getId().';';
// }
// echo "<br/>".count($products);exit;
// $productConfig = Mage::getResourceModel('catalog/product_collection')->addAttributeToFilter('type_id', 'configurable'); 	
// foreach($productConfig as $rpo)
// {
	// echo "<br>".$pro->getId().'dad';
// }
// exit;
// chuong trinh demo doc truc tiep file excel v?i ti?ng vi?t unicode 
// set_time_limit(0);
 ini_set('memory_limit', '2048M');
        set_time_limit(180000);
// $filename="import.xls"; // file du lieu demmo chi gom 2 cot hoten va diem
// require_once 'excel_reader2.php'; // nhung thu vien xu ly ma nguon mo 
// $data = new Spreadsheet_Excel_Reader($filename,true,"UTF-8"); // khoi tao doi tuong doc file excel 
// $rowsnum = $data->rowcount($sheet_index=0); // lay so hang cua sheet
// $colsnum =  $data->colcount($sheet_index=0);

	$category = Mage::getModel('catalog/category');
	$tree = $category->getTreeModel();
	$tree->load();
	$ids = $tree->getCollection()->getAllIds();
	$leve = "";
	$catearray = array();
	if ($ids){
		foreach ($ids as $id)
		{
			$ar = Mage::getModel('catalog/category');
			$ar->load($id);
			$textt = $ar->getName();
			$leve = $ar->getLevel();
			$levest = $ar->getLevel();
			$paid = $ar->getParentId();
			if ($levest ==2) {
				
			}
			else {
				while($leve > 2)
				{
					$cat11 = Mage::getModel('catalog/category');
					$cat11->load($paid);
					$textt = $cat11->getName().'/'.$textt;
					$leve = $cat11->getLevel();
					$paid = $cat11->getParentId();
				}
			}
			$catearray[$ar->getId()] = $textt;
		}	
	}
	
	function getlabl($value,$code)
	{
		// $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', 'attribute_id');
		// foreach ( $attribute->getSource()->getAllOptions(true, true) as $option){
			// $attributeArray[$option['value']] = $option['label'];
		// }
		
		$attributeId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product',$code);
		$attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);
		foreach ( $attribute->getSource()->getAllOptions(true, true) as $option){
			$attributeArray[$option['value']] = $option['label'];
		}
		return $attributeArray[$value];
	}
// function xlsBOF() {
// echo pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);
// return;
// }

// function xlsEOF() {
// echo pack("ss", 0x0A, 0x00);
// return;
// }

// function xlsWriteNumber($Row, $Col, $Value) {
// echo pack("sssss", 0x203, 14, $Row, $Col, 0x0);
// echo pack("d", $Value);
// return;
// }

// function xlsWriteLabel($Row, $Col, $Value ) {
// $L = strlen($Value);
// echo pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L);
// echo $Value;
// return;
// }
// header("Pragma: public");
// header("Expires: 0");
// header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
// header("Content-Type: application/force-download");
// header("Content-Type: application/octet-stream");
// header("Content-Type: application/download");;
// header("Content-Disposition: attachment;filename=php2xls.xls "); // แล้วนี่ก็ชื่� ไฟล์
// header("Content-Transfer-Encoding: binary ");

// xlsBOF();
// xlsWriteLabel(0,0,'sku');
// xlsWriteLabel(0,1,'store');
// xlsWriteLabel(0,2,'websites');
// xlsWriteLabel(0,3,'attribute_set');
// xlsWriteLabel(0,4,'categories');
// xlsWriteLabel(0,5,'type');
// xlsWriteLabel(0,6,'has_options');
// xlsWriteLabel(0,7,'name');
// xlsWriteLabel(0,8,'image');
// xlsWriteLabel(0,9,'small_image');
// xlsWriteLabel(0,10,'thumbnail');
// xlsWriteLabel(0,11,'gallery');
// xlsWriteLabel(0,12,'price');
// xlsWriteLabel(0,13,'special_price');
// xlsWriteLabel(0,14,'special_from_date');
// xlsWriteLabel(0,15,'special_to_date');
// xlsWriteLabel(0,16,'weight');
// xlsWriteLabel(0,17,'status');
// xlsWriteLabel(0,18,'visibility');
// xlsWriteLabel(0,19,'enable_googlecheckout');
// xlsWriteLabel(0,20,'tax_class_id');
// xlsWriteLabel(0,21,'description');
// xlsWriteLabel(0,22,'short_description');
// xlsWriteLabel(0,23,'is_in_stock');
// xlsWriteLabel(0,24,'qty');
// xlsWriteLabel(0,25,'manufacturer');
// xlsWriteLabel(0,26,'ring_size');
// xlsWriteLabel(0,27,'color');
// xlsWriteLabel(0,28,'height');
// xlsWriteLabel(0,29,'width');
// xlsWriteLabel(0,30,'length');
// xlsWriteLabel(0,31,'special_packaging');
// xlsWriteLabel(0,32,'free_shipping');
// xlsWriteLabel(0,33,'dangerous_goods');
// xlsWriteLabel(0,34,'dangerous_goods_options');
// xlsWriteLabel(0,35,'is_imported');
// xlsWriteLabel(0,36,'custom_option');
// xlsWriteLabel(0,37,'title');
// xlsWriteLabel(0,38,'type');
echo 'sku,store,websites,attribute_set,categories,type,has_options,name,image,small_image,thumbnail,gallery,price,special_price,special_from_date,special_to_date,weight,status,visibility,enable_googlecheckout,tax_class_id,description,short_description,is_in_stock,qty,manufacturer,ring_size,color,height,width,length,special_packaging,free_shipping,dangerous_goods,dangerous_goods_options,is_imported,custom_option,title,sort_order_op,type'.'<br/>';
$products = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('*');
// $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
$ii = 1;
foreach($products as $prod) {
	$ii = $ii + 1;
	$prod = Mage::getModel('catalog/product')->load($prod->getId());
	
	// xlsWriteLabel($ii,0,$prod->getSku());
	echo '"'.$prod->getSku().'",';
	// xlsWriteLabel($ii,1,'default');
	echo '"default",';
	// xlsWriteLabel($ii,2,'base');
	echo '"base",';
	// xlsWriteLabel($ii,3,'Default');
	echo '"Default",';
	$catelist = "";
	$cac = 0;
	$catsproduct = $prod->getCategoryIds();
	foreach ($catsproduct as $category_id) {
		if ($cac ==0)
		{
			$cac = 1;
			$catelist = $catearray[$category_id];
		}
		else
		{
			$catelist = $catelist.','.$catearray[$category_id];
		}
	} 

	// xlsWriteLabel($ii,4,$catelist);
	echo '"'.$catelist.'",';
	// xlsWriteLabel($ii,5,'simple');
	echo '"simple",';
	// xlsWriteLabel($ii,6,'1');
	echo '"1",';
	// xlsWriteLabel($ii,7,$prod->getName());
	echo '"'.$prod->getName().'",';
	// xlsWriteLabel($ii,8,$prod->getImageUrl());
	echo '"'.$prod->getImageUrl().'",';
	// xlsWriteLabel($ii,9,$prod->getImageUrl());
	echo '"'.$prod->getImageUrl().'",';
	// xlsWriteLabel($ii,10,$prod->getImageUrl());
	echo '"'.$prod->getImageUrl().'",';
	$galley = "";
	$dem = 0;
	foreach ($prod->getMediaGalleryImages() as $image) {
		if ($dem ==0)
		{
			$galley = $image->getUrl();
			$dem = 1;
		}	
		else
		{
			$galley = $galley.';'.$image->getUrl();
		}
	}   			
	// xlsWriteLabel($ii,11,$galley);
	echo '"'.$galley.'",';
	// xlsWriteLabel($ii,12,$prod->getPrice());
	echo '"'.$prod->getPrice().'",';
	// xlsWriteLabel($ii,13,$prod->getFinalPrice());
	echo '"'.$prod->getFinalPrice().'",';
	// xlsWriteLabel($ii,14,$prod->getSpecialFromDate());
	echo '"'.$prod->getSpecialFromDate().'",';
	// xlsWriteLabel($ii,15,$prod->getSpecialToDate());
	echo '"'.$prod->getSpecialToDate().'",';
	// xlsWriteLabel($ii,16,$prod->getWeight());
	echo '"'.$prod->getWeight().'",';
	// xlsWriteLabel($ii,17,$prod->getStatus());
	echo '"'.getlabl($prod->getStatus(),'status').'",';
	// xlsWriteLabel($ii,18,$prod->getVisibility());
	echo '"'.getlabl($prod->getVisibility(),'visibility').'",';
	// xlsWriteLabel($ii,19,$prod->getEnableGooglecheckout());
	echo '"'.$prod->getEnableGooglecheckout().'",';
	// xlsWriteLabel($ii,20,$prod->getTaxClassId());
	echo '"'.getlabl($prod->getTaxClassId(),'tax_class_id').'",';
	// xlsWriteLabel($ii,21,$prod->getDescription());
	echo '"'.htmlspecialchars(str_replace('"','\'',$prod->getDescription())).'",';
	// xlsWriteLabel($ii,22,$prod->getShortDescription());
	echo '"'.htmlspecialchars(str_replace('"','\'',$prod->getShortDescription())).'",';
	// xlsWriteLabel($ii,23,$prod->getIsInStock());
	echo '"'.$prod->getIsInStock().'",';
	// xlsWriteLabel($ii,24,$prod->getQty());
	echo '"'.$prod->getQty().'",';
	// xlsWriteLabel($ii,25,$prod->getManufacturer());
	echo '"'.getlabl($prod->getManufacturer(),'manufacturer').'",';
	// xlsWriteLabel($ii,26,$prod->getRingSize());
	echo '"'.getlabl($prod->getRingSize(),'ring_size').'",';
	// xlsWriteLabel($ii,27,$prod->getColor());
	echo '"'.getlabl($prod->getColor(),'color').'",';
	// xlsWriteLabel($ii,28,$prod->getHeight());
	echo '"'.$prod->getHeight().'",';
	// xlsWriteLabel($ii,29,$prod->getWidth());
	echo '"'.$prod->getWidth().'",';
	// xlsWriteLabel($ii,30,$prod->getLength());
	echo '"'.$prod->getLength().'",';
	// xlsWriteLabel($ii,31,$prod->getSpecialPackaging());
	echo '"'.getlabl($prod->getSpecialPackaging(),'special_packaging').'",';
	// xlsWriteLabel($ii,32,$prod->getFreeShipping());
	echo '"'.getlabl($prod->getFreeShipping(),'free_shipping').'",';
	// xlsWriteLabel($ii,33,$prod->getDangerousGoods());
	echo '"'.getlabl($prod->getDangerousGoods(),'dangerous_goods').'",';
	// xlsWriteLabel($ii,34,$prod->getDangerousGoodsOptions());
	echo '"'.getlabl($prod->getDangerousGoodsOptions(),'dangerous_goods_options').'",';
	// xlsWriteLabel($ii,35,$prod->getIsImported());
	echo '"'.getlabl($prod->getIsImported(),'is_imported').'",';
	$title = "";
	$type = "";
	$cnttt = 0;
	$option = "";
	$cnopt = 0;
	$cntss = 0;
	foreach ($prod->getOptions() as $o) {
		if ($cnttt == 0)
		{
			$title = $o->getTitle();
			$type = $o->getType();
			$cnttt = 1;
		}	
		else
		{
			$title = $title.'canh'.$o->getTitle();
			$type = $type.'canh'.$o->getType();
		}
		
		$values123 = $o->getValues();
		if ($cnopt==0) 
		{	
			$cnopt = 1;
		}else {
			$option = $option.'title';
		}
        foreach ($values123 as $v) {
			$vchild = $v->getData();
			if ($cntss == 0)
			{
				
				$option =  $vchild['title'].':1:15::1';
				// print_r($v->getData());
				$cntss = 1;
			}
			else
			{
				$option =  $option.'|'.$vchild['title'].':1:15::1';
			}
        }
		
		
	}
	// xlsWriteLabel($ii,36,$option);
	echo '"'.$option.'",';
	// xlsWriteLabel($ii,37,$title);
	echo '"'.$title.'",';
	// xlsWriteLabel($ii,38,$type);
	echo '"'.$type.'"<br/>';
	
}
// xlsEOF();
?>