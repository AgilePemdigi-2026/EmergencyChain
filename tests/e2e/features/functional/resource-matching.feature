
@functional @resource-matching @sprint2
Feature: Resource Matching & Rematching
  Sebagai Pelapor, saya ingin mendapatkan penanganan
  Emergency Case sesuai kebutuhan Resource yang saya laporkan.
  Sebagai Instansi, saya ingin menyelesaikan penugasan agar
  Resource yang tersedia kembali dapat digunakan untuk
  menangani kasus lainnya.

  Background:
    Given database pengujian telah direset dan akun demo tersedia

  @RM-01 @positive
  Scenario: Saya mengirim laporan ketika seluruh Resource tersedia
    Given saya sebagai Pelapor memiliki akses ke unit Ambulans dan Polisi yang tersedia
    When saya mengirim laporan Emergency Case yang membutuhkan Ambulans dan Polisi
    Then saya melihat konfirmasi bahwa laporan berhasil dikirim
    And saya mendapatkan penugasan Ambulans dan Polisi untuk laporan saya
    And saya tidak mendapatkan penugasan Resource yang sama lebih dari satu kali

  @RM-02 @alternative
  Scenario: Saya mengirim laporan ketika sebagian Resource tidak tersedia
    Given saya sebagai Pelapor berada pada kondisi unit Ambulans tersedia dan Polisi tidak tersedia
    When saya mengirim laporan Emergency Case yang membutuhkan Ambulans dan Polisi
    Then saya melihat konfirmasi bahwa laporan berhasil dikirim
    And saya mendapatkan penugasan Ambulans untuk laporan saya
    And saya melihat kebutuhan Polisi pada laporan saya masih menunggu penugasan
    And saya tidak mendapatkan penugasan Resource yang sama lebih dari satu kali

  @RM-03 @alternative
  Scenario: Saya menyelesaikan penugasan dan menggunakan kembali Resource untuk kasus lain
    Given saya sebagai Instansi sedang menangani penugasan Polisi pada kasus pertama
    And saya memiliki kasus kedua yang sudah mendapatkan Ambulans tetapi masih menunggu Polisi
    When saya menyelesaikan penugasan Polisi pada kasus pertama
    Then saya melihat penugasan Polisi pada kasus pertama telah selesai
    And saya melihat unit Polisi telah digunakan untuk menangani kasus kedua
    And saya melihat kebutuhan Polisi pada kasus kedua tidak lagi menunggu penugasan
    And saya tidak menemukan penugasan Polisi ganda pada kasus kedua
