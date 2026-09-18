<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class M_skmb extends CI_Model {

    protected $table = 'skmb';

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
            1 => 'patient_name',
            2 => 'nik',
            3 => 'pengantar',
            4 => 'tgl_datang',
            5 => 'jam',
            6 => 'docnumb',
            7 => 'docdate',
        );

        $this->db->from($this->table);

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('patient_name', $search);
            $this->db->or_like('nik', $search);
            $this->db->or_like('pengantar', $search);
            $this->db->or_like('docnumb', $search);
            $this->db->group_end();
        }

        if (isset($columns[$order_col])) {
            $dir = strtoupper($order_dir) === 'DESC' ? 'DESC' : 'ASC';
            $this->db->order_by($columns[$order_col], $dir);
        } else {
            $this->db->order_by('insertdt', 'DESC');
        }
    }

    /**
     * Get all doctors
     */
    public function get_all_doctor() {
        return $this->db->query(
            'SELECT *
             FROM conf_users
             WHERE level = 3 and status = 1
             ORDER BY fullname DESC'
        );
    }

    /**
     * Get single SKMB by ID
     */
    public function get_by_id($id) {
        return $this->db->get_where($this->table, array('id' => intval($id)))->row();
    }

    /**
     * Get SKMB by ID with doctor join
     */
    public function get_skmb_by_id($id) {
        $this->db->select('skmb.*, doctor.fullname AS doct_name, creator.fullname AS insert_name, updater.fullname AS update_name');
        $this->db->from('skmb');
        $this->db->join('conf_users AS doctor', 'skmb.doct_by_id = doctor.id_user', 'left');
        $this->db->join('conf_users AS creator', 'skmb.insertby = creator.id_user', 'left');
        $this->db->join('conf_users AS updater', 'skmb.updateby = updater.id_user', 'left');
        $this->db->where('skmb.id', $id);
        return $this->db->get()->row();
    }

    /**
     * Insert new SKMB
     */
    public function insert($data) {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    /**
     * Update existing SKMB
     */
    public function update($id, $data) {
        $this->db->where('id', intval($id));
        $this->db->update($this->table, $data);
        return $this->db->affected_rows();
    }

    /**
     * Delete SKMB by ID
     */
    public function delete($id) {
        $this->db->where('id', intval($id));
        $this->db->delete($this->table);
        return $this->db->affected_rows();
    }

    /**
     * Check if docnumb already exists (for validation)
     */
    public function is_docnumb_unique($docnumb, $exclude_id = 0) {
        $this->db->where('docnumb', $docnumb);
        if ($exclude_id > 0) {
            $this->db->where('id !=', intval($exclude_id));
        }
        return $this->db->get($this->table)->num_rows() === 0;
    }

    /**
     * Nomor dokumen default: 00000/SKMB/IX/2026
     * Running number selalu 00000 dan diperbarui manual oleh user pada form
     * (Create & Update). Dipakai sebagai cadangan bila field kosong.
     */
    public function generate_docnumb() {
        return docnumb_default('SKMB');
    }

    /**
     * Konversi angka bulan (1–12) ke Romawi
     */
    private function month_roman($n) {
        $map = array(
            1  => 'I',   2  => 'II',  3  => 'III', 4  => 'IV',
            5  => 'V',   6  => 'VI',  7  => 'VII', 8  => 'VIII',
            9  => 'IX',  10 => 'X',   11 => 'XI',  12 => 'XII',
        );
        return isset($map[$n]) ? $map[$n] : '';
    }
}
