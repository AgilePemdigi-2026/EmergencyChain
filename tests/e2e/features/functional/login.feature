
@functional @login @sprint1
Feature: Login EmergencyChain
  Sebagai pengguna EmergencyChain
  Saya ingin login menggunakan akun terdaftar
  Agar dapat mengakses fitur sesuai role.

  @LOG-01 @positive
  Scenario: Login berhasil menggunakan akun Pelapor
    Given pengguna berada di halaman Login
    When pengguna mengisi kredensial Pelapor yang valid
    And pengguna menekan tombol Login
    Then pengguna diarahkan ke halaman sesuai role Pelapor

  @LOG-02 @negative
  Scenario: Login gagal karena email dan password kosong
    Given pengguna berada di halaman Login
    When pengguna membiarkan email dan password kosong
    And pengguna mencoba menekan tombol Login
    Then browser menolak form Login yang belum lengkap
    And pengguna tetap berada di halaman Login

  @LOG-03 @negative
  Scenario: Login gagal karena kredensial salah
    Given pengguna berada di halaman Login
    When pengguna mengisi email terdaftar dengan password salah
    And pengguna menekan tombol Login
    Then sistem menampilkan kesalahan kredensial
    And pengguna tetap berada di halaman Login

  @LOG-04 @negative
  Scenario: Login gagal karena akun belum terdaftar
    Given pengguna berada di halaman Login
    When pengguna mengisi email yang belum terdaftar
    And pengguna menekan tombol Login
    Then sistem menampilkan kesalahan kredensial
    And pengguna tetap berada di halaman Login
