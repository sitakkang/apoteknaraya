<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Anamnesa extends CI_Controller {

    public $dir_v = 'anamnesa/';

    public function __construct() {
        parent::__construct();
        $this->m_auth->check_login();
        $this->m_auth->check_akses();
        $this->load->model('M_anamnesa');
        $this->load->model('M_dokter');   // dipakai endpoint modal (diagnosa/obat/racikan/SKS/SKBS/SKMB)
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
            'src/js/admin/exam_endpoint.js',
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
            'src/js/admin/exam_endpoint.js',
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

    // ================================================================
    // ENDPOINT MODAL ANAMNESA
    //
    // Semua aksi modal (Dokter / Diagnosa / Obat & Racikan / SKS / SKBS /
    // SKMB) ditangani di controller ini. Sebelumnya request dikirim ke
    // controller Dokter, tetapi Dokter::__construct() memakai aturan akses
    // menu "dokter" sehingga seluruh request dari menu Anamnesa ditolak
    // (redirect) dan tombol pada modal tidak berfungsi.
    //
    // View yang dipakai tetap view bersama di folder views/dokter/ supaya
    // tampilan halaman Anamnesa dan menu Dokter tetap identik.
    // Aturan akses menyesuaikan menu Anamnesa (level 1, 2, 3).
    // ================================================================

    // ---------------- Diagnosa ----------------

    public function reload_diagnosa() {
        $visit_id = intval($this->input->get('visit_id'));
        $row = $this->M_dokter->get_by_visit_id($visit_id);
        if (!$row) return;
        $diagnosa_terpilih = $this->M_dokter->get_diagnosa_by_medical_record($row->id_medical_record);
        $this->load->view('dokter/_diagnosa_table', array('diagnosa_terpilih' => $diagnosa_terpilih));
    }

    public function act_add_diagnosa() {
        $medical_record_id = intval($this->input->post('medical_record_id'));
        $dgn_id = intval($this->input->post('dgn_id'));

        if (!$medical_record_id || !$dgn_id) {
            echo json_encode(array('status' => 1, 'notif' => 'Data tidak lengkap!'));
            return;
        }

        $dgn = $this->db->get_where('ms_diagnosa', array('id_diagnosa' => $dgn_id))->row();
        if (!$dgn) {
            echo json_encode(array('status' => 1, 'notif' => 'Diagnosa tidak ditemukan!'));
            return;
        }

        $exists = $this->db->get_where('trans_diagnosa', array(
            'medical_record_id' => $medical_record_id,
            'trans_dgn_name'    => $dgn->dgn_name,
            'trans_dgn_status'  => 1
        ))->row();
        if ($exists) {
            echo json_encode(array('status' => 1, 'notif' => 'Diagnosa sudah ditambahkan!'));
            return;
        }

        $data = array(
            'medical_record_id'   => $medical_record_id,
            'trans_dgn_name'      => $dgn->dgn_name,
            'trans_dgn_cat'       => $dgn->dgn_cat,
            'trans_dgn_note'      => trim($this->input->post('dgn_note')),
            'trans_dgn_status'    => 1,
            'trans_dgn_insert_dt' => date('Y-m-d H:i:s'),
            'trans_dgn_insert_by' => $this->session->userdata('sess_id'),
        );
        $this->M_dokter->insert_diagnosa($data);

        echo json_encode(array('status' => 2, 'notif' => 'Diagnosa berhasil ditambahkan!'));
    }

    public function act_del_diagnosa() {
        $id = intval($this->input->post('id'));
        $this->M_dokter->delete_diagnosa($id);
        echo json_encode(array('status' => 2, 'notif' => 'Diagnosa berhasil dihapus!'));
    }

    // ---------------- Obat ----------------

    public function reload_obat() {
        $visit_id = intval($this->input->get('visit_id'));
        $row = $this->M_dokter->get_by_visit_id($visit_id);
        if (!$row) return;
        $obat_terpilih = $this->M_dokter->get_obat_by_medical_record($row->id_medical_record);
        $this->load->view('dokter/_obat_table', array('obat_terpilih' => $obat_terpilih));
    }

    public function act_add_obat() {
        $medical_record_id = intval($this->input->post('medical_record_id'));
        $obat_name = trim($this->input->post('obat_name'));

        if (!$medical_record_id || $obat_name === '') {
            echo json_encode(array('status' => 1, 'notif' => 'Nama obat wajib diisi!'));
            return;
        }

        $qty = intval($this->input->post('qty'));
        if ($qty < 1) $qty = 1;

        // Obat diinput manual (tanpa master ms_obat): cukup simpan teksnya
        $data = array(
            'medical_record_id'    => $medical_record_id,
            'trans_obat_name'      => $obat_name,
            'trans_obat_qty'       => $qty,
            'trans_obat_dosis'     => trim($this->input->post('dosis')),
            'trans_obat_status'    => 1,
            'trans_obat_insert_dt' => date('Y-m-d H:i:s'),
            'trans_obat_insert_by' => $this->session->userdata('sess_id'),
        );
        $this->M_dokter->insert_obat($data);

        echo json_encode(array('status' => 2, 'notif' => 'Obat berhasil ditambahkan!'));
    }

    public function act_del_obat() {
        $id = intval($this->input->post('id'));
        $this->M_dokter->delete_obat($id);
        echo json_encode(array('status' => 2, 'notif' => 'Obat berhasil dihapus!'));
    }

    // ---------------- Racikan ----------------

    public function reload_pulv() {
        $visit_id = intval($this->input->get('visit_id'));
        $row = $this->M_dokter->get_by_visit_id($visit_id);
        if (!$row) return;
        $this->load->view('dokter/racikan/v_list', array(
            'pulv_list'  => $this->M_dokter->get_pulv_by_medical_record($row->id_medical_record),
            'pulv_items' => $this->M_dokter->get_pulv_items_grouped($row->id_medical_record),
        ));
    }

    public function get_pulv_popup() {
        $medical_record_id = intval($this->input->get('mrd_id'));
        if (!$medical_record_id) {
            echo 'Data tidak lengkap';
            return;
        }
        $this->load->view('dokter/racikan/v_form', array(
            'medical_record_id' => $medical_record_id,
            'pulv_list'         => $this->M_dokter->get_pulv_by_medical_record($medical_record_id),
        ));
    }

    public function edit_pulv_popup() {
        $id = intval($this->input->get('id'));
        $pulv = $this->M_dokter->get_pulv_by_id($id);
        if (!$pulv) {
            echo 'Racikan tidak ditemukan';
            return;
        }
        $this->load->view('dokter/racikan/v_form_edit', array('pulv' => $pulv));
    }

    public function act_save_pulv() {
        $medical_record_id = intval($this->input->post('medical_record_id'));
        $option_pulv = $this->input->post('option_pulv');
        $obat_ids    = $this->input->post('obat_ids');

        if (empty($obat_ids) || !$medical_record_id) {
            echo json_encode(array('status' => 1, 'notif' => 'Pilih obat terlebih dahulu!'));
            return;
        }

        $obat_ids  = array_map('intval', (array) $obat_ids);
        $insert_by = $this->session->userdata('sess_id');

        if ($option_pulv === 'new') {
            $pulv_name  = trim($this->input->post('pulv_name'));
            $pulv_qty   = intval($this->input->post('pulv_qty'));
            $pulv_dosis = trim($this->input->post('pulv_dosis'));

            if (empty($pulv_name) || empty($pulv_dosis) || $pulv_qty < 1) {
                echo json_encode(array('status' => 1, 'notif' => 'Nama, dosis, dan jumlah racikan wajib diisi!'));
                return;
            }

            $this->db->trans_begin();
            $id_pulv = $this->M_dokter->insert_pulv(array(
                'medical_record_id' => $medical_record_id,
                'pulv_name'         => strtoupper($pulv_name),
                'pulv_notes'        => trim($this->input->post('pulv_notes')),
                'pulv_dosis'        => $pulv_dosis,
                'pulv_qty'          => $pulv_qty,
                'pulv_insert_by'    => $insert_by,
                'pulv_insert_dt'    => date('Y-m-d H:i:s'),
            ));
            if ($id_pulv) {
                $this->M_dokter->update_obat_pulv($obat_ids, $id_pulv);
            }
            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                echo json_encode(array('status' => 1, 'notif' => 'Gagal menyimpan racikan!'));
                return;
            }
            $this->db->trans_commit();
            $msg = 'Racikan berhasil disimpan!';
        } else {
            $id_pulv = intval($option_pulv);
            if (!$id_pulv) {
                echo json_encode(array('status' => 1, 'notif' => 'Racikan tujuan tidak valid!'));
                return;
            }
            $this->M_dokter->update_obat_pulv($obat_ids, $id_pulv);
            $msg = 'Obat berhasil ditambahkan ke racikan!';
        }

        echo json_encode(array('status' => 2, 'notif' => $msg));
    }

    public function act_update_pulv() {
        $id         = intval($this->input->post('id'));
        $pulv_name  = trim($this->input->post('pulv_name'));
        $pulv_qty   = intval($this->input->post('pulv_qty'));
        $pulv_dosis = trim($this->input->post('pulv_dosis'));

        if (empty($pulv_name) || empty($pulv_dosis) || $pulv_qty < 1) {
            echo json_encode(array('status' => 1, 'notif' => 'Nama, dosis, dan jumlah racikan wajib diisi!'));
            return;
        }

        $this->M_dokter->update_pulv($id, array(
            'pulv_name'  => strtoupper($pulv_name),
            'pulv_notes' => trim($this->input->post('pulv_notes')),
            'pulv_dosis' => $pulv_dosis,
            'pulv_qty'   => $pulv_qty,
        ));
        echo json_encode(array('status' => 2, 'notif' => 'Racikan berhasil diperbarui!'));
    }

    public function act_delete_pulv() {
        $id = intval($this->input->post('id'));
        $this->db->trans_begin();
        $this->M_dokter->unlink_obat_from_pulv($id);
        $this->M_dokter->delete_pulv($id);
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            echo json_encode(array('status' => 1, 'notif' => 'Gagal menghapus racikan!'));
            return;
        }
        $this->db->trans_commit();
        echo json_encode(array('status' => 2, 'notif' => 'Racikan berhasil dihapus!'));
    }

    // ---------------- SKS ----------------

    public function reload_sks() {
        $visit_id = intval($this->input->get('visit_id'));
        $row = $this->M_dokter->get_by_visit_id($visit_id);
        if (!$row) return;

        $sks = $this->M_dokter->get_sks_by_visit_id($visit_id);
        $data['row']                   = $row;
        $data['sks']                   = $sks;
        $data['sks_list']              = $sks ? array($sks) : array();
        $data['dokter_list']           = $this->M_dokter->get_all_doctor();
        $data['sks_diagnosa_default']  = $this->text_diagnosa_default($row->id_medical_record);
        $data['sks_terapi_default']    = $this->text_terapi_default($row->id_medical_record);

        $this->load->view('dokter/_sks_section', $data);
    }

    public function act_buat_sks() {
        $visit_id = intval($this->input->post('visit_id'));
        $row = $this->M_dokter->get_by_visit_id($visit_id);
        if (!$row) {
            echo json_encode(array('status' => 1, 'notif' => 'Data pasien tidak ditemukan!'));
            return;
        }
        if (!$row->trans_doct_by) {
            echo json_encode(array('status' => 1, 'notif' => 'Data dokter pemeriksa belum ditentukan!'));
            return;
        }

        $existing = $this->M_dokter->get_sks_by_visit_id($visit_id);
        $now      = date('Y-m-d H:i:s');
        $insertby = $this->session->userdata('sess_id');
        $doct_by  = $row->trans_doct_by;

        $age    = !empty($row->patient_bod) ? date_diff(date_create($row->patient_bod), date_create('now'))->y : ($existing->age ?? '');
        $gender = !empty($row->patient_gender) ? $row->patient_gender : ($existing->gender ?? '');

        $data = array(
            'visit_id'     => $visit_id,
            'patient_name' => strtoupper(trim($row->patient_name)),
            'company_name' => strtoupper(trim($row->trans_patient_company)),
            'patient_job'  => strtoupper(trim($row->patient_job)),
            'age'          => $age,
            'gender'       => $gender,
            'diagnosa'     => strtoupper(trim($this->input->post('diagnosa'))),
            'terapi'       => trim($this->input->post('terapi')),
            'datefrom'     => $this->format_date_db($this->input->post('datefrom')),
            'dateto'       => $this->format_date_db($this->input->post('dateto')),
            'docdate'      => $this->format_date_db($this->input->post('docdate')),
            'doctby'       => $doct_by,
            'alamat'       => strtoupper(trim($row->patient_address)),
        );

        if ($existing) {
            $data['updateby'] = $insertby;
            $data['updatedt'] = $now;
            $this->M_dokter->update_sks($existing->id, $data);
            $msg = 'SKS berhasil diperbarui! (No. ' . htmlspecialchars($existing->docnumb) . ')';
        } else {
            $data['docnumb']  = $this->M_dokter->generate_docnumb_sks();
            $data['insertby'] = $insertby;
            $data['insertdt'] = $now;
            $this->M_dokter->insert_sks($data);
            $msg = 'SKS berhasil dibuat! (No. ' . $data['docnumb'] . ')';
        }

        echo json_encode(array('status' => 2, 'notif' => $msg));
    }

    public function act_del_sks() {
        $id  = intval($this->input->post('id'));
        $row = $this->M_dokter->get_sks_by_id($id);

        if (!$row) {
            echo json_encode(array('status' => 1, 'notif' => 'Data SKS tidak ditemukan!'));
            return;
        }

        $this->M_dokter->delete_sks($id);
        echo json_encode(array('status' => 2, 'notif' => 'SKS ' . htmlspecialchars($row->docnumb) . ' berhasil dihapus!'));
    }

    // ---------------- SKBS ----------------

    public function reload_skbs() {
        $visit_id = intval($this->input->get('visit_id'));
        $row = $this->M_dokter->get_by_visit_id($visit_id);
        if (!$row) return;

        $data['row']         = $row;
        $data['skbs']        = $this->M_dokter->get_skbs_by_visit_id($visit_id);
        $data['dokter_list'] = $this->M_dokter->get_all_doctor();
        $this->load->view('dokter/_skbs_section', $data);
    }

    public function act_simpan_skbs() {
        $visit_id = intval($this->input->post('visit_id'));
        $row = $this->M_dokter->get_by_visit_id($visit_id);
        if (!$row) {
            echo json_encode(array('status' => 1, 'notif' => 'Data pasien tidak ditemukan!'));
            return;
        }
        if (!$row->trans_doct_by) {
            echo json_encode(array('status' => 1, 'notif' => 'Data dokter pemeriksa belum ditentukan!'));
            return;
        }

        $existing  = $this->M_dokter->get_skbs_by_visit_id($visit_id);
        $now       = date('Y-m-d H:i:s');
        $insert_by = $row->trans_doct_by;
        $doct      = $this->M_dokter->get_doct_by($insert_by);

        $data = array(
            'visit_id'                => $visit_id,
            'skbs_patient_name'       => strtoupper(trim($row->patient_name)),
            'skbs_patient_nik'        => $row->patient_nik,
            'skbs_patient_department' => strtoupper(trim($row->trans_patient_department)),
            'skbs_patient_company'    => strtoupper(trim($row->trans_patient_company)),
            'skbs_patient_ktp'        => $row->patient_ktp,
            'skbs_patient_age'        => !empty($row->patient_bod) ? date_diff(date_create($row->patient_bod), date_create('now'))->y : '0',
            'skbs_result_id'          => null,
            'skbs_result_name'        => strtoupper(trim($this->input->post('skbs_result'))),
            'skbs_desc'               => trim($this->input->post('skbs_desc')),
            'skbs_note'               => trim($this->input->post('skbs_note')),
            'skbs_td'                 => trim($this->input->post('skbs_td')),
            'skbs_bw'                 => trim($this->input->post('skbs_bw')),
            'skbs_tb'                 => trim($this->input->post('skbs_tb')),
            'skbs_bb'                 => trim($this->input->post('skbs_bb')),
            'skbs_doc_date'           => date('Y-m-d'),
            'skbs_doct_id'            => $insert_by,
            'skbs_doct_name'          => $doct ? $doct->fullname : '',
            'skbs_status'             => 1,
        );

        if ($existing) {
            $data['update_dt'] = $now;
            $data['update_by'] = $insert_by;
            $this->M_dokter->update_skbs($existing->id_skbs, $data);
            $msg = 'SKBS berhasil diperbarui!';
        } else {
            $data['insert_dt'] = $now;
            $data['insert_by'] = $insert_by;
            $this->M_dokter->insert_skbs($data);
            $msg = 'SKBS berhasil disimpan!';
        }

        echo json_encode(array('status' => 2, 'notif' => $msg));
    }

    public function act_del_skbs() {
        $id = intval($this->input->post('id'));
        $this->db->where('id_skbs', $id);
        $this->db->update('trans_skbs', array('skbs_status' => 0));
        echo json_encode(array('status' => 2, 'notif' => 'SKBS berhasil dihapus!'));
    }

    // ---------------- SKMB ----------------

    public function reload_skmb() {
        $visit_id = intval($this->input->get('visit_id'));
        $row = $this->M_dokter->get_by_visit_id($visit_id);
        if (!$row) return;

        $data['row']         = $row;
        $data['skmb']        = $this->M_dokter->get_skmb_by_visit_id($visit_id);
        $data['dokter_list'] = $this->M_dokter->get_all_doctor();
        $this->load->view('dokter/_skmb_section', $data);
    }

    public function act_simpan_skmb() {
        $visit_id = intval($this->input->post('visit_id'));
        $row = $this->M_dokter->get_by_visit_id($visit_id);
        if (!$row) {
            echo json_encode(array('status' => 1, 'notif' => 'Data pasien tidak ditemukan!'));
            return;
        }
        if (!$row->trans_doct_by) {
            echo json_encode(array('status' => 1, 'notif' => 'Data dokter pemeriksa belum ditentukan!'));
            return;
        }

        $existing  = $this->M_dokter->get_skmb_by_visit_id($visit_id);
        $now       = date('Y-m-d H:i:s');
        $insert_by = $row->trans_doct_by;
        $doct      = $this->M_dokter->get_doct_by($insert_by);

        $data = array(
            'visit_id'          => $visit_id,
            'patient_name'      => strtoupper(trim($this->input->post('patient_name'))),
            'nik'               => trim($this->input->post('nik')),
            'company_name'      => strtoupper(trim($this->input->post('company_name'))),
            'pengantar'         => strtoupper(trim($this->input->post('pengantar'))),
            'nik_pengantar'     => trim($this->input->post('nik_pengantar')),
            'company_pengantar' => strtoupper(trim($this->input->post('company_pengantar'))),
            'hubungan'          => $this->input->post('hubungan'),
            'tgl_datang'        => $this->format_date_db($this->input->post('tgl_datang')),
            'jam'               => trim($this->input->post('jam')),
            'docdate'           => date('Y-m-d'),
            'doct_by_id'        => $insert_by,
            'doct_by_name'      => $doct ? $doct->fullname : '',
        );

        if ($existing) {
            $data['updateby'] = $insert_by;
            $data['updatedt'] = $now;
            $this->M_dokter->update_skmb($existing->id, $data);
            $msg = 'SKMB berhasil diperbarui!';
        } else {
            $data['docnumb']  = $this->generate_docnumb_skmb();
            $data['insertby'] = $insert_by;
            $data['insertdt'] = $now;
            $this->M_dokter->insert_skmb($data);
            $msg = 'SKMB berhasil disimpan!';
        }

        echo json_encode(array('status' => 2, 'notif' => $msg));
    }

    public function act_del_skmb() {
        $id = intval($this->input->post('id'));
        $this->M_dokter->delete_skmb($id);
        echo json_encode(array('status' => 2, 'notif' => 'SKMB berhasil dihapus!'));
    }

    // ---------------- Dokter pemeriksa ----------------

    public function act_update_doctor() {
        $visit_id  = intval($this->input->post('visit_id'));
        $doctor_id = intval($this->input->post('doctor_id'));

        if (!$visit_id || !$doctor_id) {
            echo json_encode(array('status' => 1, 'notif' => 'Data tidak lengkap!'));
            return;
        }

        $this->db->where('visit_id', $visit_id);
        $this->db->update('trans_medical_record', array('mrd_doct_by' => $doctor_id));

        $this->db->where('id_visit', $visit_id);
        $this->db->update('trans_visit', array('trans_doct_by' => $doctor_id));

        echo json_encode(array('status' => 2, 'notif' => 'Dokter pemeriksa berhasil diubah.'));
    }

    /**
     * Nomor dokumen SKMB (running number per bulan)
     */
    private function generate_docnumb_skmb() {
        $month_roman = $this->month_roman(date('n'));
        $year = date('Y');

        $last = $this->db->query(
            "SELECT docnumb FROM skmb
             WHERE docnumb LIKE '%/SKMB/" . $month_roman . "/$year'
             ORDER BY id DESC LIMIT 1"
        )->row();

        if ($last) {
            $parts = explode('/', $last->docnumb);
            $next  = intval($parts[0]) + 1;
        } else {
            $next = 1;
        }

        return sprintf('%05d', $next) . '/SKMB/' . $month_roman . '/' . $year;
    }

    private function month_roman($n) {
        $map = array(1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
                     7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII');
        return isset($map[$n]) ? $map[$n] : '';
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
