<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MagentoCloud\Step;

use Magento\MagentoCloud\Filesystem\FileSystemException;
use Magento\MagentoCloud\Config\Deploy\Writer;
use Psr\Log\LoggerInterface;

/**
 * Switching magento to production mode.
 * This is making for compatibility magento with cloud environment read-only file system.
 * As magento contains logic that skips checking on read-only file system only in production mode.
 */
class SetProductionMode implements StepInterface
{
    /**
     * @var Writer
     */
    private $writer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     * @param Writer $deployConfigWriter
     */
    public function __construct(
        LoggerInterface $logger,
        Writer $deployConfigWriter
    ) {
        $this->logger = $logger;
        $this->writer = $deployConfigWriter;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $this->logger->info('Set Magento application mode to \'production\'');

        try {
            $this->writer->update(['MAGE_MODE' => 'production']);
        } catch (FileSystemException $exception) {
            throw new StepException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
