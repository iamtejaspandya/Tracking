<?php
namespace Tejas\Tracking\Cron;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Escaper;
use Magento\Framework\App\Area;
use Tejas\Tracking\Model\ResourceModel\ConnectionLog\CollectionFactory as ConnectionLogCollectionFactory;
use Psr\Log\LoggerInterface;

/**
 * Cron job for sending customer connections email.
 */
class SendCustomerConnectionsEmail
{
    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var ConnectionLogCollectionFactory
     */
    protected $connectionLogCollectionFactory;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor.
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param ScopeConfigInterface $scopeConfig
     * @param TimezoneInterface $timezone
     * @param ConnectionLogCollectionFactory $connectionLogCollectionFactory
     * @param Escaper $escaper
     * @param LoggerInterface $logger
     */
    public function __construct(
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        ScopeConfigInterface $scopeConfig,
        TimezoneInterface $timezone,
        ConnectionLogCollectionFactory $connectionLogCollectionFactory,
        Escaper $escaper,
        LoggerInterface $logger
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->scopeConfig = $scopeConfig;
        $this->timezone = $timezone;
        $this->connectionLogCollectionFactory = $connectionLogCollectionFactory;
        $this->escaper = $escaper;
        $this->logger = $logger;
    }

    /**
     * Execute cron job.
     */
    public function execute()
    {
        try {
            $sendEmail = $this->scopeConfig->getValue(
                'tejas_tracking/general/send_email',
                ScopeInterface::SCOPE_STORE
            );
            if ($sendEmail) {
                $yesterdayStart = $this->timezone->date()
                    ->setTime(0, 0, 0)
                    ->sub(new \DateInterval('P1D'))
                    ->format('Y-m-d H:i:s');
                $yesterdayEnd = $this->timezone->date()
                    ->setTime(23, 59, 59)
                    ->sub(new \DateInterval('P1D'))
                    ->format('Y-m-d H:i:s');

                $connectionLogCollection = $this->connectionLogCollectionFactory->create();
                $connectionLogCollection->addFieldToFilter('created_at', ['from' => $yesterdayStart,
                'to' => $yesterdayEnd]);
                $totalConnections = $connectionLogCollection->getSize();

                if ($totalConnections > 0) {
                    $receiverEmail = $this->scopeConfig->getValue(
                        'tejas_tracking/general/email_receiver',
                        ScopeInterface::SCOPE_STORE
                    );
                    $senderName = $this->scopeConfig->getValue(
                        'trans_email/ident_general/name',
                        ScopeInterface::SCOPE_STORE
                    );
                    $senderEmail = $this->scopeConfig->getValue(
                        'trans_email/ident_general/email',
                        ScopeInterface::SCOPE_STORE
                    );

                    $this->inlineTranslation->suspend();

                    $transport = $this->transportBuilder
                        ->setTemplateIdentifier('customer_connections_email_template')
                        ->setTemplateOptions(
                            [
                            'area' => Area::AREA_FRONTEND,
                            'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                            ]
                        )
                        ->setTemplateVars(
                            [
                            'total_connections' => $totalConnections,
                            ]
                        )
                        ->setFrom(
                            [
                            'email' => $this->escaper->escapeHtml($senderEmail),
                            'name' => $this->escaper->escapeHtml($senderName),
                            ]
                        )
                        ->addTo($this->escaper->escapeHtml($receiverEmail))
                        ->getTransport();

                    $transport->sendMessage();

                    $this->inlineTranslation->resume();
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('Error in SendCustomerConnectionsEmail cron job: ' . $e->getMessage());
        }
    }
}
