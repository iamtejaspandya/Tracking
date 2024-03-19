<?php

namespace Tejas\Tracking\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Tejas\Tracking\Model\ResourceModel\ConnectionLog as DataResourceModel;

/**
 * Model class for managing data.
 */
class ConnectionLog extends AbstractModel
{
    /**
     * Initialize resource model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(DataResourceModel::class);
    }
}
