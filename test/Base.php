<?php 
if (php_sapi_name() != 'cli') return;
require_once(__DIR__."/../../../lib/test.php");
require_once(__DIR__."/../directorybased.php");

class DirectoryBasedTestBase extends PHPUnit_Framework_TestCase {
  public function setUp() {
    $this->pico = $GLOBALS['pico'];
  }

  /**
   * テスト用のDirectoryBasedオブジェクトを取得する
   */
  public function getTest($paginate = false, $oncurdir = 5, $scansubdir = true) {
    $test = new DirectoryBased($this->pico);
    $config = $this->pico->getConfig();
    $config['dir_based'] = array('pagination' => 
      array(
        'enabled' => $paginate, 
        'oncurdir' => $oncurdir,
        'scansubdir' => $scansubdir
      ), 
    );
    $test->onConfigLoaded($config);
    return $test;
  }
  
  /**
   * 警告を出さないためのダミーテスト
   */
  public function test(){
    $this->assertNull(null);
  }
};

?>