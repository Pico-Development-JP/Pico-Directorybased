# pico-directorybased
Picoでカレントディレクトリの記事を表示する変数を追加するプラグイン

## テンプレートに追加する値
なし
 
##  追加するTwig変数
 * dir_pages:カレントディレクトリのページリスト
  * supages:もし、その記事がサブディレクトリのファイルであれば、サブディレクトリの記事が格納される
 * dir_paginate:ページネーション処理に関する情報(配列)
  * enabled:ページネーションが有効ならtrue
  * hasprev:前ページがあればtrue
  * prevurl:前ページのURL(httpから)
  * hasnext:次ページがあればtrue
  * nexturl:次ページのURL(httpから)
  * pageindex:現在表示しているページのインデックス
  * pagemax:カレントディレクトリのページ数
 * pathinfo:現在のページのディレクトリ名をひとつひとつ分割した配列

##  コンフィグオプション
 * $config['dir_based']['pagination']['enabled']:ページネーション処理を行うかどうか。初期値はfalse
 * $config['dir_based']['pagination']['oncurdir']:カレントディレクトリのページネーションにおいて、1ページに表示する記事数。初期値は5
