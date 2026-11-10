
@functional @hospital-matching @sprint2
Feature: Hospital Matching & Referral

  Sebagai Petugas EC, saya ingin memantau proses pencarian
  dan memverifikasi kebutuhan rujukan Rumah Sakit agar
  Emergency Case mendapatkan rujukan yang sesuai.

  Sebagai Petugas Rumah Sakit, saya ingin melihat dan
  merespons permintaan rujukan agar proses rujukan
  dapat berjalan dengan baik.

  Background:
    Given database pengujian telah direset dan akun demo tersedia

  @HM-01 @positive @basic-flow
  Scenario: Saya melihat hasil pencarian Rumah Sakit untuk Emergency Case
    Given saya memiliki Emergency Case yang membutuhkan rujukan dan Rumah Sakit kandidat yang valid
    When saya membuka halaman Hospital Matching untuk Emergency Case tersebut
    Then saya melihat permintaan rujukan dikirim kepada Rumah Sakit terdekat
    And saya melihat urutan permintaan dan batas waktu responsnya

  @HM-02 @positive
  Scenario: Saya menerima permintaan rujukan Rumah Sakit
    Given saya memiliki permintaan rujukan aktif yang ditujukan kepada Rumah Sakit saya
    When saya membuka dashboard permintaan rujukan
    And saya membuka detail permintaan rujukan tersebut
    And saya menerima permintaan rujukan
    Then saya melihat status permintaan menjadi Diterima
    And saya melihat Rumah Sakit saya ditetapkan sebagai tujuan rujukan

  @HM-03 @positive
  Scenario: Saya memverifikasi kebutuhan rujukan dan memulai Hospital Matching
    Given saya memiliki Emergency Case yang memerlukan verifikasi kebutuhan rujukan
    When saya membuka halaman Hospital Matching untuk Emergency Case tersebut
    And saya memverifikasi bahwa Emergency Case membutuhkan rujukan Rumah Sakit
    Then saya melihat permintaan rujukan dikirim kepada Rumah Sakit kandidat pertama
    And saya melihat proses rujukan berada dalam status Sedang Mencari Rumah Sakit

  @HM-04 @positive @alternative
  Scenario: Saya menolak permintaan rujukan dan permintaan diteruskan ke Rumah Sakit berikutnya
    Given saya memiliki proses rujukan dengan permintaan aktif kepada Rumah Sakit kandidat pertama
    When Petugas Rumah Sakit kandidat pertama menolak permintaan rujukan
    Then saya melihat permintaan pertama berstatus Ditolak
    And saya melihat permintaan berikutnya dikirim kepada Rumah Sakit kandidat selanjutnya

  @HM-05 @positive @alternative
  Scenario: Saya menerima permintaan rujukan setelah permintaan sebelumnya mengalami timeout
    Given saya merupakan Petugas Rumah Sakit kandidat berikutnya
    And permintaan rujukan kepada kandidat sebelumnya masih menunggu respons
    When batas waktu permintaan sebelumnya berakhir
    And saya membuka dashboard permintaan rujukan
    Then saya melihat permintaan baru untuk Emergency Case tersebut
    When saya menerima permintaan rujukan
    Then saya melihat status permintaan menjadi Diterima
    And saya melihat Rumah Sakit saya ditetapkan sebagai tujuan rujukan

  @HM-06 @negative
  Scenario: Saya melihat tidak ada Rumah Sakit kandidat yang memenuhi syarat
    Given saya memiliki Emergency Case yang membutuhkan rujukan tanpa Rumah Sakit kandidat yang valid
    When saya membuka halaman Hospital Matching untuk Emergency Case tersebut
    Then saya melihat proses rujukan berakhir dengan status Gagal
    And saya melihat informasi penyebab kegagalan rujukan

  @HM-07 @negative
  Scenario: Saya melihat proses rujukan gagal setelah seluruh Rumah Sakit kandidat menolak
    Given saya memiliki proses rujukan dengan beberapa Rumah Sakit kandidat
    When seluruh Rumah Sakit kandidat menolak permintaan rujukan
    Then saya melihat seluruh permintaan rujukan berstatus Ditolak
    And saya melihat proses rujukan berakhir dengan status Gagal
    And saya melihat informasi bahwa tidak ada Rumah Sakit yang menerima rujukan
