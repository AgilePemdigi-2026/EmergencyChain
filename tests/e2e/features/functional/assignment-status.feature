
@functional @assignment-status @sprint2
Feature: Assignment Status Lifecycle

  Sebagai Instansi, saya ingin memperbarui status penugasan
  dan memantau perkembangan penanganan Emergency Case
  agar proses penanganan berjalan dengan baik.

  Background:
    Given database pengujian telah direset dan akun demo tersedia

  @AS-01 @positive @basic-flow
  Scenario: Saya membuka dan menyelesaikan penugasan melalui tahapan status penanganan
    Given saya memiliki penugasan Polisi yang aktif untuk ditangani
    When saya membuka dashboard penugasan
    And saya membuka detail penugasan Polisi tersebut
    And saya memperbarui status penanganan secara berurutan hingga selesai
    Then saya melihat penugasan Polisi telah selesai

  @AS-02 @positive
  Scenario: Saya memastikan status Emergency Case selesai setelah seluruh penugasan diselesaikan
    Given saya memiliki Emergency Case dengan penugasan Ambulans dan Polisi
    When saya menyelesaikan penugasan Ambulans
    Then saya melihat Emergency Case masih berstatus Menunggu
    When saya menyelesaikan penugasan Polisi
    Then saya melihat Emergency Case berstatus Selesai

  @AS-03 @positive
  Scenario: Saya memastikan Resource tersedia kembali setelah penugasan selesai
    Given saya memiliki unit Polisi yang sedang ditugaskan
    When saya menyelesaikan penugasan Polisi tersebut
    Then saya melihat unit Polisi kembali tersedia
