@hospital @functional
Feature: Manajemen Pasien Rumah Sakit
  Sebagai petugas rumah sakit,
  saya ingin mengelola permintaan rujukan dan pasien yang dirujuk,
  agar proses penerimaan dan penanganan pasien dapat dipantau melalui EmergencyChain.

  Background:
    Given pengguna telah login sebagai petugas rumah sakit

  @RS-01
  Scenario: Rumah sakit melihat dan menerima permintaan rujukan
    Given terdapat permintaan rujukan yang ditujukan kepada rumah sakit pengguna
    When petugas membuka halaman permintaan rujukan
    Then sistem menampilkan permintaan rujukan yang ditujukan kepada rumah sakit tersebut
    When petugas menerima permintaan rujukan berstatus menunggu keputusan
    Then sistem menyimpan keputusan penerimaan rujukan
    And sistem mengaitkan rujukan dengan rumah sakit pengguna
    And sistem membuat data pasien dari rujukan yang diterima
    And sistem membuat riwayat awal pasien dengan status "Dalam Penanganan"

  @RS-02
  Scenario: Rumah sakit melihat dan menolak permintaan rujukan
    Given terdapat permintaan rujukan yang ditujukan kepada rumah sakit pengguna
    When petugas membuka halaman permintaan rujukan
    Then sistem menampilkan permintaan rujukan yang ditujukan kepada rumah sakit tersebut
    When petugas menolak permintaan rujukan berstatus menunggu keputusan
    Then sistem memproses penolakan rujukan
    And sistem meneruskan proses pencarian rumah sakit tujuan berikutnya

  @RS-03
  Scenario: Rumah sakit melihat daftar pasien dari rujukan yang diterima
    Given terdapat rujukan yang telah diterima oleh rumah sakit pengguna
    And data pasien dari rujukan tersebut telah dibuat
    When petugas membuka halaman daftar pasien
    Then sistem menampilkan pasien yang diterima oleh rumah sakit pengguna
    And sistem menampilkan pasien yang status penanganan terakhirnya "Dalam Penanganan"

  @RS-04
  Scenario: Rumah sakit melihat detail pasien
    Given terdapat pasien yang dirujuk dan diterima oleh rumah sakit pengguna
    When petugas membuka detail pasien tersebut
    Then sistem menampilkan informasi pasien
    And sistem menampilkan informasi emergency case terkait
    And sistem menampilkan kondisi korban dan informasi rujukan
    And sistem menampilkan riwayat penanganan pasien

  @RS-05
  Scenario: Rumah sakit memperbarui status dan melihat riwayat penanganan pasien
    Given terdapat pasien yang sedang ditangani oleh rumah sakit pengguna
    And status penanganan terakhir pasien adalah "Dalam Penanganan"
    When petugas mengubah status pasien menjadi "Pasien Sudah Ditangani"
    And petugas menyimpan perubahan status
    Then sistem menyimpan status penanganan terakhir sebagai "Pasien Sudah Ditangani"
    And sistem menyimpan catatan riwayat perubahan status pasien
    And sistem mencatat waktu perubahan status
    And sistem mencatat petugas yang melakukan perubahan
    And sistem mengarahkan petugas ke halaman Riwayat Penanganan
    And sistem menampilkan pasien tersebut di Riwayat Penanganan
    And sistem tidak menampilkan pasien tersebut di daftar pasien aktif