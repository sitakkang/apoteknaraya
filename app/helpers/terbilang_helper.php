<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Helper terbilang — angka menjadi kata dalam bahasa Indonesia.
 *
 * Dipakai pada dokumen cetak supaya angka juga tertulis dalam huruf,
 * contoh: 2 → "dua" sehingga tampil "2 (dua) hari".
 *
 * Di-autoload melalui app/config/autoload.php.
 */
if ( ! function_exists('terbilang'))
{
    /**
     * Ubah angka menjadi kata bahasa Indonesia (huruf kecil).
     *
     * @param  mixed $angka Angka yang akan diubah (mis. 2, 15, 30)
     * @return string       Mis. 2 → 'dua', 15 → 'lima belas', 1.250 → 'seribu dua ratus lima puluh'
     */
    function terbilang($angka)
    {
        $angka   = abs((int) $angka);
        $satuan  = array('', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas');

        if ($angka < 1) {
            return 'nol';
        }
        if ($angka < 12) {
            return $satuan[$angka];
        }
        if ($angka < 20) {
            return terbilang($angka - 10) . ' belas';
        }
        if ($angka < 100) {
            return trim(terbilang(intdiv($angka, 10)) . ' puluh ' . ($angka % 10 ? terbilang($angka % 10) : ''));
        }
        if ($angka < 200) {
            return trim('seratus ' . ($angka % 100 ? terbilang($angka % 100) : ''));
        }
        if ($angka < 1000) {
            return trim(terbilang(intdiv($angka, 100)) . ' ratus ' . ($angka % 100 ? terbilang($angka % 100) : ''));
        }
        if ($angka < 2000) {
            return trim('seribu ' . ($angka % 1000 ? terbilang($angka % 1000) : ''));
        }
        if ($angka < 1000000) {
            return trim(terbilang(intdiv($angka, 1000)) . ' ribu ' . ($angka % 1000 ? terbilang($angka % 1000) : ''));
        }
        if ($angka < 1000000000) {
            return trim(terbilang(intdiv($angka, 1000000)) . ' juta ' . ($angka % 1000000 ? terbilang($angka % 1000000) : ''));
        }

        // Angka yang tidak wajar (di atas 1 miliar) dikembalikan apa adanya.
        return (string) $angka;
    }
}
