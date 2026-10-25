
@journey @petugas-ec @sprint1
Feature: Petugas EC Emergency Case Journey
  Sebagai Petugas EC
  Saya ingin melihat dan memperbarui Emergency Case
  Agar informasi kejadian sesuai kondisi terbaru.

  Scenario: Petugas EC login, mengelola laporan, dan logout
    Given Petugas EC berada di halaman login
    When Petugas EC login dengan akun testing yang valid
    Then Petugas EC diarahkan ke halaman Manage Emergency Case

    When Petugas EC membuka detail Emergency Case
    Then informasi Emergency Case ditampilkan

    When Petugas EC memperbarui kondisi kejadian
    Then perubahan Emergency Case berhasil disimpan
    And perubahan tetap tampil setelah halaman dimuat ulang

    When Petugas EC melakukan logout
    Then Petugas EC kembali ke halaman login
