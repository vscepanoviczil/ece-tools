<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MagentoCloud\Step\Deploy\InstallUpdate\Install;

use Magento\MagentoCloud\Config\Environment;
use Magento\MagentoCloud\Filesystem\FileSystemException;
use Magento\MagentoCloud\Step\StepException;
use Magento\MagentoCloud\Step\StepInterface;
use Psr\Log\LoggerInterface;
use Magento\MagentoCloud\Util\UrlManager;
use Magento\MagentoCloud\Filesystem\Driver\File;
use Magento\MagentoCloud\Filesystem\DirectoryList;

/**
 * Sends email with link to reset password.
 *
 * {@inheritdoc}
 */
class ResetPassword implements StepInterface
{
    /**
     * @var File
     */
    private $file;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Environment
     */
    private $environment;

    /**
     * @var UrlManager
     */
    private $urlManager;

    /**
     * @param LoggerInterface $logger
     * @param Environment $environment
     * @param UrlManager $urlManager
     * @param File $file
     * @param DirectoryList $directoryList
     */
    public function __construct(
        LoggerInterface $logger,
        Environment $environment,
        UrlManager $urlManager,
        File $file,
        DirectoryList $directoryList
    ) {
        $this->logger = $logger;
        $this->environment = $environment;
        $this->urlManager = $urlManager;
        $this->file = $file;
        $this->directoryList = $directoryList;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        if ($this->environment->getAdminPassword() || !$this->environment->getAdminEmail()) {
            return;
        }

        $credentialsFile = $this->directoryList->getMagentoRoot() . '/var/credentials_email.txt';
        $templateFile = $this->directoryList->getViews() . '/reset_password.html';

        $adminEmail = $this->environment->getAdminEmail();
        $adminUsername = $this->environment->getAdminUsername() ?: Environment::DEFAULT_ADMIN_NAME;
        $adminName = $this->environment->getAdminFirstname();

        $adminUrl = $this->urlManager->getUrls()['secure']['']
            . ($this->environment->getAdminUrl() ?: Environment::DEFAULT_ADMIN_URL);

        try {
            $emailContent = strtr(
                $this->file->fileGetContents($templateFile),
                [
                    '{{ admin_url }}' => $adminUrl,
                    '{{ admin_email }}' => $adminEmail,
                    '{{ admin_name }}' => $adminName ?: $adminUsername,
                ]
            );
        } catch (FileSystemException $exception) {
            throw new StepException($exception->getMessage(), $exception->getCode(), $exception);
        }

        $this->logger->info('Emailing admin URL to admin user ' . $adminUsername . ' at ' . $adminEmail);

        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type:text/html;charset=UTF-8' . "\r\n";
        $headers .= 'From: Magento Cloud <accounts@magento.cloud>' . "\r\n";

        mail(
            $adminEmail,
            'Magento Commerce Cloud - Admin URL',
            $emailContent,
            $headers
        );
        $this->logger->info('Saving email with admin URL: ' . $credentialsFile);

        try {
            $this->file->filePutContents($credentialsFile, $emailContent);
        } catch (FileSystemException $exception) {
            throw new StepException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
