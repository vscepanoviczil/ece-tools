<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MagentoCloud\Config\Shared;

use Magento\MagentoCloud\Filesystem\Driver\File;
use Magento\MagentoCloud\Filesystem\FileList;
use Magento\MagentoCloud\Filesystem\Writer\WriterInterface;

/**
 * @inheritdoc
 */
class Writer implements WriterInterface
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var File
     */
    private $file;

    /**
     * @var FileList
     */
    private $fileList;

    /**
     * @param Reader $reader ,
     * @param File $file
     * @param FileList $fileList
     */
    public function __construct(
        Reader $reader,
        File $file,
        FileList $fileList
    ) {
        $this->reader = $reader;
        $this->file = $file;
        $this->fileList = $fileList;
    }

    /**
     * @inheritdoc
     */
    public function create(array $config)
    {
        $updatedConfig = '<?php' . PHP_EOL . 'return ' . var_export($config, true) . ';';

        $this->file->filePutContents($this->fileList->getConfig(), $updatedConfig);
    }

    /**
     * @inheritdoc
     */
    public function update(array $config)
    {
        $updatedConfig = array_replace_recursive($this->reader->read(), $config);

        $this->create($updatedConfig);
    }
}
