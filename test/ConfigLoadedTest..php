<?php 
if (php_sapi_name() != 'cli') return;
require_once("Base.php");

class DirectoryBasedCLTest extends DirectoryBasedTestBase {
  /**
   * 以下の状態で、値がセットされていることを確認する
   *
   * 状態：コンフィグファイルに値が設定されていない
   * 期待値：設定されている値が、全てプラグインの初期設定と同等
   */
  public function testOnCL_SetDefault() {
    Closure::bind(function() {
      $config = array();
      $test = new DirectoryBased($this->pico);
      $test->onConfigLoaded($config);
      $p = $test->config['pagination'];
      $this->assertEquals(false, $p['enabled']);
      $this->assertEquals(5, $p['oncurdir']);
      $this->assertEquals(true, $p['scansubdir']);
    }, $this, 'DirectoryBased')->__invoke();
  }

  /**
   * 以下の状態で、値がセットされていることを確認する
   *
   * 状態：コンフィグファイルにpagination/enabledのみ設定
   * 期待値：pagination/enabled以外の値が、全てプラグインの初期設定と同等
   */
  public function testOnCL_SetEnabledOnly() {
    Closure::bind(function() {
      $config = array();
      $config['dir_based'] = array('pagination' => array('enabled' => true, ), );
      $test = new DirectoryBased($this->pico);
      $test->onConfigLoaded($config);
      $p = $test->config['pagination'];
      $this->assertEquals(true, $p['enabled']);
      $this->assertEquals(5, $p['oncurdir']);
      $this->assertEquals(true, $p['scansubdir']);
    }, $this, 'DirectoryBased')->__invoke();
  }

  /**
   * 以下の状態で、値がセットされていることを確認する
   *
   * 状態：コンフィグファイルにpagination/*すべて設定
   * 期待値：pagination/*すべての値が、設定した値と同等
   */
  public function testOnCL_SetAll() {
    Closure::bind(function() {
      $config = array();
      $config['dir_based'] = array('pagination' => 
        array(
          'enabled' => true, 
          'oncurdir' => 99, 
          'scansubdir' => false
        ), 
      );
      $test = new DirectoryBased($this->pico);
      $test->onConfigLoaded($config);
      $p = $test->config['pagination'];
      $this->assertEquals(true, $p['enabled']);
      $this->assertEquals(99, $p['oncurdir']);
      $this->assertEquals(false, $p['scansubdir']);
    }, $this, 'DirectoryBased')->__invoke();
  }
  
};

?>