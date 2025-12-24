# コードベース構造

```
php-scaffolding/
├── bin/
│   └── console           # CLIエントリポイント
├── src/PhpScff/
│   ├── Application.php   # Symfonyアプリケーション
│   ├── Kernel.php        # カーネル
│   ├── Template.php      # テンプレートクラス
│   ├── Group.php         # グループクラス
│   ├── Command/          # CLIコマンド
│   │   ├── TemplateAddCommand.php
│   │   ├── TemplateEditCommand.php
│   │   ├── GroupConfigCommand.php
│   │   ├── GroupListCommand.php
│   │   ├── GroupRenameCommand.php
│   │   └── GroupTreeCommand.php
│   ├── Config/           # 設定関連
│   │   ├── ConfigStorage.php
│   │   └── PathResolver.php
│   ├── FileSystem/       # ファイル・ディレクトリ操作
│   │   ├── File.php
│   │   ├── Directory.php
│   │   ├── Path.php
│   │   ├── AbstractFileSystem.php
│   │   ├── FileSystemInterface.php
│   │   └── PathTrait.php
│   ├── Tree/             # ツリー構造パース
│   │   ├── StructureParser.php
│   │   ├── TreeNode.php
│   │   └── TreeEntry.php
│   ├── Service/          # サービス
│   │   └── TwigService.php
│   ├── Helper/           # ヘルパー
│   │   ├── Helper.php
│   │   ├── Msg.php
│   │   └── Filter.php
│   ├── Exception/        # 例外
│   │   ├── ExistenceException.php
│   │   └── ProcessException.php
│   ├── Process/          # プロセス
│   │   └── EditProcess.php
│   └── Resource/         # リソース
│       └── template/
├── tests/                # テスト（srcと同じ構造）
├── config/               # 設定ファイル
├── var/                  # 一時ファイル（カバレッジなど）
├── composer.json
├── phpunit.xml
├── Makefile
├── Dockerfile
└── docker-compose.yml
```

## 主要クラスの責務

- **Kernel**: アプリケーションの起動と設定管理
- **Application**: Symfonyコンソールアプリケーション
- **ConfigStorage**: 設定フォルダ（.phpscff）の管理
- **Template**: テンプレートファイルの読み書き
- **Group**: グループ設定の管理
- **File/Directory**: ファイルシステム操作の抽象化
- **Path**: パス操作のユーティリティ
- **StructureParser**: ツリー構造のパース
