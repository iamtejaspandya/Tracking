<?php

namespace Tejas\Tracking\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Tejas\Tracking\Model\ConnectionLogFactory;

class LogCustomerConnection implements ObserverInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var RemoteAddress
     */
    protected $remoteAddress;

    /**
     * @var ConnectionLogFactory
     */
    protected $connectionLogFactory;

    /**
     * LogCustomerConnection constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param RemoteAddress $remoteAddress
     * @param ConnectionLogFactory $connectionLogFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        RemoteAddress $remoteAddress,
        ConnectionLogFactory $connectionLogFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->remoteAddress = $remoteAddress;
        $this->connectionLogFactory = $connectionLogFactory;
    }

    /**
     * Execute observer
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $saveDb = $this->scopeConfig->getValue(
            'tejas_tracking/general/track_customer_connections',
            ScopeInterface::SCOPE_STORE
        );
        if ($saveDb) {
            $customer = $observer->getCustomer();
            $ip = $this->remoteAddress->getRemoteAddress();
        
            $connectionLog = $this->connectionLogFactory->create();
            $connectionLog->setCustomerId($customer->getId());
            $connectionLog->setIp($ip);
            $connectionLog->save();
        }
    }
}
