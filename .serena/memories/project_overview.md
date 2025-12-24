# php-scaffolding プロジェクト概要

## 説明
PHPで実装したスカフォールディングツール。テンプレートからプロジェクトの雛形を作成する。

## 主な機能
1. `add` コマンド: ファイルをテンプレートとして登録
2. `edit` コマンド: 登録したテンプレートを編集（パラメータ化）
3. `group` コマンド: フォルダ構造やファイルグループを設定
4. `new` コマンド: 指定したディレクトリにプロジェクト雛形を構築（未実装）

## 設定フォルダ
- `$HOME/.phpscff/templates`: テンプレート保存場所
- `$HOME/.phpscff/groups`: グループ設定yaml保存場所

## テクノロジースタック
- **PHP**: メイン言語
- **Symfony Console**: CLIコマンド
- **Symfony Filesystem**: ファイル操作
- **Symfony DI**: 依存性注入
- **Twig**: テンプレートエンジン
- **PHPUnit 12**: テストフレームワーク
- **Docker**: 開発環境

## 名前空間
- メイン: `Hytmng\PhpScff`
- テスト: `Tests`
