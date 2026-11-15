
@functional @agency-treatment-history @sprint3
Feature: Riwayat Penanganan Instansi
  Sebagai Instansi, saya ingin melihat riwayat Assignment
  yang telah selesai agar saya dapat meninjau informasi
  Emergency Case, Resource, dan proses penanganan sebelumnya.

  Background:
    Given database pengujian telah direset dan akun demo tersedia

  @ATH-01 @positive
  Scenario: Saya menyelesaikan Assignment dan melihatnya pada Riwayat Penanganan
    Given saya sebagai Instansi memiliki Assignment yang sedang dalam penanganan
    When saya menyelesaikan Assignment melalui halaman Status Emergency
    Then saya melihat status Assignment berubah menjadi selesai
    When saya membuka Dashboard Penanganan
    Then saya tidak melihat card Emergency Case yang sudah selesai
    When saya membuka halaman Riwayat Penanganan
    Then saya melihat Assignment yang telah selesai pada daftar riwayat
    And saya hanya melihat Assignment berstatus selesai pada daftar riwayat

  @ATH-02 @positive
  Scenario: Saya melihat detail Riwayat Penanganan yang telah selesai
    Given saya sebagai Instansi memiliki Assignment yang telah selesai
    When saya membuka detail Assignment pada halaman Riwayat Penanganan
    Then saya melihat informasi Emergency Case dan Resource yang ditugaskan
    And saya melihat waktu penyelesaian Assignment
    And saya melihat timeline perubahan status penanganan
    And saya melihat catatan lapangan yang tercatat selama penanganan

  @ATH-03 @alternative
  Scenario: Saya mencari dan memfilter Riwayat Penanganan
    Given saya sebagai Instansi memiliki beberapa Assignment yang telah selesai
    When saya mencari Riwayat Penanganan berdasarkan Case ID, jenis kejadian, atau lokasi
    Then saya melihat daftar riwayat yang sesuai dengan pencarian
    When saya memfilter Riwayat Penanganan berdasarkan bulan penyelesaian
    Then saya melihat daftar riwayat yang sesuai dengan bulan yang dipilih
    And saya hanya melihat Assignment berstatus selesai pada hasil filter

  @ATH-04 @alternative
  Scenario: Saya membuka Riwayat Penanganan ketika belum ada Assignment selesai
    Given saya sebagai Instansi belum memiliki Assignment yang selesai
    When saya membuka halaman Riwayat Penanganan
    Then saya melihat pesan bahwa belum ada Riwayat Penanganan
    And saya tidak melihat data Assignment pada tabel riwayat
