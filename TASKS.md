# Tasks

## GENERAL

- [x] `$HOME/.phpscff` フォルダに追加したテンプレート等を保存する
- [x] `$HOME/.phpscff/templates` フォルダにあるファイルをFileオブジェクトにして読み込む
- [x] ホームフォルダの設定フォルダの作成・読み込みを行うクラスを作成
- [x] テンプレートを読み込むクラスを作成
- [x] `Directory::list` メソッド作り、FileまたはDirectoryオブジェクトの配列で返す
- [x] `FileSystemInterface` を作り、getPath・isDir・isFileメソッドを持つようにする
- [x] `AbstractFileSystem` を作り、共通メソッドを実装、FileとDirectoryに継承させる

## DESIGN

- [x] 設定フォルダの管理やテンプレートとは責務を分離したDirectory・Fileクラスを作成
- [x] TemplateクラスはFileオブジェクトをコンストラクタの引数にとるようにし、ファイル操作はFileオブジェクトでやる
- [x] Pathクラスを作成してパスの作成・取得の責務を分離
- [x] Directory・FileクラスはPathオブジェクトを引数にとる
