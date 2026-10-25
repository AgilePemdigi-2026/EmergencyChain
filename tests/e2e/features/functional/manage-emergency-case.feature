
@functional @manage-ec @sprint1
Feature: Manage Emergency Case
  Sebagai Petugas EmergencyChain
  Saya ingin memeriksa dan memperbarui informasi Emergency Case
  Agar kebutuhan penanganan sesuai kondisi kejadian.

  @MEC-01 @positive
  Scenario: Berhasil memperbarui kebutuhan penanganan
    Given Petugas EC telah login dan membuka detail laporan testing
    When Petugas EC membuka formulir edit informasi
    And Petugas EC mengubah kebutuhan penanganan
    And Petugas EC menyimpan perubahan informasi
    Then sistem menampilkan pesan pembaruan berhasil
    And kebutuhan penanganan terbaru ditampilkan

  @MEC-02 @alternative
  Scenario: Berhasil melengkapi informasi yang belum lengkap
    Given Petugas EC telah login dan membuka detail laporan testing
    When Petugas EC membuka formulir edit informasi
    And Petugas EC mengosongkan kondisi kejadian
    And Petugas EC melengkapi kondisi kejadian
    And Petugas EC menyimpan perubahan informasi
    Then sistem menampilkan pesan pembaruan berhasil
    And kondisi kejadian yang dilengkapi ditampilkan

  @MEC-03 @negative
  Scenario: Gagal menyimpan Emergency Case dengan kondisi kosong
    Given Petugas EC telah login dan membuka detail laporan testing
    When Petugas EC membuka formulir edit informasi
    And Petugas EC mengosongkan kondisi kejadian
    And Petugas EC mencoba menyimpan perubahan tidak valid
    Then browser menolak pembaruan Emergency Case
    And Petugas EC tetap berada di halaman detail laporan

  @MEC-04 @positive
  Scenario: Berhasil melihat informasi terbaru setelah pembaruan
    Given Petugas EC telah login dan membuka detail laporan testing
    When Petugas EC membuka formulir edit informasi
    And Petugas EC mengubah kondisi kejadian
    And Petugas EC menyimpan perubahan informasi
    Then sistem menampilkan pesan pembaruan berhasil
    When Petugas EC memuat ulang halaman detail laporan
    Then kondisi kejadian terbaru tetap ditampilkan
