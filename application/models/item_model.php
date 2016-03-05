<?php
class Item_model extends CI_Model{

	function __construct(){
		parent::__construct();
		$this->load->database();
	}

	function add_item($data){
		return $this->db->insert('items', $data);
	}
	
}
?>