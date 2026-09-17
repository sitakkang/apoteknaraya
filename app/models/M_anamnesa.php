<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class M_anamnesa extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Get visit + medical record by trans_doc date
     * Menampilkan semua pasien yang daftar di tanggal tsb,
     * dengan LEFT JOIN ke trans_anamnesa (jika sudah ada).
     */
    public function get_by_date($date) {
        $sql = "SELECT 
                    A.*,
                    B.id_medical_record,
                    B.mrd_status,
                    B.mrd_vst_type,
                    B.mrd_doct_by,
                    B.mrd_insert_dt AS mrd_insert_dt,
                    B.mrd_insert_by AS mrd_insert_by,
                    C.patient_name,
                    C.patient_nik,
                    C.patient_ktp,
                    C.patient_bod,
                    C.patient_address,
                    D.id_trans_anm,
                    D.anm_temp,
                    D.anm_pulse,
                    D.anm_respirasi,
                    D.anm_blood_press,
                    D.anm_height,
                    D.anm_note,
                    D.anm_stomatch_wide,
                    D.anm_status AS anm_status,
                    E.fullname AS mrd_doct_name,
                    (SELECT COUNT(*) FROM trans_diagnosa   X1 WHERE X1.medical_record_id = B.id_medical_record AND X1.trans_dgn_status = 1) AS jml_diagnosa,
                    (SELECT COUNT(*) FROM trans_obat       X2 WHERE X2.medical_record_id = B.id_medical_record AND X2.trans_obat_status = 1) AS jml_obat,
                    (SELECT COUNT(*) FROM trans_obat_racikan X3 WHERE X3.medical_record_id = B.id_medical_record) AS jml_pulv,
                    (SELECT COUNT(*) FROM sks              X4 WHERE X4.visit_id = A.id_visit) AS jml_sks,
                    (SELECT COUNT(*) FROM trans_skbs       X5 WHERE X5.visit_id = A.id_visit AND X5.skbs_status = 1) AS jml_skbs,
                    (SELECT COUNT(*) FROM skmb             X6 WHERE X6.visit_id = A.id_visit) AS jml_skmb
                FROM trans_visit A
                LEFT JOIN trans_medical_record B ON A.id_visit = B.visit_id
                LEFT JOIN ms_patient C ON A.patient_id = C.id_patient
                LEFT JOIN trans_anamnesa D ON B.id_medical_record = D.medical_record_id AND D.anm_status = 1
                LEFT JOIN conf_users E ON B.mrd_doct_by = E.id_user
                WHERE A.trans_doc = ?
                  AND A.trans_status = 1
                  AND B.mrd_status = 1
                ORDER BY A.id_visit DESC";

        return $this->db->query($sql, array($date));
    }

    /**
     * Get single record by visit_id
     */
    public function get_by_visit_id($visit_id) {
        $sql = "SELECT 
                    A.*,
                    B.id_medical_record,
                    B.mrd_status,
                    B.mrd_vst_type,
                    B.mrd_doct_by,
                    B.mrd_insert_dt AS mrd_insert_dt,
                    B.mrd_insert_by AS mrd_insert_by,
                    C.patient_name,
                    C.patient_nik,
                    C.patient_ktp,
                    C.patient_bod,
                    C.patient_address,
                    D.fullname AS insert_fullname,
                    E.fullname AS mrd_insert_fullname
                FROM trans_visit A
                LEFT JOIN trans_medical_record B ON A.id_visit = B.visit_id
                LEFT JOIN ms_patient C ON A.patient_id = C.id_patient
                LEFT JOIN conf_users D ON A.trans_insert_by = D.id_user
                LEFT JOIN conf_users E ON B.mrd_insert_by = E.id_user
                WHERE A.id_visit = ?
                LIMIT 1";

        return $this->db->query($sql, array(intval($visit_id)))->row();
    }

    // ----------------------------------------------------------------
    // Anamnesa CRUD
    // ----------------------------------------------------------------

    /**
     * Get anamnesa by medical_record_id
     */
    public function get_anamnesa_by_medical_record($medical_record_id) {
        return $this->db->get_where('trans_anamnesa', array(
            'medical_record_id' => intval($medical_record_id),
            'anm_status' => 1
        ))->row();
    }

    /**
     * Insert trans_anamnesa
     */
    public function insert_anamnesa($data) {
        $this->db->insert('trans_anamnesa', $data);
        return $this->db->insert_id();
    }

    /**
     * Update trans_anamnesa
     */
    public function update_anamnesa($id, $data) {
        $this->db->where('id_trans_anm', intval($id));
        $this->db->update('trans_anamnesa', $data);
        return $this->db->affected_rows();
    }
}
