
@functional @pelapor-report-status @sprint2
Feature: Status Laporan Emergency Case Pelapor
  Sebagai Pelapor, saya ingin melihat daftar, mencari,
  memfilter, dan membuka detail laporan Emergency Case
  milik saya sendiri.

  Background:
    Given database pengujian telah direset dan akun demo tersedia

  @PR-01 @positive
  Scenario: Pelapor membuka daftar laporan miliknya
    Given Pelapor telah memiliki laporan Emergency Case
    When Pelapor membuka halaman Status Laporan
    Then daftar laporan milik Pelapor ditampilkan
    And informasi laporan ditampilkan dengan lengkap

  @PR-02 @positive
  Scenario: Pelapor mencari laporan berdasarkan Case ID
    Given Pelapor telah memiliki laporan Emergency Case
    When Pelapor mencari laporan berdasarkan Case ID
    Then laporan dengan Case ID yang dicari ditampilkan

  @PR-03 @positive
  Scenario: Pelapor mencari laporan berdasarkan jenis kejadian
    Given Pelapor telah memiliki laporan Emergency Case
    When Pelapor mencari laporan berdasarkan jenis kejadian
    Then laporan dengan jenis kejadian yang sesuai ditampilkan

  @PR-04 @positive
  Scenario: Pelapor memfilter laporan berdasarkan status
    Given Pelapor telah memiliki laporan Emergency Case
    When Pelapor memfilter laporan dengan status Menunggu
    Then hanya laporan dengan status Menunggu yang ditampilkan

@PR-05 @positive
Scenario: Pelapor membuka detail laporan miliknya
  Given Pelapor telah memiliki laporan Emergency Case
  When Pelapor membuka halaman Status Laporan
  And Pelapor memilih laporan miliknya
  Then halaman detail laporan berhasil ditampilkan

  @PR-06 @negative
  Scenario: Pelapor mencoba membuka laporan milik Pelapor lain
    Given Pelapor lain telah memiliki laporan Emergency Case
    When Pelapor membuka detail laporan milik Pelapor lain
    Then sistem menolak akses ke laporan tersebut

  @PR-07 @alternative
  Scenario: Pelapor mencari laporan yang tidak ditemukan
    Given Pelapor telah memiliki laporan Emergency Case
    When Pelapor mencari laporan yang tidak tersedia
    Then sistem menampilkan pesan Belum ada laporan ditemukan

