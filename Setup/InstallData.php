<?php
/*
 * @package     Intelipost_PreSales
 * @copyright   Copyright (c) 2016 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

namespace Intelipost\PreSales\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
 
class InstallData implements InstallDataInterface
{

protected $_eavSetupFactory;
protected $_scopeConfig;

protected $_attributesList = array ();

public function __construct(
    EavSetupFactory $eavSetupFactory,
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
)
{
    $this->_eavSetupFactory = $eavSetupFactory;
    $this->_scopeConfig = $scopeConfig;

    $this->_attributesList = array(
        'presales' => __('PreSales'),
        'package'  => __('Package'),
    );
}

public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
{
    $setup->startSetup();

    $eavSetup = $this->_eavSetupFactory->create(['setup' => $setup]);

    foreach ($this->_attributesList as $attributeCode => $attributeName)
    {
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'intelipost_product_' . $attributeCode,
            [
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => 'Intelipost Product ' . __($attributeName),
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => ''
            ]
        );
    }

    $eavSetup->addAttribute(
        \Magento\Catalog\Model\Product::ENTITY,
        'intelipost_product_readytogo',
        [
            'type' => 'int',
            'backend' => '',
            'frontend' => '',
            'label' => __('Intelipost Product Ready To Go'),
            'input' => 'select',
            'class' => '',
            'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
            'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
            'visible' => true,
            'required' => false,
            'user_defined' => false,
            'default' => '',
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'used_in_product_listing' => true,
            'unique' => false,
            'apply_to' => ''
        ]
    );

    $setup->endSetup();
}

}

