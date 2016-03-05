<?php
class Transaction_model extends CI_Model{

	function __construct(){
		parent::__construct();
		$this->load->database();
	}

	function add_transaction($data){
		return $this->db->insert('transactions', $data);
	}
	
}
?>