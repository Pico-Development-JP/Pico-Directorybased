<?php
/**
 * Pico Directorty Based Plugin
 * Picoに、ディレクトリベースでサブディレクトリの記事だけをリストアップする、dir_pages変数を追加するプラグイン
 *
 * @author TakamiChie
 * @link http://onpu-tamago.net/
 * @license http://opensource.org/licenses/MIT
 * @version 1.1
 */
class DirectoryBased extends AbstractPicoPlugin {

  protected $enabled = false;

  private $current_url;

  private $content_dir;  

  private $content_ext;

  private $config;
  
  private $pagination_index;
  
  public function onConfigLoaded(array &$config)
  {
    $this->config = array(
      'pagination' => array(
        'enabled'   => false,
        'oncurdir'  => 5,
        'scansubdir'=> true,
      ),
    );
    $this->content_dir = $config['content_dir'];
    $this->content_ext = $config['content_ext'];
    if( isset($config['dir_based']) ) {
      $this->config['pagination'] = $config['dir_based']['pagination'] + $this->config['pagination'];
    }
  }
	
  public function onRequestUrl(&$url)
  {
	  if($this->config['pagination']['enabled']) {
  		// ページネーション対応
      $m = array();
    	if ( preg_match("|^(.*?)/(\d+)$|", $url, $m) ) {
  		  $this->pagination_index = $m[2];
  		  $url = $m[1];
  		}else{
    		$this->pagination_index = 1;
  		}
		  $this->current_url = $this->getBaseUrl() . "/" . $url;
    }
	}

  public function onPageRendering(Twig_Environment &$twig, array &$twigVariables, &$templateName)
  {
		$pages = $twigVariables["pages"];
		$curdir = array();
		$subdir = array();
		$dirmap = array();
		$page_index = 0;
		$page_max = 0;
		$page_hasprev = false;
		$page_prevurl = "";
		$page_hasnext = false;
		$page_nexturl = "";
		$pagination_enabled = false;
		$current_page = $twigVariables["current_page"];

    $p_current = $this->getpagepath($current_page);
    foreach ($pages as $page) {
      $p_page = $this->getpagepath($page);
      if($p_page["name"] == "" && $p_page["fullpath"] == $p_current["fullpath"]) continue;

      $pcp = $p_current["path"];
      $ppp = $p_page["path"];
      $pathc = count($ppp) - count($pcp);
      // ファイルはカレントページと同じディレクトリ？
      if($pathc >= 0){
        $match = true;
        for($i = 0; $i < count($pcp); $i++){
          $match = ($match and ($pcp[$i] == $ppp[$i]));
        }
        if($match){
          $d_name = end($p_page["path"]);
          switch($pathc){
          case 0:
            // 同じディレクトリであれば、カレントディレクトリリストに追加
              $dirmap[$d_name] = count($curdir);
              array_push($curdir, $page);
            break;
          case 1:
            if($this->config['pagination']['scansubdir']){
              // サブディレクトリ
              if($p_page["name"] == ""){
                // サブディレクトリのindexファイルであれば、カレントディレクトリリストに追加
                  $dirmap[$d_name] = count($curdir);
                array_push($curdir, $page);
              }else{
                // そうでなければ、サブディレクトリとして追加
                if(!array_key_exists($d_name, $subdir)){
                  $subdir[$d_name] = array();
                }
                array_push($subdir[$d_name], $page);
              }
            }
            break;
          default:
            // サブサブディレクトリ以降は走査しない
            break;
          }
        }
      }
    }

    // カレントディレクトリのページ判定
    $oncurdir = $this->config['pagination']['oncurdir'];
    if($this->config['pagination']['enabled'] && $oncurdir > 0 && count($curdir) > $oncurdir) {
      // 変数計算
  		$pagination_enabled = true;
      $first = ($this->pagination_index - 1) * $oncurdir;
      $last = $first + $oncurdir;
      $page_hasprev = $this->pagination_index > 1;
      if($page_hasprev) 
        $page_prevurl = $this->current_url . "/" . ($this->pagination_index - 1);
      $page_hasnext = count($curdir) > $last;
      if($page_hasnext) 
        $page_nexturl = $this->current_url . "/" . ($this->pagination_index + 1);
      $page_index = $this->pagination_index;
      $page_max = ceil(count($curdir) / $oncurdir);
      // ディレクトリコンテンツ切り出し
      $curdir = array_slice($curdir, $first, $oncurdir);
    }
    
    // カレントディレクトリとサブディレクトリの結びつけ
    foreach($dirmap as $k => $v){
      if(isset($subdir[$k])){
        $curdir[$v]["subpages"] = $subdir[$k];
      }
    }
    $twigVariables["dir_pages"] = $curdir;
    $twigVariables["dir_paginate"] = array(
      "enabled" => $pagination_enabled,
      "hasprev" => $page_hasprev,
      "prevurl" => $page_prevurl,
      "hasnext" => $page_hasnext,
      "nexturl" => $page_nexturl,
      "pageindex" => $page_index,
      "pagemax" => $page_max
    );
    $twigVariables["pathinfo"] = $p_current["path"];
	}

  /*
   * ページのパスを名前およびパス名に分割
   *
   * @param $page ページデータ
   * @param $testing テスト時のみtrue(ファイルの存在確認を行っているため)
   *
   */
  private function getpagepath($page, $testing = false)
  {
	  $path = substr($page["url"], strlen($this->getBaseUrl()));
    if($testing || file_exists($this->content_dir . $path . $this->content_ext)){
      // これはファイル
    }else{
      // ディレクトリっぽい(Ver1.1で区別がつかなくなったため)
      if($path && $path[strlen($path) - 1] != "/") $path .= "/";
      $path .= "index";
    }
    $p = explode("/", $path);
    $pathes = array();
    $pathes['name'] = array_pop($p); // 最後の項目はファイル名
    if($pathes['name'] == 'index') $pathes['name'] = "";
    $pathes['path'] = $p;
    $pathes['fullpath'] = $path;

	  return $pathes;
	}
	
}

?>
