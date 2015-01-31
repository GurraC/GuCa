<?php
class CUser {
	private $db;
	private $acronym;
	/**
	* Constructor.
	*/
	public function __construct($db){
		$this->acronym = isset($_SESSION['user']) ? $_SESSION['user']->acronym : null;
		$this->db=$db;
	}
	
	
	/**
	* Login function
	* @param string $user
	* @param string $password
	*/
	public function logIn($user,$password){
		$sql = "SELECT acronym, name FROM USER WHERE acronym = ? AND password = md5(concat(?, salt));";
		$res = $this->db->ExecuteSelectQueryAndFetchAll($sql,array($_POST['acronym'], $_POST['password']));
		if(isset($res[0])) {
			$_SESSION['user'] = $res[0];
		}
	}
	
	/**
	* Logout function
	*/
	public function logOut(){
		unset($_SESSION['user']);
	}
	
	/**
	* Authentication function
	* @return boolean true if active session
	*/
	public function isActive(){
		if($this->acronym) {
			return true;
		}
		else {
			return false;
		}
	}
	
	/**
	* Getters speaks for themselves
	*/
	public function getAcronym(){
		return $_SESSION['user']->acronym;
	}
	
	public function getName(){
		return $_SESSION['user']->name;
	}

}
?>