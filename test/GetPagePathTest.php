<?php 
if (php_sapi_name() != 'cli') return;
require_once("Base.php");

class DirectoryBasedGPPTest extends DirectoryBasedTestBase {
  /** 
   * 当該メソッドが、引数に指定したパスを分割することを確認する
   *
   * 状態:引数がファイルパス
   */
  public function testGetpagepathFilePath(){
    Closure::bind(function() {
      $test = $this->getTest(true);
      $page = $this->getTestPage("/file/path/testing");
      $result = $test->getpagepath($page);
      $this->assertEquals("testing", $result["name"], "name");
      $this->assertEquals("/file/path", implode("/", $result["path"]), "path");
      $this->assertEquals("/file/path/testing", $result["fullpath"], "fullpath");
    }, $this, 'DirectoryBased')->__invoke();    
  }

  /** 
   * 当該メソッドが、引数に指定したパスを分割することを確認する
   *
   * 状態:引数がディレクトリパス
   */
  public function testGetpagepathDirectoryPath(){
    Closure::bind(function() {
      $test = $this->getTest(true);
      $page = $this->getTestPage("/file/path/testing/");
      $result = $test->getpagepath($page);
      $this->assertEquals("", $result["name"], "name");
      $this->assertEquals("/file/path/testing", implode("/", $result["path"]), "path");
      $this->assertEquals("/file/path/testing/", $result["fullpath"], "fullpath");
    }, $this, 'DirectoryBased')->__invoke();    
  }

  /** 
   * 当該メソッドが、引数に指定したパスを分割することを確認する
   *
   * 状態:引数がindexファイルを示すパス
   */
  public function testGetpagepathIndexPath(){
    Closure::bind(function() {
      $test = $this->getTest(true);
      $page = $this->getTestPage("/file/path/testing/index");
      $result = $test->getpagepath($page);
      $this->assertEquals("", $result["name"], "name");
      $this->assertEquals("/file/path/testing", implode("/", $result["path"]), "path");
      $this->assertEquals("/file/path/testing/index", $result["fullpath"], "fullpath");
    }, $this, 'DirectoryBased')->__invoke();    
  }
  
  public function getTestPage($url){
    return array('url' => $this->pico->getBaseUrl() . $url);
  }
};

?>