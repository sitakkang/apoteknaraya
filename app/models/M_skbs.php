<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class M_skbs extends CI_Model {

    protected $table = 'trans_skbs';

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
     * DataTables server-side: jumlah total seluruh record (hanya aktif)
     */
    public function count_all() {
        $this->db->where('skbs_status', 1);
        return $this->db->count_all_results($this->table);
    }

    /**
     * Query builder dasar untuk server-side (search + order)
     */
    private function _dt_query($search, $order_col, $order_dir) {
        $columns = array(
            1 => 'skbs_patient_name',
            2 => 'skbs_patient_nik',
            3 => 'skbs_patient_company',
            4 => 'skbs_patient_department',
            5 => 'skbs_result_name',
            6 => 'skbs_doct_name',
            7 => 'skbs_doc_date',
        );

        $this->db->from($this->table);
        $this->db->where('skbs_status', 1);

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('skbs_patient_name', $search);
            $this->db->or_like('skbs_patient_nik', $search);
            $this->db->or_like('skbs_patient_company', $search);
            $this->db->or_like('skbs_patient_department', $search);
            $this->db->or_like('skbs_result_name', $search);
            $this->db->or_like('skbs_doct_name', $search);
            $this->db->group_end();
        }

        if (isset($columns[$order_col])) {
            $dir = strtoupper($order_dir) === 'DESC' ? 'DESC' : 'ASC';
            $this->db->order_by($columns[$order_col], $dir);
        } else {
            $this->db->order_by('insert_dt', 'DESC');
        }
    }

    /**
     * Get single SKBS by ID
     */
    public function get_by_id($id) {
        return $this->db->get_where($this->table, array('id_skbs' => intval($id)))->row();
    }

    /**
     * Get SKBS by ID with audit info
     */
    public function get_skbs_by_id($id) {
        $this->db->select('trans_skbs.*, 
                           creator.fullname AS insert_name,
                           updater.fullname AS update_name');
        $this->db->from('trans_skbs');
        $this->db->join('conf_users AS creator', 'trans_skbs.insert_by = creator.id_user', 'left');
        $this->db->join('conf_users AS updater', 'trans_skbs.update_by = updater.id_user', 'left');
        $this->db->where('trans_skbs.id_skbs', intval($id));
        return $this->db->get()->row();
    }
}
