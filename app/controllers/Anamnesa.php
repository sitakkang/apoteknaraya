<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Anamnesa extends CI_Controller {

    public $dir_v = 'anamnesa/';

    public function __construct() {
        parent::__construct();
        $this->m_auth->check_login();
        $this->m_auth->check_akses();
        $this->load->model('M_anamnesa');
    }

    public function index() {
        $data['css'] = array(
            'lib/datatables/dataTables.bootstrap.min.css',
            'lib/datatables/fixedColumns.bootstrap.min.css',
            'lib/datepicker/datepicker.min.css',
            'lib/select/component-chosen.min.css',
            'lib/clockpicker/clockpicker.min.css',
        );
        $data['js'] = array(
            'lib/datatables/datatables.min.js',
            'lib/datatables/dataTables.bootstrap.min.js',
            'lib/datatables/dataTables.fixedColumns.min.js',
            'lib/sweetalert/sweetalert2.all.min.js',
            'lib/datepicker/datepicker.min.js',
            'lib/select/chosen.jquery.min.js',
            'lib/mask/jquery.mask.min.js',
            'lib/clockpicker/clockpicker.min.js',
            'src/js/admin/pemeriksaandokter.js',
            'src/js/admin/anamnesa.js',
        );
        $data['panel'] = '<i class="fa fa-stethoscope"></i> &nbsp;<b>Anamnesa</b>';
        $this->l_skin->main($this->dir_v.'view', $data);
    }

    /**
     * DataTables JSON source — filter by date
     */
    public function table() {
        $date = trim($this->input->get('date'));

        if (empty($date)) {
            echo json_encode(array(
                'draw'            => intval($this->input->get('draw')),
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => array(),
            ));
            exit();
        }

        // Convert dari dd/mm/yyyy ke yyyy-mm-dd
        $date_db = $this->format_date_db($date);
        $rows = $this->M_anamnesa->get_by_date($date_db);

        $draw = intval($this->input->get('draw'));
        $data = array();
        $i    = 1;

        foreach ($rows->result() as $row) {
            $has_anamnesa = !empty($row->id_trans_anm);

            $data[] = array(
                'DT_RowId'        => $row->id_visit,
                '0'               => $i++,
                '1'               => htmlspecialchars($row->trans_patient_code),
                '2'               => htmlspecialchars($row->patient_name),
                '3'               => htmlspecialchars($row->trans_patient_company),
                '4'               => htmlspecialchars($row->trans_patient_phone),
                '5'               => !empty($row->trans_doc) ? date('d/m/Y', strtotime($row->trans_doc)) : '-',
                '6'               => !empty($row->trans_insert_dt) ? date('d/m/Y H:i', strtotime($row->trans_insert_dt)) : '-',
                'id_medical_record' => $row->id_medical_record,
                'id_trans_anm'    => $row->id_trans_anm,
                'has_anamnesa'    => $has_anamnesa,
                'mrd_doct_name'   => htmlspecialchars($row->mrd_doct_name ?: ''),
                'jml_diagnosa'    => intval($row->jml_diagnosa),
                'jml_obat'        => intval($row->jml_obat),
                'jml_pulv'        => intval($row->jml_pulv),
                'jml_sks'         => intval($row->jml_sks),
                'jml_skbs'        => intval($row->jml_skbs),
                'jml_skmb'        => intval($row->jml_skmb),
            );
        }

        echo json_encode(array(
            'draw'            => $draw,
            'recordsTotal'    => $rows->num_rows(),
            'recordsFiltered' => $rows->num_rows(),
            'data'            => $data,
        ));
        exit();
    }

    /**
     * View detail Anamnesa (via AJAX ke modal)
     */
    public function detail() {
        $id = intval($this->input->get('id'));
        $data['row'] = $this->M_anamnesa->get_by_visit_id($id);
        if (!$data['row']) {
            show_404();
        }
        $this->load->view($this->dir_v.'detail', $data);
    }

    /**
     * Load form anamnesa (Tambah / Ubah) via AJAX ke modal
     */
    public function anamnesa_form() {
        $visit_id = intval($this->input->get('visit_id'));
        $row = $this->M_anamnesa->get_by_visit_id($visit_id);
        if (!$row) {
            show_404();
        }

        // Cek apakah anamnesa sudah ada
        $anamnesa = $this->M_anamnesa->get_anamnesa_by_medical_record($row->id_medical_record);
        $data['row'] = $row;
        $data['anamnesa'] = $anamnesa;

        $this->load->view($this->dir_v.'anamnesa_form', $data);
    }

    /**
     * Halaman pemeriksaan lengkap (Dokter / Diagnosa / Obat / Racikan / SKS / SKBS / SKMB).
     * Memakai view & endpoint yang sama dengan menu Dokter supaya perilakunya identik.
     */
    public function periksa($visit_id) {
        $this->load->model('M_dokter');

        $data['row'] = $this->M_dokter->get_by_visit_id($visit_id);
        if (!$data['row']) {
            show_404();
        }

        $mrd_id = $data['row']->id_medical_record;

        $data['diagnosa_list']     = $this->M_dokter->get_all_diagnosa();
        $data['dokter_list']       = $this->M_dokter->get_all_doctor();
        $data['diagnosa_terpilih'] = $this->M_dokter->get_diagnosa_by_medical_record($mrd_id);
        $data['obat_terpilih']     = $this->M_dokter->get_obat_by_medical_record($mrd_id);
        $data['pulv_list']         = $this->M_dokter->get_pulv_by_medical_record($mrd_id);
        $data['pulv_items']        = $this->M_dokter->get_pulv_items_grouped($mrd_id);

        // Default teks diagnosa & terapi untuk SKS
        $data['sks_diagnosa_default'] = $this->text_diagnosa_default($mrd_id);
        $data['sks_terapi_default']   = $this->text_terapi_default($mrd_id);

        $data['sks']  = $this->M_dokter->get_sks_by_visit_id($visit_id);
        $data['skbs'] = $this->M_dokter->get_skbs_by_visit_id($visit_id);
        $data['skmb'] = $this->M_dokter->get_skmb_by_visit_id($visit_id);
        $data['dokter_level6'] = $this->M_dokter->get_users_by_level(3);

        // Halaman ini dibuka dari menu Anamnesa → tombol "Kembali" mengarah ke daftar anamnesa
        $data['back_menu'] = 'anamnesa';

        $data['css'] = array(
            'lib/datatables/dataTables.bootstrap.min.css',
            'lib/datatables/fixedColumns.bootstrap.min.css',
            'lib/datepicker/datepicker.min.css',
            'lib/select/component-chosen.min.css',
        );
        $data['js'] = array(
            'lib/bootstrap/js/bootstrap.min.js',
            'lib/datatables/datatables.min.js',
            'lib/datatables/dataTables.bootstrap.min.js',
            'lib/datepicker/datepicker.min.js',
            'lib/select/chosen.jquery.min.js',
            'lib/mask/jquery.mask.min.js',
            'lib/sweetalert/sweetalert2.all.min.js',
            'src/js/admin/dokter.js',
            'src/js/admin/pemeriksaandokter.js',
        );
        $data['panel'] = '<i class="fa fa-stethoscope"></i> &nbsp;<b>Pemeriksaan Pasien</b>';
        $this->l_skin->main('dokter/pemeriksaan', $data);
    }

    /**
     * Isi modal per bagian pemeriksaan — dipakai kolom Dokter / Diagnosa / Obat / SKS / SKBS / SKMB
     * pada daftar Anamnesa. URL: anamnesa/modal/{section}/{visit_id}
     */
    public function modal($section = '', $visit_id = 0) {
        $this->load->model('M_dokter');

        $section  = strtolower(trim($section));
        $visit_id = intval($visit_id);

        $row = $this->M_dokter->get_by_visit_id($visit_id);
        if (!$row) {
            show_404();
        }

        $mrd_id = $row->id_medical_record;
        $data['row'] = $row;

        switch ($section) {
            case 'dokter':
                $data['dokter_list'] = $this->M_dokter->get_users_by_level(3);
                $this->load->view('anamnesa/_modal_dokter', $data);
                break;

            case 'diagnosa':
                $data['diagnosa_list']     = $this->M_dokter->get_all_diagnosa();
                $data['diagnosa_terpilih'] = $this->M_dokter->get_diagnosa_by_medical_record($mrd_id);
                $this->load->view('dokter/_diagnosa_section', $data);
                break;

            case 'obat':
                $data['obat_terpilih'] = $this->M_dokter->get_obat_by_medical_record($mrd_id);
                $data['pulv_list']     = $this->M_dokter->get_pulv_by_medical_record($mrd_id);
                $data['pulv_items']    = $this->M_dokter->get_pulv_items_grouped($mrd_id);
                $this->load->view('dokter/_obat_section', $data);
                break;

            case 'sks':
                $sks = $this->M_dokter->get_sks_by_visit_id($visit_id);
                $data['sks']            = $sks;
                $data['sks_list']       = $sks ? array($sks) : array();
                $data['dokter_list']    = $this->M_dokter->get_all_doctor();
                $data['sks_diagnosa_default'] = $this->text_diagnosa_default($mrd_id);
                $data['sks_terapi_default']   = $this->text_terapi_default($mrd_id);
                $this->load->view('dokter/_sks_wrap', $data);
                break;

            case 'skbs':
                $data['skbs']        = $this->M_dokter->get_skbs_by_visit_id($visit_id);
                $data['dokter_list'] = $this->M_dokter->get_all_doctor();
                $this->load->view('dokter/_skbs_wrap', $data);
                break;

            case 'skmb':
                $data['skmb']        = $this->M_dokter->get_skmb_by_visit_id($visit_id);
                $data['dokter_list'] = $this->M_dokter->get_all_doctor();
                $this->load->view('dokter/_skmb_wrap', $data);
                break;

            default:
                show_404();
        }
    }

    /**
     * Teks default diagnosa untuk form SKS
     */
    private function text_diagnosa_default($mrd_id) {
        $text = '';
        $rows = $this->M_dokter->get_diagnosa_by_medical_record($mrd_id);
        if (!empty($rows)) {
            $no = 1;
            foreach ($rows as $d) {
                $text .= $no . '. ' . $d->trans_dgn_name . "\r\n";
                $no++;
            }
        }
        return $text;
    }

    /**
     * Teks default terapi untuk form SKS (diambil dari daftar obat)
     */
    private function text_terapi_default($mrd_id) {
        $text = '';
        $rows = $this->M_dokter->get_obat_by_medical_record($mrd_id);
        if (!empty($rows)) {
            $no = 1;
            foreach ($rows as $o) {
                $item = trim($o->trans_obat_name);
                if (!empty($o->trans_obat_dosis)) {
                    $item .= ' ' . trim($o->trans_obat_dosis);
                }
                $text .= $no . '. ' . $item . "\r\n";
                $no++;
            }
        }
        return $text;
    }

    /**
     * Proses simpan / update anamnesa
     */
    public function act_anamnesa() {
        $id_trans_anm = intval($this->input->post('id_trans_anm'));
        $medical_record_id = intval($this->input->post('medical_record_id'));

        if (empty($medical_record_id)) {
            echo json_encode(array('status' => 1, 'notif' => 'Data tidak lengkap!'));
            return;
        }

        $data = array(
            'medical_record_id' => $medical_record_id,
            'anm_temp'          => trim($this->input->post('anm_temp')),
            'anm_pulse'         => trim($this->input->post('anm_pulse')),
            'anm_respirasi'     => trim($this->input->post('anm_respirasi')),
            'anm_blood_press'   => trim($this->input->post('anm_blood_press')),
            'anm_height'        => trim($this->input->post('anm_height')),
            'anm_weight'        => trim($this->input->post('anm_weight')),
            'anm_stomatch_wide' => trim($this->input->post('anm_stomatch_wide')),
            'anm_note'          => trim($this->input->post('anm_note')),
        );

        if ($id_trans_anm > 0) {
            // UPDATE
            $data['anm_insert_dt'] = date('Y-m-d H:i:s');
            $this->M_anamnesa->update_anamnesa($id_trans_anm, $data);
            $msg = 'Data anamnesa berhasil diperbarui!';
        } else {
            // INSERT
            $data['anm_status'] = 1;
            $data['anm_insert_dt'] = date('Y-m-d H:i:s');
            $data['anm_insert_by'] = $this->session->userdata('sess_id');
            $this->M_anamnesa->insert_anamnesa($data);
            $msg = 'Data anamnesa berhasil disimpan!';
        }

        echo json_encode(array(
            'status' => 2,
            'notif'  => $msg,
        ));
    }

    /**
     * Batalkan visit dan medical record
     */
    public function act_batal() {
        $visit_id = intval($this->input->post('visit_id'));
        $now = date('Y-m-d H:i:s');
        $user_id = $this->session->userdata('sess_id');

        // Update trans_visit
        $this->db->where('id_visit', $visit_id);
        $this->db->update('trans_visit', array(
            'trans_status'    => 0,
            'trans_cancel_dt' => $now,
            'trans_cancel_by' => $user_id,
        ));

        // Update trans_medical_record
        $this->db->where('visit_id', $visit_id);
        $this->db->update('trans_medical_record', array(
            'mrd_status'    => 0,
            'mrd_cancel_dt' => $now,
            'mrd_cancel_by' => $user_id,
        ));

        echo json_encode(array(
            'status' => 2,
            'notif'  => 'Data berhasil dibatalkan.',
        ));
    }

    /**
     * Konversi tanggal dari format dd/mm/yyyy ke yyyy-mm-dd untuk MySQL
     */
    private function format_date_db($date) {
        if (empty($date)) return null;
        $clean = str_replace('/', '-', $date);
        return date('Y-m-d', strtotime($clean));
    }
}
