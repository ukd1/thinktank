<?php
 
require_once (dirname(__FILE__).'/simpletest/autorun.php');

require_once (dirname(__FILE__).'/config.tests.inc.php');

ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once ("common/class.MySQLDAO.php");
require_once ("common/class.Database.php");
require_once ("common/class.Logger.php");
require_once ("config.inc.php");


class TestOfMySQLDAO extends UnitTestCase {
	var $logger;
	var $db;
    function TestOfMySQLDAO() {
        $this->UnitTestCase('MySQLDAO class test');
    }
    
    function setUp() {
		global $THINKTANK_CFG;
        $this->logger = new Logger($THINKTANK_CFG['log_location']);
		$this->db = new Database($THINKTANK_CFG);
    }
    
    function tearDown() {
		$this->logger->close();
    	
    }
    
    function testCreatingNewMySQLDAO() {
		$dao = new MySQLDAO($this->logger, $this->db);
		$this->assertTrue(isset($dao->logger), "Logger set");
		$this->assertTrue(isset($dao->db), "DB set");

    }
}
?>