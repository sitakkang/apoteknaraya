<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Obat extends CI_Controller {

    public $dir_v = 'obat/';

    public function __construct() {
        parent::__construct();
        $this->m_auth->check_login();
        $this->m_auth->check_akses();
        $this->load->model('M_obat');
    }

    public function index() {
        $data['css'] = array(
            'lib/datatables/dataTables.bootstrap.min.css',
            'lib/datatables/fixedColumns.bootstrap.min.css',
        );
        $data['js'] = array(
            'lib/datatables/datatables.min.js',
            'lib/datatables/dataTables.bootstrap.min.js',
            'lib/datatables/dataTables.fixedColumns.min.js',
            'lib/sweetalert/sweetalert2.all.min.js',
            'src/js/admin/obat.js',
        );
        $data['panel'] = '<i class="fa fa-pills"></i> &nbsp;<b>Manajemen Obat</b>';
        $this->l_skin->main($this->dir_v.'view', $data);
    }

    public function table() {
        $draw   = intval($this->input->get('draw'));
        $start  = intval($this->input->get('start'));
        $length = intval($this->input->get('length'));

        $search_raw = $this->input->get('search');
        $search = (!empty($search_raw) && isset($search_raw['value']))
            ? trim($search_raw['value'])
            : '';

        $order_raw = $this->input->get('order');
        $order_col = (!empty($order_raw) && isset($order_raw[0]['column']))
            ? intval($order_raw[0]['column'])
            : -1;
        $order_dir = (!empty($order_raw) && isset($order_raw[0]['dir']))
            ? $order_raw[0]['dir']
            : 'asc';

        $total    = $this->M_obat->count_all();
        $filtered = $this->M_obat->count_filtered($search, $order_col, $order_dir);
        $rows     = $this->M_obat->get_datatables($search, $order_col, $order_dir, $start, $length);

        $data = array();
        $i    = $start + 1;

        foreach ($rows as $row) {
            $status = $row->obat_status == 1
                ? '<span class="badge badge-success">Aktif</span>'
                : '<span class="badge badge-secondary">Nonaktif</span>';

            $data[] = array(
                'DT_RowId'  => $row->id_obat,
                '0'         => $i++,
                '1'         => htmlspecialchars($row->obat_name),
                '2'         => $status,
            );
        }

        echo json_encode(array(
            'draw'            => $draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $data,
        ));
        exit();
    }

    public function add() {
        $this->load->view($this->dir_v.'add');
    }

    public function edit() {
        $id = intval($this->input->get('id'));
        $data['row'] = $this->M_obat->get_by_id($id);
        if (!$data['row']) {
            show_404();
        }
        $this->load->view($this->dir_v.'edit', $data);
    }

    public function act_add() {
        // Satuan & harga tidak dipakai lagi (obat diinput manual di pemeriksaan)
        $data = array(
            'obat_name'   => strtoupper(trim($this->input->post('obat_name'))),
            'obat_status' => 1,
        );

        $this->M_obat->insert($data);

        echo json_encode(array(
            'status' => 2,
            'notif'  => 'Obat ' . $data['obat_name'] . ' berhasil ditambahkan!',
        ));
    }

    public function act_edit() {
        $id = intval($this->input->post('id'));

        $data = array(
            'obat_name'   => strtoupper(trim($this->input->post('obat_name'))),
        );

        $this->M_obat->update($id, $data);

        echo json_encode(array(
            'status' => 2,
            'notif'  => 'Obat ' . $data['obat_name'] . ' berhasil diperbarui!',
        ));
    }

    public function act_del() {
        $id  = intval($this->input->post('id'));
        $row = $this->M_obat->get_by_id($id);

        if (!$row) {
            echo json_encode(array('status' => 1, 'notif' => 'Data obat tidak ditemukan!'));
            return;
        }

        $this->M_obat->delete($id);

        echo json_encode(array(
            'status' => 2,
            'notif'  => 'Obat ' . htmlspecialchars($row->obat_name) . ' berhasil dihapus!',
        ));
    }
}
