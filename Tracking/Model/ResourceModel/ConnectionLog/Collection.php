<?php

namespace Tejas\Tracking\Model\ResourceModel\ConnectionLog;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Tejas\Tracking\Model\ConnectionLog;
use Tejas\Tracking\Model\ResourceModel\ConnectionLog as DataResourceModel;

/**
 * Collection class for managing data entities.
 */
class Collection extends AbstractCollection
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ConnectionLog::class, DataResourceModel::class);
    }
}
