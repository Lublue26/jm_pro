<?php
class Tegdesign_Emailcollector_Block_Adminhtml_Emails_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
         
        $this->setDefaultSort('id');
        $this->setId('tegdesign_emailcollector_emails_grid');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);

    }
     
    protected function _getCollectionClass()
    {
        return 'tegdesign_emailcollector/emails_collection';
    }
     
    protected function _prepareCollection()
    {
        
        $collection = Mage::getResourceModel($this->_getCollectionClass());
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }
     protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('emailcollector_emails');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => $this->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => $this->__('Are you sure?')
        ));

        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addExportType('*/*/exportCsv', Mage::helper('tegdesign_emailcollector')->__('CSV'));
		$this->addExportType('*/*/exportExcel',Mage::helper('tegdesign_emailcollector')->__('Excel XML'));
	
		$this->addColumn('id',
            array(
                'header'=> $this->__('ID'),
                'index' => 'id'
            )
        );

        $this->addColumn('date_collected',
            array(
                'header'=> $this->__('Date Collected'),
                'index' => 'date_collected',
                'type'=>'datetime'
            )
        );

        $this->addColumn('email',
            array(
                'header'=> $this->__('Email'),
                'index' => 'email'
            )
        );

        $this->addColumn('customer_id',
            array(
                'header'=> $this->__('Customer ID'),
                'index' => 'email',
                'renderer'  => 'Tegdesign_Emailcollector_Block_Adminhtml_Emails_Renderer_Customer',
            )
        );

        $this->addColumn('customer_value',
            array(
                'header'=> $this->__('Customer Value'),
                'index' => 'email',
                'renderer'  => 'Tegdesign_Emailcollector_Block_Adminhtml_Emails_Renderer_Customervalue',
            )
        );

        $this->addColumn('coupon',
            array(
                'header'=> $this->__('Coupon'),
                'index' => 'coupon'
            )
        );

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'    => Mage::helper('customer')->__('Store'),
                'align'     => 'center',
                'width'     => '80px',
                'type'      => 'options',
                'options'   => Mage::getSingleton('adminhtml/system_store')->getStoreOptionHash(true),
                'index'     => 'store_id',
            ));
        }


        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('website_id', array(
                'header'    => Mage::helper('customer')->__('Website'),
                'align'     => 'center',
                'width'     => '80px',
                'type'      => 'options',
                'options'   => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(true),
                'index'     => 'website_id',
            ));
        }

        return parent::_prepareColumns();
    }
     
    public function getRowUrl($row)
    {
        //return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
    
}
