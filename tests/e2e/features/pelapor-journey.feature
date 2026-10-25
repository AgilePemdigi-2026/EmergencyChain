
@journey @pelapor @sprint1
Feature: Pelapor Emergency Case Journey

  Scenario: Pelapor login, membuat laporan, dan logout
    Given Pelapor berada di halaman login
    When Pelapor login dengan akun testing yang valid
    Then Pelapor diarahkan ke halaman Buat Laporan Emergency

    When Pelapor mengisi informasi Emergency Case yang valid
    And Pelapor menentukan titik lokasi kejadian
    And Pelapor mengirimkan laporan
    Then Emergency Case berhasil dibuat

    When Pelapor melakukan logout
    Then Pelapor kembali ke halaman login
