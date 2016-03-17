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

  private $content_dir;

  private $base_url;
  
  private $current_url;
  
  private $config;
  
  private $pagination_index;
  
  public function onConfigLoaded(array &$config)
  {
    $this->base_url = $config['base_url'];
    $this->content_dir = $config['content_dir'];
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
  		$checkurl = $url;
  		while ( !$this->url_exists($checkurl) ) {
  		  $checkurl = dirname($checkurl);
  		}
  		$differ = substr( $url, strlen($checkurl) + 1 );
    	if ( $differ != "" && preg_match("/^\d+$/", $differ) ) {
  		  $this->pagination_index = $differ;
  		  $url = $checkurl;
  		}else{
    		$this->pagination_index = 1;
  		}
		  $this->current_url = $this->base_url . "/" . $url;
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
          var_dump($p_page);
          $d_name = end($p_page["path"]);
          var_dump($d_name);
          var_dump($pathc);
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
   *
   */
  private function getpagepath($page)
  {
	  $path = substr($page["url"], strlen($this->base_url));
	  $p = explode("/", $path);
	  $pathes = array();
	  $pathes['name'] = array_pop($p); // 最後の項目はファイル名
    if($pathes['name'] == 'index') $pathes['name'] = "";
	  $pathes['path'] = $p;
	  $pathes['fullpath'] = $path;
	  return $pathes;
	}

  /*
   * URLが実在するURLかどうかを確認する(Picoソースよりコピー)
   *
   * @param $url URL
   *
   */
  private function url_exists($url)
  {
    $file = "";
		if($url) $file = $this->content_dir . $url;
		else $file = $this->content_dir .'index';

		if(is_dir($file)) $file = $this->content_dir . $url .'/index'. CONTENT_EXT;
		else $file .= CONTENT_EXT;
		
		return file_exists($file);
  }
	
}

?>
