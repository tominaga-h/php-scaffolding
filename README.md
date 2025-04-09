# php-scaffolding

![開発中です](https://img.shields.io/badge/UNDER%20DEVELOPMENT%20:%20%E9%96%8B%E7%99%BA%E4%B8%AD%E3%81%A7%E3%81%99-red?style=for-the-badge)

PHPで実装したスカフォールディングツール

## 機能

1. `add` コマンドでファイルを登録する
2. `edit` コマンドで指定したファイルを開き、雛形作成時にパラメータ化したいところを編集する
3. `group` コマンドでフォルダ構造や使うファイルを設定するyamlファイルを編集する
4. `new` コマンドで指定したディレクトリにプロジェクトの雛形を構築

## 設定フォルダ

`$HOME/.phpscff` に設定フォルダを作成します。
登録したテンプレート、グループ設定を保存します。

- `.phpscff/templates` テンプレート保存場所
- `.phpscff/groups` グループ設定yaml保存場所
