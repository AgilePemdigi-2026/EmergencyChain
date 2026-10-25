
@functional @create-ec @sprint1
Feature: Create Emergency Case Report
  Sebagai Pelapor
  Saya ingin membuat laporan keadaan darurat
  Agar kejadian dapat tercatat dalam EmergencyChain.

  @EC-01 @positive
  Scenario: Berhasil membuat Emergency Case dengan data valid
    Given Pelapor telah login dan berada di halaman Buat Laporan
    When Pelapor mengisi seluruh data laporan yang valid
    And Pelapor menentukan lokasi kejadian pada peta
    And Pelapor menekan tombol Kirim Laporan
    Then laporan Emergency Case berhasil dibuat

  @EC-02 @negative
  Scenario: Gagal membuat Emergency Case karena data tidak lengkap
    Given Pelapor telah login dan berada di halaman Buat Laporan
    When Pelapor mengisi laporan tanpa kondisi kejadian
    And Pelapor mencoba menekan tombol Kirim Laporan
    Then browser menolak laporan yang belum lengkap
    And Pelapor tetap berada di halaman Buat Laporan

  @EC-03 @alternative
  Scenario: Berhasil mengirim laporan setelah melengkapi data
    Given Pelapor telah login dan berada di halaman Buat Laporan
    When Pelapor mengisi laporan tanpa kondisi kejadian
    And Pelapor mencoba menekan tombol Kirim Laporan
    Then browser menolak laporan yang belum lengkap
    When Pelapor melengkapi kondisi kejadian
    And Pelapor menekan tombol Kirim Laporan
    Then laporan Emergency Case berhasil dibuat
