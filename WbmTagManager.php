<?php
/**
 * Tag Manager
 * Copyright (c) Webmatch GmbH
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

namespace WbmTagManager;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UpdateContext;

/**
 * Class WbmTagManager
 * @package WbmTagManager
 */
class WbmTagManager extends \Shopware\Components\Plugin
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        $container->setParameter('wbm_tag_manager.plugin_dir', $this->getPath());

        parent::build($container);
    }

    /**
     * @param InstallContext $context
     */
    public function install(InstallContext $context)
    {
        $sql = file_get_contents($this->getPath() . '/Resources/sql/install.sql');

        $this->container->get('shopware.db')->query($sql);
    }

    /**
     * @param ActivateContext $context
     */
    public function activate(ActivateContext $context)
    {
        $context->scheduleClearCache(InstallContext::CACHE_LIST_ALL);
    }

    /**
     * @param UpdateContext $context
     */
    public function update(UpdateContext $context)
    {
        if (version_compare($context->getCurrentVersion(), '2.0.0', '<')) {
            $sql = file_get_contents($this->getPath() . '/Resources/sql/install.sql');

            $this->container->get('shopware.db')->query($sql);
        }

        if (version_compare($context->getCurrentVersion(), '2.0.3', '<')) {
            $this->container->get('shopware.db')->query('
                INSERT IGNORE INTO `wbm_data_layer_properties` (`id`, `module`, `parentID`, `name`, `value`) VALUES
                (106, \'frontend_detail_index\', 13, \'products\', \'[$sArticle] as $article\');
            ');
            $this->container->get('shopware.db')->query('
                UPDATE `wbm_data_layer_properties`
                SET `parentID` = 106, `value` = REPLACE(`value`, \'$sArticle\', \'$article\')
                WHERE `id` IN (16, 17, 18, 19, 21);
            ');
        }

        $context->scheduleClearCache(InstallContext::CACHE_LIST_ALL);
    }
}