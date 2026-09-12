# Sertifikat CA untuk Condition SATUSEHAT

`windows-root-ca.pem` berisi sertifikat publik dari trusted root store Windows
(`LocalMachine\Root` dan `CurrentUser\Root`) pada mesin pengembangan, diekspor
pada 13 September 2026. Tidak berisi private key.

Verifikasi sertifikat dan hostname pada pengiriman Condition saat ini
dinonaktifkan untuk localhost. Bundle ini tidak digunakan oleh pengiriman
Condition pada konfigurasi tersebut.

Saat dipasang pada production, aktifkan kembali `CURLOPT_SSL_VERIFYPEER=true`
dan `CURLOPT_SSL_VERIFYHOST=2`, lalu atur `curl.cainfo` di PHP yang melayani web ke
bundle CA yang dipercaya server tersebut. Perbarui bundle lokal jika trusted
root store Windows berubah.
