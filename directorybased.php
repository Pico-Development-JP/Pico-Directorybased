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
  
  private $config;
  
  private $p_index;
  
  public function onConfigLoaded(array &$config)
  {
    $this->config = array(
      'pagination' => array(
        'enabled'   => false,
        'oncurdir'  => 5,
        'scansubdir'=> true,
      ),
    );
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
  		} else if ( preg_match("|^(.*?)(index)?$|", $url, $m)){
    		$this->pagination_index = 1;
  		  $url = $m[1];
  		} else {
        throw new Exception("Invalid URL");
      }
		  $this->current_url = $this->getBaseUrl() . "/" . $url;
    }
	}

  public function onPageRendering(Twig_Environment &$twig, array &$twigVariables, &$templateName)
  {
		$pages = $twigVariables["pages"];
		$current_page = $twigVariables["current_page"];
    $curdir = $this->scanFiles($pages, $current_page);
    $p = $this->scanPagination($curdir);
    $twigVariables["dir_pages"] = $curdir;
    if($p != null){
      $twigVariables["dir_paginate"] = array(
        "enabled" => $p["pagination_enabled"],
        "hasprev" => $p["page_hasprev"],
        "prevurl" => $p["page_prevurl"],
        "hasnext" => $p["page_hasnext"],
        "nexturl" => $p["page_nexturl"],
        "pageindex" => $p["page_index"],
        "pagemax" => $p["page_max"]
      );
    }
    $twigVariables["pathinfo"] = $this->getpagepath($current_page);
	}

  /**
   * ファイルを走査し、
   * カレントディレクトリおよびサブディレクトリのファイル一覧を得る
   *
   * @param pages ページ一覧
   * @param current_page 現在のページを示す配列
   */
  private function scanFiles(&$pages, &$current_page) {
    $curdir = array();
		$subdir = array();
		$dirmap = array();
    $p_current = $this->getpagepath($current_page);
    foreach ($pages as $page) {
      $p_page = $this->getpagepath($page);
      if($p_page["name"] == "" && 
        $p_page["fullpath"] == $p_current["fullpath"]) 
        continue;

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
            // サブディレクトリ
            if($p_page["name"] == ""){
              // サブディレクトリのindexファイルであれば、カレントディレクトリリストに追加
                $dirmap[$d_name] = count($curdir);
              array_push($curdir, $page);
            }else if($this->config['pagination']['scansubdir']){
              // そうでなければ、サブディレクトリとして追加
              if(!array_key_exists($d_name, $subdir)){
                $subdir[$d_name] = array();
              }
              array_push($subdir[$d_name], $page);
            }
            break;
          default:
            // サブサブディレクトリ以降は走査しない
            break;
          }
        }
      }
    }

    // カレントディレクトリとサブディレクトリの結びつけ
    foreach($dirmap as $k => $v){
      if(isset($subdir[$k])){
        $curdir[$v]["subpages"] = $subdir[$k];
      }
    }  
    return $curdir;  
  }
  
  /**
   * ページネーション処理を行う
   * 
   * @param curdir カレントディレクトリデータ配列(scanFiles()戻り値)
   * @return ページネーション処理に関するデータを含む配列
   */
  public function scanPagination(&$curdir) {
    // カレントディレクトリのページ判定
    $result = null;
    $oncurdir = $this->config['pagination']['oncurdir'];
    if($this->config['pagination']['enabled'] && $oncurdir > 0 && count($curdir) > $oncurdir) {
      $result = array();
      // 変数計算
  		$result["pagination_enabled"] = true;
      $first = ($this->pagination_index - 1) * $oncurdir;
      $last = $first + $oncurdir;
      $result["page_hasprev"] = $this->pagination_index > 1;
      if($result["page_hasprev"]) { 
        $result["page_prevurl"] = $this->current_url . "/" 
          . ($this->pagination_index - 1);
      } else {
        $result["page_prevurl"] = "";
      }
      $result["page_hasnext"] = count($curdir) > $last;
      if($result["page_hasnext"]) { 
        $result["page_nexturl"] = $this->current_url . "/" 
          . ($this->pagination_index + 1);
      } else {
        $result["page_nexturl"] = "";
      }
      $result["page_index"] = $this->pagination_index;
      $result["page_max"] = ceil(count($curdir) / $oncurdir);
      // ディレクトリコンテンツ切り出し
      $curdir = array_slice($curdir, $first, $oncurdir);
    }
    return $result;
  }
  
  /*
   * ページのパスを名前およびパス名に分割
   *
   * @param $page ページデータ
   *
   */
  private function getpagepath($page)
  {
	  $path = substr($page["url"], strlen($this->getBaseUrl()));
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
