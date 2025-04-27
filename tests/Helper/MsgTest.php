<?php

namespace Tests\PhpScff\Helper;

use PHPUnit\Framework\TestCase;
use Hytmng\PhpScff\Helper\Msg;

class MsgTest extends TestCase
{
    public function testMakeTemplateMsg(): void
    {
        // テンプレートのみのケース
        $result = Msg::makeTemplateMsg(Msg::FLG_ADDED, 'test.php', null);
        $this->assertEquals('Template "test.php" added.', $result);

        // グループ付きのケース
        $result = Msg::makeTemplateMsg(Msg::FLG_ADDED, 'test.php', 'testGroup');
        $this->assertEquals('Template "test.php" added in group "testGroup".', $result);

        // 色付きのケース
        $result = Msg::makeTemplateMsg(
            Msg::FLG_ADDED,
            'test.php',
            'testGroup',
            'green',
            ['bold'],
            'blue',
            []
        );
        $this->assertEquals(
            'Template "<fg=green;options=bold>test.php</>" added in group "<fg=blue>testGroup</>".',
            $result
        );
    }

    public function testQuote(): void
    {
        $result = Msg::quote('test');
        $this->assertEquals('"test"', $result);
    }

    public function testGetFlgMsg(): void
    {
        $this->assertEquals('not found', Msg::getFlgMsg(Msg::FLG_NOT_FOUND));
        $this->assertEquals('added', Msg::getFlgMsg(Msg::FLG_ADDED));
        $this->assertEquals('already exists', Msg::getFlgMsg(Msg::FLG_ALREADY_EXISTS));
        $this->assertEquals('', Msg::getFlgMsg(999)); // 未定義の値
    }

    public function testStyle(): void
    {
        // 色なしのケース
        $result = Msg::style('test');
        $this->assertEquals('test', $result);

        // 色のみのケース
        $result = Msg::style('test', 'green');
        $this->assertEquals('<fg=green>test</>', $result);

        // 色とオプション1つのケース
        $result = Msg::style('test', 'green', ['bold']);
        $this->assertEquals('<fg=green;options=bold>test</>', $result);

        // 色と複数オプションのケース（2つ以上）
        $result = Msg::style('test', 'green', ['bold', 'italic']);
        $this->assertEquals('<fg=green;options=bold,italic>test</>', $result);
    }
}
