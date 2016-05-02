<?php 
if (php_sapi_name() != 'cli') return;
require_once("Base.php");

class DirectoryBasedRUTest extends DirectoryBasedTestBase {
  /**
   * 以下の状態で、カレントURL及びページインデックスの値を確認する
   *
   * URL:ページネーションインデックスを含まない
   * カレントURL:そのまま
   * ページインデックス:1
   */
  public function testOnRU_NoPaginationURL() {
    Closure::bind(function() {
      $test = $this->getTest(true);
      $url = "testurl/has/no/paginate/";
      $test->onRequestUrl($url);
      $this->assertRegExp("|${url}$|", $test->current_url);
      $this->assertEquals(1, $test->pagination_index);
    }, $this, 'DirectoryBased')->__invoke();
  }
  
  /**
   * 以下の状態で、カレントURL及びページインデックスの値を確認する
   *
   * URL:ページネーションインデックスを含む。インデックス1
   * カレントURL:インデックスが削除された値
   * ページインデックス:1
   */
  public function testOnRU_Pagination1() {
    Closure::bind(function() {
      $test = $this->getTest(true);
      $testurl = "testurl/has/no/paginate";
      $url = "${testurl}/1";
      $test->onRequestUrl($url);
      $this->assertRegExp("|${testurl}$|", $test->current_url);
      $this->assertEquals(1, $test->pagination_index);
    }, $this, 'DirectoryBased')->__invoke();
  }
  
  /**
   * 以下の状態で、カレントURL及びページインデックスの値を確認する
   *
   * URL:ページネーションインデックスを含む。インデックス1
   * カレントURL:インデックスが削除された値
   * ページインデックス:10
   */
  public function testOnRU_Pagination10() {
    Closure::bind(function() {
      $test = $this->getTest(true);
      $testurl = "testurl/has/no/paginate";
      $url = "${testurl}/10";
      $test->onRequestUrl($url);
      $this->assertRegExp("|${testurl}$|", $test->current_url);
      $this->assertEquals(10, $test->pagination_index);
    }, $this, 'DirectoryBased')->__invoke();
  }
  
  /**
   * 以下の状態で、カレントURL及びページインデックスの値を確認する
   *
   * URL:ページネーションインデックスを含まないが、最後のディレクトリ名に数字を含む
   * カレントURL:そのまま
   * ページインデックス:1
   */
  public function testOnRU_NoPaginationAndIncludeNumber() {
    Closure::bind(function() {
      $test = $this->getTest(true);
      $url = "testurl/has/no/paginate/1/";
      $test->onRequestUrl($url);
      $this->assertRegExp("|${url}$|", $test->current_url);
      $this->assertEquals(1, $test->pagination_index);
    }, $this, 'DirectoryBased')->__invoke();
  }
};

?>