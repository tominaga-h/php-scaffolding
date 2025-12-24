# コードスタイル・コンベンション

## インデント
- **タブ**を使用（スペースではない）

## 命名規則
- クラス: PascalCase（例: `ConfigStorage`, `TreeNode`）
- メソッド: camelCase（例: `fromPath`, `getFilename`）
- 変数: camelCase
- 定数: UPPER_SNAKE_CASE

## ドキュメンテーション
- PHPDoc形式のコメントを使用
- **日本語**でコメントを記述
- 例:
```php
/**
 * ファイル名を取得する
 *
 * @return string
 */
public function getFilename(): string
```

## 型宣言
- 引数と戻り値に型宣言を使用
- 例: `public static function fromPath(Path $path): self`

## オートロード
- PSR-4準拠
- 名前空間: `Hytmng\PhpScff`

## ファイル構成
- 1ファイル1クラス
- クラス名とファイル名を一致させる
- インターフェースは `Interface` サフィックス
- 抽象クラスは `Abstract` プレフィックス
- トレイトは `Trait` サフィックス

## テスト
- テストクラスは `Test` サフィックス
- ソース構造を tests/ にミラーリング
