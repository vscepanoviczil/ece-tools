<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MagentoCloud\Config;

use Magento\MagentoCloud\App\Logger;

/**
 * Interface for validators which runs at the very beginning of build or deploy phase
 */
interface ValidatorInterface
{
    const LEVEL_NOTICE = Logger::NOTICE;
    const LEVEL_WARNING = Logger::WARNING;
    const LEVEL_CRITICAL = Logger::CRITICAL;

    /**
     * @return Validator\ResultInterface
     */
    public function validate(): Validator\ResultInterface;
}
