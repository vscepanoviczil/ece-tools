<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MagentoCloud\Test\Unit\Process\PostDeploy;

use Magento\MagentoCloud\Filesystem\Flag\Manager;
use Magento\MagentoCloud\Step\PostDeploy\DeployFailed;
use Magento\MagentoCloud\Step\StepException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @inheritDoc
 */
class DeployFailedTest extends TestCase
{
    /**
     * @var DeployFailed
     */
    private $process;

    /**
     * @var Manager|MockObject
     */
    private $flagManagerMock;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->flagManagerMock = $this->createMock(Manager::class);

        $this->process = new DeployFailed(
            $this->flagManagerMock
        );
    }

    /**
     * @throws StepException
     */
    public function testExecute(): void
    {
        $this->process->execute();
    }

    /**
     * @throws StepException
     * @expectedException \Magento\MagentoCloud\Step\StepException
     * @expectedExceptionMessage Post-deploy is skipped because deploy was failed.
     */
    public function testExecuteToBeFailed(): void
    {
        $this->flagManagerMock->expects($this->once())
            ->method('exists')
            ->with(Manager::FLAG_DEPLOY_HOOK_IS_FAILED)
            ->willReturn(true);

        $this->process->execute();
    }
}
