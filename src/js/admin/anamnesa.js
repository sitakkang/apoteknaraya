var tabel_anamnesa;

$(document).ready(function () {

    // --- Init Datepicker ---
    $('.datepicker').datetimepicker({
        datepicker: true,
        timepicker: false,
        format: 'd/m/Y',
        closeOnDateSelect: true,
        scrollMonth: false,
        scrollInput: false,
    });

    // Tombol kolom pemeriksaan (Dokter/Diagnosa/Obat/SKS/SKBS/SKMB) → membuka modal
    function anmSectionBtn(section, row, icon, label, jml) {
        jml = parseInt(jml) || 0;
        return '<button class="ds-act-btn ds-act-view btn-anm-section" data-section="' + section + '" data-visit-id="' + row.DT_RowId + '" title="' + label + ' (' + jml + ')">' +
                   '<i class="fa ' + icon + '"></i>' +
                   (jml > 0 ? '<span style="font-size:10px;font-weight:700;margin-left:2px;">' + jml + '</span>' : '') +
               '</button>';
    }

    // --- DataTable (mulai kosong) ---
    tabel_anamnesa = $('#tabel_anamnesa').DataTable({
        processing: true,
        serverSide: false,
        scrollY: "500px",
        deferRender: true,
        scrollX: true,
        scrollCollapse: true,
        fixedColumns: {
            leftColumns: 2,
            rightColumns: 2
        },
        data: [],
        columns: [
            { data: '0', width: '40px' },
            { data: '1' },
            { data: '2' },
            { data: '3' },
            { data: '4', className: 'text-center' },
            { data: '5', className: 'text-center' },
            { data: '6', className: 'text-center' },
            {
                data: null,
                width: '110px',
                orderable: false,
                className: 'text-center',
                render: function (data, type, row) {
                    return '<div class="ds-act-group">' +
                               '<button class="ds-act-btn ds-act-view view-row-btn" data-id="' + row.DT_RowId + '" title="Lihat Detail">' +
                                   '<i class="fa fa-eye"></i>' +
                               '</button>' +
                               '<a href="' + site_url + 'anamnesa/periksa/' + row.DT_RowId + '" class="ds-act-btn ds-act-print" title="Pemeriksaan: Dokter, Diagnosa, Obat, SKS, SKBS, SKMB">' +
                                   '<i class="fa fa-stethoscope"></i>' +
                               '</a>' +
                           '</div>';
                }
            },
            {
                data: null,
                width: '170px',
                orderable: false,
                className: 'text-center',
                render: function (data, type, row) {
                    var btn;
                    if (row.has_anamnesa) {
                        btn = '<button class="ds-btn-anm ds-btn-anm-edit anamnesa-row-btn" data-visit-id="' + row.DT_RowId + '" title="Ubah Anamnesa">' +
                                   '<i class="fa fa-pen"></i> Ubah' +
                              '</button>';
                    } else {
                        btn = '<button class="ds-btn-anm ds-btn-anm-add anamnesa-row-btn" data-visit-id="' + row.DT_RowId + '" title="Tambah Anamnesa">' +
                                   '<i class="fa fa-plus-circle"></i> Tambah' +
                              '</button>';
                    }
                    return '<div style="display:flex;gap:4px;justify-content:center;">' +
                               btn +
                               '<button class="ds-btn-anm ds-btn-anm-cancel batal-row-btn" data-visit-id="' + row.DT_RowId + '" title="Batalkan">' +
                                   '<i class="fa fa-times"></i> Batal' +
                               '</button>' +
                           '</div>';
                }
            },
            // ---- Kolom aksi pemeriksaan — semua aksinya lewat modal ----
            {
                data: null,
                width: '90px',
                orderable: false,
                className: 'text-center',
                render: function (data, type, row) {
                    var nama = row.mrd_doct_name || '';
                    return '<button class="ds-act-btn ds-act-edit btn-anm-dokter" data-visit-id="' + row.DT_RowId + '" title="Dokter Pemeriksa: ' + (nama || 'belum ditentukan') + '">' +
                               '<i class="fa fa-user-md"></i>' +
                           '</button>' +
                           (nama ? '' : ' <i class="fa fa-exclamation-circle" style="color:#e67e22;font-size:11px;" title="Dokter belum dipilih"></i>');
                }
            },
            {
                data: null, width: '90px', orderable: false, className: 'text-center',
                render: function (data, type, row) { return anmSectionBtn('diagnosa', row, 'fa-stethoscope', 'Diagnosa', row.jml_diagnosa); }
            },
            {
                data: null, width: '90px', orderable: false, className: 'text-center',
                render: function (data, type, row) { return anmSectionBtn('obat', row, 'fa-pills', 'Obat & Racikan', row.jml_obat); }
            },
            {
                data: null, width: '80px', orderable: false, className: 'text-center',
                render: function (data, type, row) { return anmSectionBtn('sks', row, 'fa-file-signature', 'SKS', row.jml_sks); }
            },
            {
                data: null, width: '80px', orderable: false, className: 'text-center',
                render: function (data, type, row) { return anmSectionBtn('skbs', row, 'fa-heartbeat', 'SKBS', row.jml_skbs); }
            },
            {
                data: null, width: '80px', orderable: false, className: 'text-center',
                render: function (data, type, row) { return anmSectionBtn('skmb', row, 'fa-ambulance', 'SKMB', row.jml_skmb); }
            },
        ],
        language: {
            processing: 'Memuat data...',
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ baris',
            info: 'Menampilkan _START_ – _END_ dari _TOTAL_ data',
            infoEmpty: 'Belum ada data',
            zeroRecords: 'Belum ada data',
            emptyTable: 'Silakan pilih tanggal dan klik Tampilkan',
            paginate: { previous: '&laquo;', next: '&raquo;' },
        },
        order: [[0, 'asc']],
        responsive: true,
        autoWidth: false,
    });

    // --- Fungsi load data by date ---
    function loadData() {
        var date = $('#filter_date').val().trim();
        if (date == '') {
            notifNo('Silakan pilih tanggal terlebih dahulu');
            return;
        }
        tabel_anamnesa.ajax.url(site_url + 'anamnesa/table?date=' + encodeURIComponent(date)).load();
    }

    // --- Tombol Tampilkan ---
    $('#search_btn').on('click', function () {
        loadData();
    });

    // --- Enter pada field date ---
    $('#filter_date').on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            loadData();
        }
    });

    // --- Helper: show modal ---
    function showDsModal(title, contentHtml, footerHtml, size) {
        $('#MyModalTitle').html(title);
        $('#MyModalContent').html(contentHtml);
        $('#MyModalFooter').html(footerHtml);
        $('.modal-dialog').addClass('ds-modal');
        if (size) {
            $('.modal-dialog').addClass(size);
        }
        $('#MyModal').modal('show');
    }

    // --- Tambah / Ubah Anamnesa ---
    $('#tabel_anamnesa tbody').on('click', '.anamnesa-row-btn', function () {
        var visitId = $(this).data('visit-id');
        var isEdit = $(this).hasClass('ds-btn-anm-edit');
        var title = isEdit ? 'Ubah Anamnesa' : 'Tambah Anamnesa';

        $.get(site_url + 'anamnesa/anamnesa_form', { visit_id: visitId }, function (html) {
            showDsModal(
                '<i class="fa fa-notes-medical"></i> ' + title,
                html,
                '<button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>' +
                '<button type="button" class="ds-btn-action ds-btn-green" id="save_anamnesa_btn" style="padding:8px 22px">' +
                    '<i class="fa fa-save"></i> Simpan</button>',
                'modal-lg'
            );
        });
    });

    $(document).on('click', '#save_anamnesa_btn', function () {
        var payload = {
            id_trans_anm:     $('#anamnesa_id').val(),
            medical_record_id: $('#medical_record_id').val(),
            anm_temp:         $('#anm_temp').val(),
            anm_pulse:        $('#anm_pulse').val(),
            anm_respirasi:    $('#anm_respirasi').val(),
            anm_blood_press:  $('#anm_blood_press').val(),
            anm_height:       $('#anm_height').val(),
            anm_weight:       $('#anm_weight').val(),
            anm_stomatch_wide:$('#anm_stomatch_wide').val(),
            anm_note:         $('#anm_note').val(),
        };
        $.post(site_url + 'anamnesa/act_anamnesa', payload, function (res) {
            if (res.status == 1) {
                notifNo(res.notif);
            } else if (res.status == 2) {
                $('#MyModal').modal('hide');
                notifYesAuto(res.notif);
                // Reload table to update button status
                loadData();
            }
        }, 'json');
    });

    // --- Lihat Detail ---
    $('#tabel_anamnesa tbody').on('click', '.view-row-btn', function () {
        var id = $(this).data('id');
        $.get(site_url + 'anamnesa/detail', { id: id }, function (html) {
            showDsModal(
                '<i class="fa fa-file-text-o"></i> Detail Anamnesa',
                html,
                '',
                'modal-md'
            );
        });
    });

    // --- Batal Anamnesa ---
    $('#tabel_anamnesa tbody').on('click', '.batal-row-btn', function () {
        var visitId = $(this).data('visit-id');
        swal({
            title: 'Yakin membatalkan?',
            text: 'Data anamnesa dan kunjungan akan dibatalkan.',
            type: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Batalkan',
            cancelButtonText: 'Tidak',
            confirmButtonColor: '#d33',
        }).then(function (result) {
            if (!result.value) return;
            $.post(site_url + 'anamnesa/act_batal', { visit_id: visitId }, function (res) {
                if (res.status == 1) notifNo(res.notif);
                else { notifYesAuto(res.notif); loadData(); }
            }, 'json');
        });
    });

    // ================================================================
    // KOLOM PEMERIKSAAN — Dokter / Diagnosa / Obat / SKS / SKBS / SKMB
    // Semua aksi (CRUD) dibuka lewat modal, isinya sama seperti menu Dokter
    // ================================================================
    var sectionMeta = {
        dokter:   { title: '<i class="fa fa-user-md"></i> Pilih Dokter Pemeriksa', size: 'modal-sm' },
        diagnosa: { title: '<i class="fa fa-stethoscope"></i> Diagnosa Pasien', size: 'modal-lg' },
        obat:     { title: '<i class="fa fa-pills"></i> Obat & Racikan', size: 'modal-lg' },
        sks:      { title: '<i class="fa fa-file-signature"></i> Surat Keterangan Sakit', size: 'modal-lg' },
        skbs:     { title: '<i class="fa fa-heartbeat"></i> SKBS', size: 'modal-lg' },
        skmb:     { title: '<i class="fa fa-ambulance"></i> SKMB', size: 'modal-lg' }
    };

    // Plugin yang perlu di-init ulang setiap isi modal dimuat
    function initAnmModalPlugins() {
        $('#MyModalContent .autocomplete').chosen({ width: '100%' });
        $('#MyModalContent .datepicker').datetimepicker({
            datepicker: true, timepicker: false, format: 'd/m/Y',
            closeOnDateSelect: true, scrollMonth: false, scrollInput: false,
        });
        if ($.fn.clockpicker) {
            $('#MyModalContent .clockpicker').clockpicker({
                autoclose: true, donetext: 'OK', placement: 'bottom', align: 'left',
            });
        }
    }

    function openAnmModal(section, visitId) {
        var meta = sectionMeta[section];
        if (!meta) return;
        window.examModalOpen = { section: section, vid: visitId };   // dipakai pemeriksaandokter.js
        $.get(site_url + 'anamnesa/modal/' + section + '/' + visitId, function (html) {
            showDsModal(meta.title, html,
                '<button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>',
                meta.size);
            $('.modal-dialog').removeClass('modal-sm modal-md modal-lg').addClass(meta.size);
            initAnmModalPlugins();
        });
    }

    $('#tabel_anamnesa tbody').on('click', '.btn-anm-section, .btn-anm-dokter', function () {
        openAnmModal($(this).data('section') || 'dokter', $(this).data('visit-id'));
    });

    // Dipakai pemeriksaandokter.js saat section yang tampil di modal perlu dimuat ulang
    // (mis. setelah simpan/ubah/hapus racikan yang popup-nya memakai modal yang sama)
    window.examModalRefresh = function (section, vid) {
        if (!window.examModalOpen) return;
        var meta = sectionMeta[section] || {};
        $.get(site_url + 'anamnesa/modal/' + section + '/' + vid, function (html) {
            $('#MyModalTitle').html(meta.title || '');
            $('#MyModalContent').html(html);
            $('#MyModalFooter').html('<button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>');
            initAnmModalPlugins();
            $('#MyModal').modal('show');
        });
    };

    // Pilih dokter pemeriksa dari modal kolom Dokter
    $(document).on('click', '.pilih-dokter-modal', function () {
        var doctorId = $(this).data('id');
        var visitId  = $('#MyModalContent').find('#visit_id_sks').val();
        if (!doctorId || !visitId) return;
        $.post(site_url + 'anamnesa/act_update_doctor', { visit_id: visitId, doctor_id: doctorId }, function (res) {
            if (res.status == 1) {
                notifNo(res.notif);
            } else {
                $('#MyModal').modal('hide');
                notifYesAuto(res.notif);
                loadData();   // perbarui kolom Dokter pada tabel
            }
        }, 'json');
    });

    // --- Auto load data saat pertama kali (dengan default date hari ini) ---
    setTimeout(function () {
        loadData();
    }, 500);

});
