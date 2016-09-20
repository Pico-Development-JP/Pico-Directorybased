<?php 
if (php_sapi_name() != 'cli') return;
require_once("Base.php");

class DirectoryBasedPRTest extends DirectoryBasedTestBase {

  /**
   * scanFiles()メソッドにおいて、以下の条件でページリストが作成されることを確認する
   *
   * ※1:カレントディレクトリ配下のファイルがリストアップされること
   * ※2:サブディレクトリファイルがリストアップされること
   * ※3:サブディレクトリだが、indexがないディレクトリはリストアップされないこと
   * ※4:サブディレクトリでないファイルはリストアップされないこと
   */
  public function testScanFiles() {
    Closure::bind(function() {
      $test = $this->getTest(true);
      $test->config["pagination"]["scansubdir"] = true;
      $page = $this->getPagesData();
      $currentPage = $page[0];
      $result = $test->scanFiles($page, $currentPage);
      $this->assertRegExp("|rootdir/testdir/file$|", $result[0]["url"], "1st file");
      $this->assertRegExp("|rootdir/testdir/subdir/index$|", 
        $result[1]["url"], "2nd file");
      $this->assertRegExp("|rootdir/testdir/subdir/file$|", 
        $result[1]["subpages"][0]["url"], "3rd file");
      $this->assertRegExp("|rootdir/testdir/subdir/file2$|", 
        $result[1]["subpages"][1]["url"], "4th file");
      $this->assertRegExp("|rootdir/testdir/subdir/file3$|", 
        $result[1]["subpages"][2]["url"], "5th file");
      $this->assertRegExp("|rootdir/testdir/subdir2/index$|", 
        $result[2]["url"], "6th file");
      $this->assertRegExp("|rootdir/testdir/subfile$|", 
        $result[3]["url"], "7th file");
      $this->assertEquals(4, count($result), "CurDir Count");
    }, $this, 'DirectoryBased')->__invoke();
  }

  /**
   * scanFiles()メソッドにおいて、以下の条件でページリストが作成されることを確認する
   *
   * scansubdirオプションがfalse
   */
  public function testScanFilesIfNoScanSubdir() {
    Closure::bind(function() {
      $test = $this->getTest(true);
      $test->config["pagination"]["scansubdir"] = false;
      $page = $this->getPagesData();
      $currentPage = $page[0];
      $result = $test->scanFiles($page, $currentPage);
      $this->assertRegExp("|rootdir/testdir/file$|", $result[0]["url"], "1st file");
      $this->assertRegExp("|rootdir/testdir/subfile$|", 
        $result[1]["url"], "2nd file");
      $this->assertEquals(2, count($result), "CurDir Count");
    }, $this, 'DirectoryBased')->__invoke();
  }
  
  /**
   * scanPagination()メソッドにおいて、ページリストが作成されることを確認する
   *
   * カレントURL:ページインデックスを含まない
   */
  public function testScanPaginationNoPage() {
    Closure::bind(function() {
      $test = $this->getTest(true);
      $page = $this->getPagesData();
      $currentPage = $page[2];
      $url = $this->pico->getBaseUrl() . "rootdir/testdir/subdir/";
      $test->onRequestUrl($url);
      $result = $test->scanFiles($page, $currentPage);
      $r = $test->scanPagination($result);
      $this->assertRegExp("|rootdir/testdir/subdir/file$|", 
        $result[0]["url"], "1st file url");
      $this->assertRegExp("|rootdir/testdir/subdir/file2$|", 
        $result[1]["url"], "2nd file url");
      $this->assertRegExp("|rootdir/testdir/subdir/file3$|", 
        $result[2]["url"], "3rd file url");
      $this->assertRegExp("|rootdir/testdir/subdir/file4$|", 
        $result[3]["url"], "4th file url");
      $this->assertRegExp("|rootdir/testdir/subdir/file5$|", 
        $result[4]["url"], "5th file url");
      $this->assertEquals(5, count($result), "page count");
      $this->assertEquals(true, $r["pagination_enabled"], "pagination enabled");
      $this->assertEquals(false, $r["page_hasprev"], "hasprev");
      $this->assertNull($r["page_prevurl"], "prevurl");
      $this->assertEquals(true, $r["page_hasnext"], "hasnext");
      $this->assertRegExp("|rootdir/testdir/subdir/2$|",
        $r["page_nexturl"], "nexturl");
      $this->assertEquals(1, $r["page_index"], "page index");
      $this->assertEquals(3, $r["page_max"], "page max");
    }, $this, 'DirectoryBased')->__invoke();
  }
  
  /**
   * scanPagination()メソッドにおいて、ページリストが作成されることを確認する
   *
   * カレントURL:ページインデックス1
   * ページ数:3
   */
  public function testScanPaginationPage1() {
    Closure::bind(function() {
      $test = $this->getTest(true);
      $page = $this->getPagesData();
      $currentPage = $page[2];
      $url = $this->pico->getBaseUrl() . "rootdir/testdir/subdir/1";
      $test->onRequestUrl($url);
      $result = $test->scanFiles($page, $currentPage);
      $r = $test->scanPagination($result);
      $this->assertRegExp("|rootdir/testdir/subdir/file$|", 
        $result[0]["url"], "1st file url");
      $this->assertRegExp("|rootdir/testdir/subdir/file2$|", 
        $result[1]["url"], "2nd file url");
      $this->assertRegExp("|rootdir/testdir/subdir/file3$|", 
        $result[2]["url"], "3rd file url");
      $this->assertRegExp("|rootdir/testdir/subdir/file4$|", 
        $result[3]["url"], "4th file url");
      $this->assertRegExp("|rootdir/testdir/subdir/file5$|", 
        $result[4]["url"], "5th file url");
      $this->assertEquals(5, count($result), "page count");
      $this->assertEquals(true, $r["pagination_enabled"], "pagination enabled");
      $this->assertEquals(false, $r["page_hasprev"], "hasprev");
      $this->assertNull($r["page_prevurl"], "prevurl");
      $this->assertEquals(true, $r["page_hasnext"], "hasnext");
      $this->assertRegExp("|rootdir/testdir/subdir/2$|",
        $r["page_nexturl"], "nexturl");
      $this->assertEquals(1, $r["page_index"], "page index");
      $this->assertEquals(3, $r["page_max"], "page max");
    }, $this, 'DirectoryBased')->__invoke();
  }
  
  /**
   * scanPagination()メソッドにおいて、ページリストが作成されることを確認する
   *
   * カレントURL:ページインデックス2
   * ページ数:3
   */
  public function testScanPagination2() {
    Closure::bind(function() {
      $test = $this->getTest(true);
      $page = $this->getPagesData();
      $currentPage = $page[2];
      $url = $this->pico->getBaseUrl() . "rootdir/testdir/subdir/2";
      $test->onRequestUrl($url);
      $result = $test->scanFiles($page, $currentPage);
      $r = $test->scanPagination($result);
      $this->assertRegExp("|rootdir/testdir/subdir/file6$|", 
        $result[0]["url"], "1st file url");
      $this->assertRegExp("|rootdir/testdir/subdir/file7$|", 
        $result[1]["url"], "2nd file url");
      $this->assertRegExp("|rootdir/testdir/subdir/file8$|", 
        $result[2]["url"], "3rd file url");
      $this->assertRegExp("|rootdir/testdir/subdir/file9$|", 
        $result[3]["url"], "4th file url");
      $this->assertRegExp("|rootdir/testdir/subdir/fileA$|", 
        $result[4]["url"], "5th file url");
      $this->assertEquals(5, count($result), "page count");
      $this->assertEquals(true, $r["pagination_enabled"], "pagination enabled");
      $this->assertEquals(true, $r["page_hasprev"], "hasprev");
      $this->assertRegExp("|rootdir/testdir/subdir/1$|", 
        $r["page_prevurl"], "prevurl");
      $this->assertEquals(true, $r["page_hasnext"], "hasnext");
      $this->assertRegExp("|rootdir/testdir/subdir/3$|",
        $r["page_nexturl"], "nexturl");
      $this->assertEquals(2, $r["page_index"], "page index");
      $this->assertEquals(3, $r["page_max"], "page max");
    }, $this, 'DirectoryBased')->__invoke();
  }
  
  /**
   * scanPagination()メソッドにおいて、ページリストが作成されることを確認する
   *
   * カレントURL:ページインデックス3
   * ページ数:3
   */
  public function testScanPagination3() {
    Closure::bind(function() {
      $test = $this->getTest(true);
      $page = $this->getPagesData();
      $currentPage = $page[2];
      $url = $this->pico->getBaseUrl() . "rootdir/testdir/subdir/3";
      $test->onRequestUrl($url);
      $result = $test->scanFiles($page, $currentPage);
      $r = $test->scanPagination($result);
      $this->assertRegExp("|rootdir/testdir/subdir/fileB$|", 
        $result[0]["url"], "1st file url");
      $this->assertRegExp("|rootdir/testdir/subdir/fileC$|", 
        $result[1]["url"], "2nd file url");
      $this->assertRegExp("|rootdir/testdir/subdir/fileD$|", 
        $result[2]["url"], "3rd file url");
      $this->assertRegExp("|rootdir/testdir/subdir/fileE$|", 
        $result[3]["url"], "4th file url");
      $this->assertRegExp("|rootdir/testdir/subdir/fileF$|", 
        $result[4]["url"], "5th file url");
      $this->assertEquals(5, count($result), "page count");
      $this->assertEquals(true, $r["pagination_enabled"], "pagination enabled");
      $this->assertEquals(true, $r["page_hasprev"], "hasprev");
      $this->assertRegExp("|rootdir/testdir/subdir/2$|",
        $r["page_prevurl"], "prevurl");
      $this->assertEquals(false, $r["page_hasnext"], "hasnext");
      $this->assertNull($r["page_nexturl"], "nexturl");
      $this->assertEquals(3, $r["page_index"], "page index");
      $this->assertEquals(3, $r["page_max"], "page max");
    }, $this, 'DirectoryBased')->__invoke();
  }
  
  /**
   * scanPagination()メソッドにおいて、ページリストが作成されないことを確認する
   *
   * カレントURL:ページインデックス4
   * ページ数:3
   */
  public function testScanPagination4() {
    Closure::bind(function() {
      $test = $this->getTest(true);
      $page = $this->getPagesData();
      $currentPage = $page[2];
      $url = $this->pico->getBaseUrl() . "rootdir/testdir/subdir/4";
      $test->onRequestUrl($url);
      $result = $test->scanFiles($page, $currentPage);
      $r = $test->scanPagination($result);
      $this->assertEmpty($result, "files");
    }, $this, 'DirectoryBased')->__invoke();
  }
  
  /**
   * scanPagination()メソッドにおいて、ページリストが作成されないことを確認する
   *
   * カレントURL:ページインデックス0
   * ページ数:3
   */
  public function testScanPagination0() {
    Closure::bind(function() {
      $test = $this->getTest(true);
      $page = $this->getPagesData();
      $currentPage = $page[2];
      $url = $this->pico->getBaseUrl() . "rootdir/testdir/subdir/0";
      $test->onRequestUrl($url);
      $result = $test->scanFiles($page, $currentPage);
      $r = $test->scanPagination($result);
      $this->assertEmpty($result, "files");
    }, $this, 'DirectoryBased')->__invoke();
  }
  
  public function getPagesData() {
    $pages = array();
    $u = function($name){
      return array("url" => $this->pico->getBaseUrl() . "/" . $name);
    };
    // ※1
    array_push($pages, $u("rootdir/testdir/index"));
    array_push($pages, $u("rootdir/testdir/file"));
    // ※2
    array_push($pages, $u("rootdir/testdir/subdir/index"));
    array_push($pages, $u("rootdir/testdir/subdir/file"));
    array_push($pages, $u("rootdir/testdir/subdir/file2"));
    array_push($pages, $u("rootdir/testdir/subdir/file3"));
    array_push($pages, $u("rootdir/testdir/subdir/file4"));
    array_push($pages, $u("rootdir/testdir/subdir/file5"));
    array_push($pages, $u("rootdir/testdir/subdir/file6"));
    array_push($pages, $u("rootdir/testdir/subdir/file7"));
    array_push($pages, $u("rootdir/testdir/subdir/file8"));
    array_push($pages, $u("rootdir/testdir/subdir/file9"));
    array_push($pages, $u("rootdir/testdir/subdir/fileA"));
    array_push($pages, $u("rootdir/testdir/subdir/fileB"));
    array_push($pages, $u("rootdir/testdir/subdir/fileC"));
    array_push($pages, $u("rootdir/testdir/subdir/fileD"));
    array_push($pages, $u("rootdir/testdir/subdir/fileE"));
    array_push($pages, $u("rootdir/testdir/subdir/fileF"));
    array_push($pages, $u("rootdir/testdir/subdir2/index"));
    array_push($pages, $u("rootdir/testdir/subfile"));
    // ※3
    array_push($pages, $u("rootdir/testdir/subdir3/file"));
    array_push($pages, $u("rootdir/testdir/subdir3/file2"));
    // ※4
    array_push($pages, $u("rootdir/testdir2/index"));
    array_push($pages, $u("rootdir/testdir2/file"));
    array_push($pages, $u("rootdir/testdir2/file2"));
    array_push($pages, $u("rootdir/index"));
    array_push($pages, $u("rootdir/file2"));
    array_push($pages, $u("rootdir/file3"));
    return $pages;
  }
};

?>