<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Hytmng\PhpScff\Kernel;
use Hytmng\PhpScff\Application;
use Hytmng\PhpScff\Config\ConfigStorage;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Console\Command\Command;

class KernelTest extends TestCase
{
    private Kernel $kernel;

    protected function setUp(): void
    {
        $this->kernel = new Kernel();
    }

    public function testGetContainer(): void
    {
        $container = $this->kernel->getContainer();
        $this->assertInstanceOf(ContainerBuilder::class, $container);
    }

    public function testSetAndGetApplication(): void
    {
        $application = new Application();
        $this->kernel->setApplication($application);

        $this->assertSame($application, $this->kernel->getApplication());
    }

    public function testSetAndGetConfigStorage(): void
    {
        $configStorage = $this->createMock(ConfigStorage::class);
        $this->kernel->setConfigStorage($configStorage);

        $this->assertSame($configStorage, $this->kernel->getConfigStorage());
    }

    public function testGetCommands(): void
    {
        $commands = $this->kernel->getCommands();
        $this->assertIsArray($commands);

        // サービス定義によって実際のコマンド数は変わりますが、
        // 少なくとも配列が返ってくることを確認
        foreach ($commands as $command) {
            $this->assertInstanceOf(Command::class, $command);
        }
    }

    public function testRun(): void
    {
        $configStorage = $this->createMock(ConfigStorage::class);
        $configStorage->expects($this->once())
            ->method('exists')
            ->willReturn(false);

        $configStorage->expects($this->once())
            ->method('create');

        $application = $this->createMock(Application::class);
        $application->expects($this->once())
            ->method('addCommands')
            ->with($this->callback(function($commands) {
                return is_array($commands);
            }));

        $application->expects($this->once())
            ->method('setConfigStorage')
            ->with($configStorage);

        $application->expects($this->once())
            ->method('run')
            ->willReturn(0);

        $this->kernel->setConfigStorage($configStorage);
        $this->kernel->setApplication($application);

        $result = $this->kernel->run();
        $this->assertEquals(0, $result);
    }
}
