<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MagentoCloud\Test\Unit\Step;

use Magento\MagentoCloud\App\Logger;
use Magento\MagentoCloud\Config\Validator\Result;
use Magento\MagentoCloud\Config\ValidatorInterface;
use Magento\MagentoCloud\Step\ValidateConfiguration;
use PHPUnit_Framework_MockObject_MockObject as Mock;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class ValidateConfigurationTest extends TestCase
{
    /**
     * @var LoggerInterface|Mock
     */
    private $loggerMock;

    public function setUp()
    {
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
    }

    /**
     * @throws \Magento\MagentoCloud\Step\StepException
     */
    public function testExecuteWithoutValidators()
    {
        $step = new ValidateConfiguration(
            $this->loggerMock,
            []
        );

        $this->loggerMock->expects($this->exactly(2))
            ->method('notice')
            ->withConsecutive(
                ['Validating configuration'],
                ['End of validation']
            );
        $this->loggerMock->expects($this->never())
            ->method('critical');

        $step->execute();
    }

    /**
     * @expectedException \Magento\MagentoCloud\Step\StepException
     * @expectedExceptionMessage Fix configuration with given suggestions
     */
    public function testExecuteWithCriticalError()
    {
        $warningValidator = $this->getMockForAbstractClass(ValidatorInterface::class);
        $warningValidator->expects($this->once())
            ->method('validate');

        $this->loggerMock->expects($this->once())
            ->method('notice')
            ->with('Validating configuration');
        $this->loggerMock->expects($this->never())
            ->method('log');

        $step = new ValidateConfiguration(
            $this->loggerMock,
            [
                ValidatorInterface::LEVEL_CRITICAL => [
                    $this->createValidatorWithError('some error', 'some  suggestion'),
                ],
                ValidatorInterface::LEVEL_WARNING => [
                    $warningValidator,
                ],
            ]
        );
        $step->execute();
    }

    /**
     * @throws \Magento\MagentoCloud\Step\StepException
     */
    public function testExecuteWithWarningMessage()
    {
        $this->loggerMock->expects($this->exactly(2))
            ->method('notice')
            ->withConsecutive(
                ['Validating configuration'],
                ['End of validation']
            );
        $this->loggerMock->expects($this->once())
            ->method('log')
            ->with(
                ValidatorInterface::LEVEL_WARNING,
                'Fix configuration with given suggestions:'
                . PHP_EOL . "- some warning\n  some warning suggestion"
            );

        $step = new ValidateConfiguration(
            $this->loggerMock,
            [
                ValidatorInterface::LEVEL_WARNING => [
                    $this->createValidatorWithError('some warning', 'some warning suggestion'),
                ],
            ]
        );
        $step->execute();
    }

    /**
     * @expectedException \Magento\MagentoCloud\Step\StepException
     * @expectedExceptionMessage Fix configuration with given suggestions
     */
    public function testExecuteWithWarningAndCriticalMessage()
    {
        $this->loggerMock->expects($this->once())
            ->method('notice')
            ->with('Validating configuration');
        $this->loggerMock->expects($this->once())
            ->method('log')
            ->with(
                Logger::WARNING,
                'Fix configuration with given suggestions:'
                . PHP_EOL . '- some warning'
                . PHP_EOL . '  some warning suggestion'
                . PHP_EOL . '- some warning 2'
                . PHP_EOL . '  some warning suggestion 2'
            );

        $step = new ValidateConfiguration(
            $this->loggerMock,
            [
                ValidatorInterface::LEVEL_CRITICAL => [
                    $this->createValidatorWithError('Critical error', 'some critical suggestion'),
                ],
                ValidatorInterface::LEVEL_WARNING => [
                    $this->createValidatorWithError('some warning', 'some warning suggestion'),
                    $this->createValidatorWithError('some warning 2', 'some warning suggestion 2'),
                ],
            ]
        );
        $step->execute();
    }

    /**
     * @param string $error
     * @param string $suggestion
     * @return \PHPUnit\Framework\MockObject\MockObject|ValidatorInterface
     */
    private function createValidatorWithError(string $error, string $suggestion)
    {
        $warningValidator = $this->getMockForAbstractClass(ValidatorInterface::class);
        $warningResultMock = $this->createMock(Result\Error::class);

        $warningResultMock->expects($this->once())
            ->method('getError')
            ->willReturn($error);
        $warningResultMock->expects($this->once())
            ->method('getSuggestion')
            ->willReturn($suggestion);
        $warningValidator->expects($this->once())
            ->method('validate')
            ->willReturn($warningResultMock);

        return $warningValidator;
    }
}
