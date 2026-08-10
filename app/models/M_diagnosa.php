<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class M_diagnosa extends CI_Model {

    protected $table = 'ms_diagnosa';

    public function __construct() {
        parent::__construct();
    }

    /**
     * DataTables server-side: data untuk halaman aktif
     */
    public function get_datatables($search, $order_col, $order_dir, $start, $length) {
        $this->_dt_query($search, $order_col, $order_dir);

        if ($length != -1) {
            $this->db->limit(intval($length), intval($start));
        }

        return $this->db->get()->result();
    }

    /**
     * DataTables server-side: jumlah record hasil filter
     */
    public function count_filtered($search, $order_col, $order_dir) {
        $this->_dt_query($search, $order_col, $order_dir);
        return $this->db->count_all_results();
    }

    /**
     * DataTables server-side: jumlah total seluruh record
     */
    public function count_all() {
        return $this->db->count_all($this->table);
    }

    /**
     * Query builder dasar untuk server-side (search + order)
     */
    private function _dt_query($search, $order_col, $order_dir) {
        $columns = array(
            1 => 'dgn_cat',
            2 => 'dgn_name',
            3 => 'dgn_status',
        );

        $this->db->from($this->table);

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('dgn_cat', $search);
            $this->db->or_like('dgn_name', $search);
            $this->db->group_end();
        }

        if (isset($columns[$order_col])) {
            $dir = strtoupper($order_dir) === 'DESC' ? 'DESC' : 'ASC';
            $this->db->order_by($columns[$order_col], $dir);
        } else {
            $this->db->order_by('id_diagnosa', 'DESC');
        }
    }

    public function get_by_id($id) {
        return $this->db->get_where($this->table, array('id_diagnosa' => intval($id)))->row();
    }

    public function insert($data) {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id_diagnosa', intval($id));
        $this->db->update($this->table, $data);
        return $this->db->affected_rows();
    }

    public function delete($id) {
        $this->db->where('id_diagnosa', intval($id));
        $this->db->delete($this->table);
        return $this->db->affected_rows();
    }
}
