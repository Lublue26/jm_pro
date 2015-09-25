<?php 
	require_once 'app/Mage.php';
	Mage::app();
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
			while($leve >= 2)
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
	foreach($catearray as $arrkey=>$valuee)
	{
		echo $arrkey.'_______'.$valuee.'<br/>';
	}
?>