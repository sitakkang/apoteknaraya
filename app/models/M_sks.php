<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class M_sks extends CI_Model {

    protected $table = 'sks';

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
            2 => 'gender',
            3 => 'company_name',
            4 => 'diagnosa',
            5 => 'docnumb',
            6 => 'docdate',
            7 => 'datefrom',
            8 => 'dateto',
        );

        $this->db->from($this->table);

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('patient_name', $search);
            $this->db->or_like('company_name', $search);
            $this->db->or_like('patient_job', $search);
            $this->db->or_like('diagnosa', $search);
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
     * Get all SKS records for DataTable
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
     * Get single SKS by ID
     */
    public function get_by_id($id) {
        return $this->db->get_where($this->table, array('id' => intval($id)))->row();
    }

    public function get_sks_by_id($id) {
        $this->db->select('sks.*, doctor.fullname AS fullname, doctor.nip AS nip, creator.fullname AS insert_name, updater.fullname AS update_name');
        $this->db->from('sks');
        $this->db->join('conf_users AS doctor', 'sks.doctby = doctor.id_user', 'left');
        $this->db->join('conf_users AS creator', 'sks.insertby = creator.id_user', 'left');
        $this->db->join('conf_users AS updater', 'sks.updateby = updater.id_user', 'left');
        $this->db->where('sks.id', $id);
        return $this->db->get()->row();
    }

    /**
     * Get patient_job from ms_patient by patient name (match terbaru)
     */
    public function get_patient_job_by_name($name) {
        if (empty($name)) return '';
        $row = $this->db->query(
            'SELECT patient_job FROM ms_patient
             WHERE patient_name = ?
             ORDER BY id_patient DESC LIMIT 1',
            array(strtoupper(trim($name)))
        )->row();
        return $row ? $row->patient_job : '';
    }

    /**
     * Insert new SKS
     */
    public function insert($data) {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    /**
     * Update existing SKS
     */
    public function update($id, $data) {
        $this->db->where('id', intval($id));
        $this->db->update($this->table, $data);
        return $this->db->affected_rows();
    }

    /**
     * Delete SKS by ID
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
     * Generate nomor dokumen otomatis (running number per bulan)
     * Format: 00001/SKS/VII/2026
     */
    public function generate_docnumb() {
        $month = date('n');  // 1–12
        $year  = date('Y');

        // Cari nomor terakhir di bulan & tahun ini
        $last = $this->db->query(
            "SELECT docnumb FROM sks
             WHERE docnumb LIKE '%/SKS/" . $this->db->escape_like_str($this->month_roman($month)) . "/$year'
             ORDER BY id DESC LIMIT 1"
        )->row();

        if ($last) {
            // Ambil angka dari depan: 00001/SKS/...
            $parts = explode('/', $last->docnumb);
            $next  = intval($parts[0]) + 1;
        } else {
            $next = 1;
        }

        return sprintf('%05d', $next) . '/SKS/' . $this->month_roman($month) . '/' . $year;
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
