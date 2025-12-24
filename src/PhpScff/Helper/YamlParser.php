<?php

namespace Hytmng\PhpScff\Helper;

use Symfony\Component\Yaml\Parser;

class YamlParser
{
	/**
	 * YAMLファイルをパースして配列を返す
	 *
	 * @param string $filename パースするYAMLファイルのパス
	 * @return array<string, mixed>
	 */
	public static function parseFile(string $filename): array
	{
		$parser = new Parser();
		return $parser->parseFile($filename);
	}
}

