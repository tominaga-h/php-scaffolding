# 開発コマンド一覧

## Docker操作（Makefile経由）
```bash
make up          # Docker起動
make down        # Docker停止（orphanコンテナも削除）
make bash        # Dockerコンテナにbashでアクセス
make install     # composer install実行
make test        # テスト実行
make php         # PHPインタラクティブシェル起動
make push        # テスト実行後にgit push
```

## Composer コマンド（コンテナ内）
```bash
composer test              # PHPUnitテスト実行
composer upload-coverage   # カバレッジレポートをアップロード
```

## アプリケーション実行
```bash
php bin/console            # CLIアプリケーション起動
```

## ユーティリティコマンド（macOS/Darwin）
```bash
ls -la                     # ファイル一覧
cd <dir>                   # ディレクトリ移動
grep -r "pattern" .        # パターン検索
find . -name "*.php"       # ファイル検索
```
