<?php
class User {
	private $_id;
	private $_name;
	private $_username;
	private $_role;
	private $_establishment;

	public function __construct($id, $name, $username, $role, $establishment) {
		$this->setAttributes($id, $name, $username, $role, $establishment);
	}
	
	public function setAttributes($id, $name, $username, $role, $establishment) {
		$this->_id = $id;
		$this->_name = $name;
		$this->_username = $username;
		$this->_role = $role;
		$this->_establishment = $establishment;
	}
	
	public function setRole($role){
		$this->_role = $role;
	}

	public function setEstablishment($establishment){
		$this->_establishment = $establishment;
	}
	
	// gets
	public function getId() {
		return $this->_id;
	}

	public function getName() {
		return $this->_name;
	}
	
	public function getUsername() {
		return $this->_username;
	}
	
	public function getRole() {
		return $this->_role;
	}
	
	public function getEstablishment() {
		return $this->_establishment;
	}
}
?>