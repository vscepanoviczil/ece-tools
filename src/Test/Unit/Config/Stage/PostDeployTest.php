<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MagentoCloud\Test\Unit\Config\Stage;

use Magento\MagentoCloud\Config\Schema;
use Magento\MagentoCloud\Config\Stage\PostDeploy;
use Magento\MagentoCloud\Config\Stage\PostDeployInterface;
use Magento\MagentoCloud\Config\StageConfigInterface;
use PHPUnit\Framework\TestCase;
use Magento\MagentoCloud\Config\Environment\Reader as EnvironmentReader;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * @inheritdoc
 */
class PostDeployTest extends TestCase
{
    /**
     * @var PostDeploy
     */
    private $config;

    /**
     * @var EnvironmentReader|Mock
     */
    private $environmentReaderMock;

    /**
     * @var Schema|Mock
     */
    private $schemaMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->environmentReaderMock = $this->createMock(EnvironmentReader::class);
        $this->schemaMock = $this->createMock(Schema::class);
        $this->schemaMock->expects($this->any())
            ->method('getDefaults')
            ->with(StageConfigInterface::STAGE_POST_DEPLOY)
            ->willReturn([
                PostDeployInterface::VAR_WARM_UP_PAGES => ['index.php']
            ]);

        $this->config = new PostDeploy(
            $this->environmentReaderMock,
            $this->schemaMock
        );
    }

    /**
     * @param string $name
     * @param array $envConfig
     * @param mixed $expectedValue
     * @dataProvider getDataProvider
     */
    public function testGet(string $name, array $envConfig, $expectedValue)
    {
        $this->environmentReaderMock->expects($this->any())
            ->method('read')
            ->willReturn([PostDeploy::SECTION_STAGE => $envConfig]);

        $this->assertSame($expectedValue, $this->config->get($name));
    }

    /**
     * @return array
     */
    public function getDataProvider(): array
    {
        return [
            'default pages' => [
                PostDeploy::VAR_WARM_UP_PAGES,
                [],
                [
                    'index.php',
                ],
            ],
            'custom pages' => [
                PostDeploy::VAR_WARM_UP_PAGES,
                [
                    PostDeploy::STAGE_POST_DEPLOY => [
                        PostDeploy::VAR_WARM_UP_PAGES => [
                            'index.php/custom',
                        ],
                    ],
                ],
                [
                    'index.php/custom',
                ],
            ],
        ];
    }

    public function testNotExists()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Config NOT_EXISTS_VALUE was not defined.');

        $this->environmentReaderMock->expects($this->never())
            ->method('read')
            ->willReturn([]);

        $this->config->get('NOT_EXISTS_VALUE');
    }

    public function testCannotMerge()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Some error');

        $this->environmentReaderMock->expects($this->once())
            ->method('read')
            ->willThrowException(new \Exception('Some error'));

        $this->config->get(PostDeploy::VAR_WARM_UP_PAGES);
    }
}
