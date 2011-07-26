<?php
###
# Info:
#  Last Updated 2011
#  Daniel Schultz
#
###
require_once("DBConn.php");
require_once("FactoryObject.php");
require_once("Claim.php");
require_once("ResultClass.php");

class Verdict extends FactoryObject {
	
	# Constants
	
	
	# Static Variables
	
	
	# Instance Variables
	private $claimID; // int
	private $resultClassID; //int
	private $url; // str
	private $dateCreated; // timestamp
	
	
	# Caches
	private $result;
	private $claim;
	
	
	# FactoryObject Methods
	protected static function gatherData($objectString) {
		$dataArrays = array();
		
		// Load an empty object
		if($objectString === FactoryObject::INIT_EMPTY) {
			$dataArray = array();
			$dataArray['itemID'] = 0;
			$dataArray['claimID'] = 0;
			$dataArray['resultClassID'] = 0;
			$dataArray['url'] = "";
			$dataArray['dateCreated'] = 0;
			$dataArrays[] = $dataArray;
			return $dataArrays;
		}
		
		// Load a default object
		if($objectString === FactoryObject::INIT_DEFAULT) {
			$dataArray = array();
			$dataArray['itemID'] = 0;
			$dataArray['claimID'] = 0;
			$dataArray['resultClassID'] = 0;
			$dataArray['url'] = "";
			$dataArray['dateCreated'] = 0;
			$dataArrays[] = $dataArray;
			return $dataArrays;
		}
		
		// Set up for lookup
		$mysqli = DBConn::connect();
		
		// Load the object data
		$queryString = "SELECT verdicts.id AS itemID,
							   verdicts.claim_id AS claimID,
							   verdicts.result_class_id AS resultClassID,
							   verdicts.url AS url,
							   unix_timestamp(verdicts.date_created) as dateCreated
						  FROM verdicts
						 WHERE verdicts.id IN (".$objectString.")";
		
		$result = $mysqli->query($queryString)
			or print($mysqli->error);
		
		while($resultArray = $result->fetch_assoc()) {
			$dataArray = array();
			$dataArray['itemID'] = $resultArray['itemID'];
			$dataArray['claimID'] = $resultArray['claimID'];
			$dataArray['resultClassID'] = $resultArray['resultClassID'];
			$dataArray['url'] = $resultArray['url'];
			$dataArray['dateCreated'] = $resultArray['dateCreated'];
			$dataArrays[] = $dataArray;
		}
		
		$result->free();
		return $dataArrays;
	}
	
	public function load($dataArray) {
		parent::load($dataArray);
		$this->claimID = isset($dataArray["claimID"])?$dataArray["claimID"]:0;
		$this->resultClassID = isset($dataArray["resultClassID"])?$dataArray["resultClassID"]:0;
		$this->url = isset($dataArray["url"])?$dataArray["url"]:"";
		$this->dateCreated = isset($dataArray["dateCreated"])?$dataArray["dateCreated"]:0;
	}
	
	
	# Data Methods
	public function validate() {
		return true;
	}
	
	public function save() {
		if(!$this->validate()) return;
		
		$mysqli = DBConn::connect();
		
		if($this->isUpdate()) {
			// Update an existing record
			$queryString = "UPDATE verdicts
							   SET verdicts.claim_id = ".DBConn::clean($this->getClaimID()).",
								   verdicts.result_class_id = ".DBConn::clean($this->getResultClassID()).",
								   verdicts.url = ".DBConn::clean($this->getURL()).",
							 WHERE verdicts.id = ".DBConn::clean($this->getItemID());
							
			$mysqli->query($queryString) or print($mysqli->error);
		} else {
			// Create a new record
			$queryString = "INSERT INTO verdicts
								   (verdicts.id,
									verdicts.claim_id,
									verdicts.result_class_id,
									verdicts.url,
									verdicts.date_created)
							VALUES (0,
									".DBConn::clean($this->getClaimID()).",
									".DBConn::clean($this->getResultClassID()).",
									".DBConn::clean($this->getURL()).",
									NOW())";
			
			$mysqli->query($queryString) or print($mysqli->error);
			$this->setItemID($mysqli->insert_id);
		}
		
		// Parent Operations
		return parent::save();
	}
	
	public function delete() {
		parent::delete();
		$mysqli = DBConn::connect();
		
		// Delete this record
		$queryString = "DELETE FROM verdicts
							  WHERE verdicts.id = ".DBConn::clean($this->getItemID());
		$mysqli->query($queryString);
	}
	
	
	# Getters
	public function getClaimID() { return $this->claimID;}
	
	public function getResultClassID() { return $this->resultClassID;}
	
	public function getURL() { return $this->url;}
	
	public function getDateCreated() { return $this->dateCreated;}
	
	public function getResultClass() {
		if($this->result != null)
			return $this->result;
		return $this->result = ResultClass::getObject($this->getResultClassID());
	}

	public function getClaim() {
		if($this->claim != null)
			return $this->claim;
		return $this->claim = Claim::getObject($this->getClaimID());
	}
	
	
	# Setters
	public function setClaimID($int) { $this->claimID = $int;}
	
	public function setResultClassID($int) { $this->resultClassID = $int;}
	
	public function setURL($str) { $this->url = $str;}
	
}

?>