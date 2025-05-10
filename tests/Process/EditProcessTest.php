<?php

namespace Tests\Process;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Hytmng\PhpScff\Process\EditProcess;
use Hytmng\PhpScff\Exception\ProcessException;
use Symfony\Component\Process\Process;

class EditProcessTest extends TestCase
{
    private ?string $originalEditor;
    private Process&MockObject $processMock;
    private EditProcess&MockObject $editProcess;

    protected function setUp(): void
    {
        // EDITORの環境変数をバックアップ
        $this->originalEditor = $_SERVER['EDITOR'] ?? null;

        // Processクラスのモックを作成
        $this->processMock = $this->getMockBuilder(Process::class)
            ->disableOriginalConstructor()
            ->getMock();

        // EditProcessクラスのモックを作成
        $this->editProcess = $this->getMockBuilder(EditProcess::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createProcess'])
            ->getMock();

        $this->editProcess->method('createProcess')
            ->willReturn($this->processMock);
    }

    protected function tearDown(): void
    {
        // EDITORの環境変数を復元
        if ($this->originalEditor === null) {
            unset($_SERVER['EDITOR']);
        } else {
            $_SERVER['EDITOR'] = $this->originalEditor;
        }
    }

    public function testConstruct_WithValidEditor(): void
    {
        $_SERVER['EDITOR'] = 'vim';

        $this->processMock->expects($this->once())
            ->method('run');
        $this->processMock->expects($this->once())
            ->method('isSuccessful')
            ->willReturn(true);

        $this->editProcess->__construct();
        $this->assertInstanceOf(EditProcess::class, $this->editProcess);
    }

    public function testConstruct_WithInvalidEditor(): void
    {
        $_SERVER['EDITOR'] = 'non_existent_editor';

        $this->processMock->expects($this->once())
            ->method('run');
        $this->processMock->expects($this->once())
            ->method('isSuccessful')
            ->willReturn(false);

        $this->expectException(ProcessException::class);
        $this->expectExceptionMessage('Editor "non_existent_editor" is not found.' . PHP_EOL . 'Please set your editor to the `EDITOR` environment variable.');

        $this->editProcess->__construct();
    }

    public function testConstruct_WithoutEditor(): void
    {
        unset($_SERVER['EDITOR']);

        $this->processMock->expects($this->once())
            ->method('run');
        $this->processMock->expects($this->once())
            ->method('isSuccessful')
            ->willReturn(true);

        $this->editProcess->__construct();
        $this->assertInstanceOf(EditProcess::class, $this->editProcess);
    }

    public function testEdit(): void
    {
        $_SERVER['EDITOR'] = 'vim';
        $filePath = '/path/to/file';

        $this->processMock->expects($this->exactly(2))
            ->method('run');
        $this->processMock->expects($this->exactly(2))
            ->method('isSuccessful')
            ->willReturn(true);
        $this->processMock->expects($this->once())
            ->method('setTty')
            ->with(true);

        $this->editProcess->__construct();
        $result = $this->editProcess->edit($filePath);
        $this->assertTrue($result);
    }

    public function testEditorExists(): void
    {
        $_SERVER['EDITOR'] = 'vim';

        $this->processMock->expects($this->exactly(2))
            ->method('run');
        $this->processMock->expects($this->exactly(2))
            ->method('isSuccessful')
            ->willReturn(true);

        $this->editProcess->__construct();
        $result = $this->editProcess->editorExists();
        $this->assertTrue($result);
    }

    public function testCreateProcess(): void
    {
        // EditProcessのモックを使用せずに実際のインスタンスを作成
        $editProcess = new EditProcess();
        $command = ['test', 'command'];

        $process = $editProcess->createProcess($command);

        $this->assertInstanceOf(Process::class, $process);
    }
}
