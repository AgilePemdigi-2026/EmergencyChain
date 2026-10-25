
@functional @register @sprint1
Feature: Register Pelapor
  Sebagai calon Pelapor
  Saya ingin mendaftarkan akun EmergencyChain
  Agar saya dapat mengakses fitur pelaporan keadaan darurat.

  @REG-01 @positive
  Scenario: Registrasi berhasil dengan data valid
    Given calon Pelapor berada di halaman registrasi
    When calon Pelapor mengisi seluruh data registrasi yang valid
    And calon Pelapor mengirimkan formulir registrasi
    Then akun Pelapor berhasil didaftarkan
    And calon Pelapor berhasil masuk ke sistem

  @REG-02 @negative
  Scenario: Registrasi gagal karena field wajib kosong
    Given calon Pelapor berada di halaman registrasi
    When calon Pelapor membiarkan field wajib kosong
    And calon Pelapor mencoba mengirimkan formulir registrasi
    Then registrasi ditolak karena data wajib belum lengkap
    And calon Pelapor tetap berada di halaman registrasi

  @REG-03 @negative
  Scenario: Registrasi gagal karena email sudah terdaftar
    Given calon Pelapor berada di halaman registrasi
    And email Pelapor testing sudah terdaftar
    When calon Pelapor mengisi registrasi menggunakan email tersebut
    And calon Pelapor mengirimkan formulir registrasi
    Then registrasi ditolak karena email sudah terdaftar
    And calon Pelapor tetap berada di halaman registrasi

  @REG-04 @negative
  Scenario: Registrasi gagal karena konfirmasi password tidak sesuai
    Given calon Pelapor berada di halaman registrasi
    When calon Pelapor mengisi data valid dengan konfirmasi password berbeda
    And calon Pelapor mengirimkan formulir registrasi
    Then registrasi ditolak karena konfirmasi password tidak sesuai
    And calon Pelapor tetap berada di halaman registrasi
