<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MagentoCloud\Test\Unit\Step\Build;

use Magento\MagentoCloud\Config\Module;
use Magento\MagentoCloud\Step\Build\RefreshModules;
use Magento\MagentoCloud\Step\StepInterface;
use Magento\MagentoCloud\Shell\ShellException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class RefreshModulesTest extends TestCase
{
    /**
     * @var StepInterface
     */
    private $step;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var Module|MockObject
     */
    private $configMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->configMock = $this->createMock(Module::class);

        $this->step = new RefreshModules(
            $this->loggerMock,
            $this->configMock
        );
    }

    public function testExecute()
    {
        $this->loggerMock->expects($this->exactly(2))
            ->method('notice')
            ->withConsecutive(
                ['Reconciling installed modules with shared config.'],
                ['End of reconciling modules.']
            );
        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with('The following modules have been enabled:' . PHP_EOL . 'module1' . PHP_EOL . 'module2');
        $this->configMock->expects($this->once())
            ->method('refresh')
            ->willReturn(['module1', 'module2']);

        $this->step->execute();
    }

    public function testExecuteNoModulesChanged()
    {
        $this->loggerMock->expects($this->exactly(2))
            ->method('notice')
            ->withConsecutive(
                ['Reconciling installed modules with shared config.'],
                ['End of reconciling modules.']
            );
        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with('No modules were changed.');
        $this->configMock->expects($this->once())
            ->method('refresh')
            ->willReturn([]);

        $this->step->execute();
    }

    /**
     * @expectedException \Magento\MagentoCloud\Step\StepException
     * @expectedExceptionMessage some error
     */
    public function testExecuteWithException()
    {
        $this->configMock->expects($this->once())
            ->method('refresh')
            ->willThrowException(new ShellException('some error'));

        $this->step->execute();
    }
}
