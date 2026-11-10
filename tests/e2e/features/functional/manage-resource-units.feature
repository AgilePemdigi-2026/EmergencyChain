
@functional @manage-resource-units @sprint2
Feature: Manage Resource Units

  Sebagai Instansi, saya ingin mengelola unit operasional
  agar ketersediaan resource untuk penanganan Emergency Case
  dapat dikelola dengan baik.

  Background:
    Given database pengujian telah direset dan akun demo tersedia

  @RU-01 @positive @basic-flow
  Scenario: Saya melihat daftar unit operasional milik instansi
    Given saya login sebagai Instansi
    When saya membuka halaman Kelola Unit
    Then saya melihat daftar unit operasional milik instansi
    And saya melihat ringkasan jumlah unit berdasarkan ketersediaannya

  @RU-02 @positive @basic-flow
  Scenario: Saya menambahkan unit operasional baru
    Given saya berada di halaman Kelola Unit
    When saya membuka form Tambah Unit
    And saya mengisi informasi unit operasional yang baru
    And saya menyimpan unit tersebut
    Then saya melihat unit baru pada daftar unit operasional
    And saya melihat unit menggunakan lokasi pangkalan instansi

  @RU-03 @positive
  Scenario: Saya mengedit informasi unit operasional
    Given saya memiliki unit yang dapat diedit
    When saya membuka form Edit pada unit tersebut
    And saya memperbarui informasi unit operasional
    And saya menyimpan perubahan unit
    Then saya melihat informasi unit telah diperbarui

  @RU-04 @positive
  Scenario: Saya menghapus unit operasional
    Given saya memiliki unit yang dapat dihapus
    When saya membuka konfirmasi penghapusan unit
    And saya mengonfirmasi penghapusan unit
    Then saya tidak lagi melihat unit tersebut pada daftar operasional

  @RU-05 @positive
  Scenario: Saya mengubah ketersediaan unit operasional
    Given saya memiliki unit yang tidak sedang ditugaskan
    When saya mengubah ketersediaan unit menjadi Tidak Tersedia
    Then saya melihat status ketersediaan unit telah berubah menjadi Tidak Tersedia

  @RU-06 @negative
  Scenario: Saya tidak dapat mengedit dan menghapus unit yang sedang ditugaskan
    Given saya memiliki unit yang sedang ditugaskan
    When saya membuka halaman Kelola Unit
    Then saya melihat tombol Edit pada unit tersebut tidak dapat digunakan
    And saya melihat tombol Hapus pada unit tersebut tidak dapat digunakan
