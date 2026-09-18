<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Helper nomor dokumen (kode surat) SKS / SKMB / SKBS.
 *
 * Running number SELALU diisi "00000" dan ditampilkan pada form Create & Update,
 * sehingga user dapat memperbarui nomornya secara manual sebelum disimpan.
 * Contoh keluaran: 00000/SKS/IX/2026
 *
 * Di-autoload melalui app/config/autoload.php.
 */
if ( ! function_exists('docnumb_default'))
{
    /**
     * Nomor dokumen default untuk kode surat tertentu.
     *
     * @param  string $kode Kode surat: SKS, SKMB, atau IMIP-SKBS
     * @return string       Contoh: 00000/SKS/IX/2026
     */
    function docnumb_default($kode)
    {
        $kode = strtoupper(trim((string) $kode));

        $roman_map = array(
            1  => 'I',   2  => 'II',  3  => 'III', 4  => 'IV',
            5  => 'V',   6  => 'VI',  7  => 'VII', 8  => 'VIII',
            9  => 'IX',  10 => 'X',   11 => 'XI',  12 => 'XII',
        );

        $bulan = (int) date('n');
        $roman = isset($roman_map[$bulan]) ? $roman_map[$bulan] : '';

        return '00000/' . $kode . '/' . $roman . '/' . date('Y');
    }
}

if ( ! function_exists('docnumb_value'))
{
    /**
     * Nomor dokumen yang dipakai pada form: pakai nomor tersimpan bila ada,
     * kalau kosong (mis. data lama) tampilkan nomor default 00000.
     *
     * @param  string $kode  Kode surat: SKS, SKMB, atau IMIP-SKBS
     * @param  mixed  $value Nilai docnumb tersimpan
     * @return string
     */
    function docnumb_value($kode, $value)
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : docnumb_default($kode);
    }
}
