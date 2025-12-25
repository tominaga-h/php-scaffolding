# Changelog

このプロジェクトのすべての重要な変更はこのファイルに記録されます。

フォーマットは [Keep a Changelog](https://keepachangelog.com/ja/1.1.0/) に基づいています。

## [1.1.0] - 2025-12-25

### Changed

- 実行ファイル名を `phpscff` に変更
- `group:config` コマンドで登録済みのテンプレートを表示するように改善
- `group:tree` コマンドが各グループの `meta.yaml` の structure を対象にするように変更
- コマンド名を `template:add` / `template:edit` に変更
- `EditProcess` のタイムアウトを解除

### Fixed

- `array_filter` が元の配列のキーを保持することによるテスト失敗を修正
- `ConfigStorage::getTemplate` が null を返す場合の `Filter.php` の処理を修正
- `StructureParser` でディレクトリ構造が undefined の場合にエラーを発生させるように修正

## [1.0.0] - 2025-12-24

### Added

- 初回リリース
- `template:add` - ファイルをテンプレートとして登録
- `template:edit` - テンプレートを編集
- `group:list` - 全グループをリスト表示
- `group:config` - `meta.yaml` ファイルを編集
- `group:rename` - グループ名をリネーム
- `group:tree` - フォルダ構造を tree 形式で表示
- `new` - グループのフォルダ構造をもとにテンプレートを構築
- Twig による変数埋め込み対応（`{{ group }}`、`{{ directory }}`）

