<?php
$includePath = Mage::getBaseDir(). "/lib/PhpExcel/Classes";
set_include_path(get_include_path() . PS . $includePath);
class Conlabz_Mreport_Adminhtml_ExportController extends Mage_Adminhtml_Controller_Action{
	
	
	public function indexAction(){
	
		$this->loadLayout();
		$this->loadLayout();
 
		$block = $this->getLayout()->createBlock('mreport/adminhtml_export');
 
		$this->getLayout()->getBlock('content')->append($block);
	    $this->_setActiveMenu('sales/order')
            ->renderLayout();

	
	}
	public function exportAction(){


		$objPHPExcel = new PHPExcel();

		$objPHPExcel->getProperties()->setCreator(Mage::helper("mreport")->__("Conlabz GmbH"));
		$objPHPExcel->getProperties()->setTitle(Mage::helper("mreport")->__("Orders Export"));
		$objPHPExcel->getProperties()->setSubject(Mage::helper("mreport")->__("Orders Export"));
		$objPHPExcel->getProperties()->setDescription(Mage::helper("mreport")->__("Orders Export"));

		$objPHPExcel->setActiveSheetIndex(0);

		$objPHPExcel->getActiveSheet()->SetCellValue('A1', Mage::helper("mreport")->__('Order Date'));
		$objPHPExcel->getActiveSheet()->SetCellValue('B1', Mage::helper("mreport")->__('Order Number'));
		$objPHPExcel->getActiveSheet()->SetCellValue('C1', Mage::helper("mreport")->__('Last name'));
		$objPHPExcel->getActiveSheet()->SetCellValue('D1', Mage::helper("mreport")->__('First name'));
		$objPHPExcel->getActiveSheet()->SetCellValue('E1', Mage::helper("mreport")->__('Zip'));
		$objPHPExcel->getActiveSheet()->SetCellValue('F1', Mage::helper("mreport")->__('City'));
		$objPHPExcel->getActiveSheet()->SetCellValue('G1', Mage::helper("mreport")->__('SKU'));
		$objPHPExcel->getActiveSheet()->SetCellValue('H1', Mage::helper("mreport")->__('Product Name'));
		$objPHPExcel->getActiveSheet()->SetCellValue('I1', Mage::helper("mreport")->__('Price netto'));
		$objPHPExcel->getActiveSheet()->SetCellValue('J1', Mage::helper("mreport")->__('QTY Ordered'));
		$objPHPExcel->getActiveSheet()->SetCellValue('K1', Mage::helper("mreport")->__('Subtotal netto'));
		$objPHPExcel->getActiveSheet()->SetCellValue('L1', Mage::helper("mreport")->__('Status'));

		$styleArray = array(
			'font' => array(
				'bold' => true,
			)
		);

		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('B1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('C1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('D1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('E1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('F1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('G1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('H1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('I1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('J1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle('K1')->applyFromArray($styleArray);
 		$objPHPExcel->getActiveSheet()->getStyle('L1')->applyFromArray($styleArray); 

		$from = str_replace("/","-",$this->getRequest()->getParam('report_from'));
		$to = str_replace("/","-",$this->getRequest()->getParam('report_to'));

		$from = str_replace(".","-",$from);
		$to = str_replace(".","-",$to);

		$from = explode('-', $from);
		$to = explode('-', $to);
		
		if (sizeof($from) == 3 && sizeof($to) == 3){
		
			$toDate = strtotime(date("Y-m-d", strtotime($to[2]."-".$to[1]."-".$to[0])) . " +1 day");
			$orders = Mage::getModel('sales/order')->getCollection()->addAttributeToFilter('created_at', array(
  		 	'from' => $from[2]."-".$from[1]."-".$from[0],
  		 	'to' => date("Y-m-d", $toDate)
    		));
    	}elseif(sizeof($from) == 3){
    		$orders = Mage::getModel('sales/order')->getCollection()->addAttributeToFilter('created_at', array(
  		 	'from' => $from[2]."-".$from[1]."-".$from[0]
  		 	));
    	}elseif(sizeof($to) == 3){
    		$toDate = strtotime(date("Y-m-d", strtotime($to[2]."-".$to[1]."-".$to[0])) . " +1 day");
			$orders = Mage::getModel('sales/order')->getCollection()->addAttributeToFilter('created_at', array(
  		 	'to' => date("Y-m-d", $toDate)
    		));
    	}else{
    		$orders = Mage::getModel('sales/order')->getCollection();
  		}
    	
    	$data = array();
    	$itemsI = 0;
    	$lineItems = array();
    	foreach($orders as $order){
    		
    		$customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
    		$address = Mage::getModel('sales/order_address')->load($order->getData('billing_address_id'));
 
    		$items = $order->getAllItems();
    		foreach ($items as $item){
    		
    			if ($item->getParentItemId()){
    				continue;
    			}

	    		$product = Mage::getModel('catalog/product')->load($item->getProductId());

    			$lineItems[$itemsI]['created_at'] = $order->getData('created_at');
    			$lineItems[$itemsI]['firstname'] = $address->getData('firstname');
    			$lineItems[$itemsI]['lastname'] = $address->getData('lastname');
    			$lineItems[$itemsI]['postcode'] = $address->getData('country_id')."-".$address->getData('postcode');
    			$lineItems[$itemsI]['city'] = $address->getData('city');
    			
    			$lineItems[$itemsI]['sku'] = $product->getData('sku');
    			$lineItems[$itemsI]['name'] = $product->getData('name');
    			$lineItems[$itemsI]['price_netto'] = $item->getData('price');
    			$lineItems[$itemsI]['qty'] = $item->getData('qty_ordered');
    			$lineItems[$itemsI]['global_netto'] = $item->getData('row_total');
    			$lineItems[$itemsI]['status'] = $order->getData('status');
    			$lineItems[$itemsI]['order_id'] = $order->getData('increment_id');
    			
    			$itemsI++;
    		}

    		
    	}
    	$iterator = 2;
    	foreach($lineItems as $line){
    	
    		$data = explode(' ', $line['created_at']);	
    		$objPHPExcel->getActiveSheet()->SetCellValue('A'.$iterator, str_replace("-",".", $data[0]));
			$objPHPExcel->getActiveSheet()->SetCellValue('B'.$iterator, $line['order_id']);
			$objPHPExcel->getActiveSheet()->SetCellValue('C'.$iterator, $line['firstname']);
			$objPHPExcel->getActiveSheet()->SetCellValue('D'.$iterator, $line['lastname']);
			$objPHPExcel->getActiveSheet()->SetCellValue('E'.$iterator, $line['postcode']);
			$objPHPExcel->getActiveSheet()->SetCellValue('F'.$iterator, $line['city']);
			$objPHPExcel->getActiveSheet()->SetCellValue('G'.$iterator, $line['sku']);
			$objPHPExcel->getActiveSheet()->SetCellValue('H'.$iterator, $line['name']);
			$objPHPExcel->getActiveSheet()->SetCellValue('I'.$iterator, $line['price_netto']);
			$objPHPExcel->getActiveSheet()->SetCellValue('J'.$iterator, $line['qty']);
			$objPHPExcel->getActiveSheet()->SetCellValue('K'.$iterator, $line['global_netto']);
			$objPHPExcel->getActiveSheet()->SetCellValue('L'.$iterator, $line['status']);
						
			$currencyCode = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();
			
			$objPHPExcel->getActiveSheet()->getStyle('K'.$iterator)->getNumberFormat()->setFormatCode($currencyCode." #,##0.00");
			$objPHPExcel->getActiveSheet()->getStyle('I'.$iterator)->getNumberFormat()->setFormatCode($currencyCode." #,##0.00");
			$objPHPExcel->getActiveSheet()->getStyle('A'.$iterator)->getNumberFormat()->setFormatCode("yyyy.mm.dd");

    		$iterator++;
    	}

		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);

		$objPHPExcel->getActiveSheet()->setTitle('Simple');

		$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
		
		$baseDir = Mage::getBaseDir()."/media/export/";
		
		if (!is_dir($baseDir)){
			mkdir($baseDir);
		}
		
		$objWriter->save($baseDir.Mage::helper('mreport')->__("Orders").".xlsx");
		$url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)."export/".Mage::helper('mreport')->__("Orders").".xlsx";
		$this->getResponse()->setRedirect($url);
	
	}
}