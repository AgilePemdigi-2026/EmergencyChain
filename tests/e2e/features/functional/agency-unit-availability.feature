
@functional @agency-unit-availability @sprint3
Feature: Pengelolaan Ketersediaan Unit Instansi
  Sebagai Instansi, saya ingin melihat dan mengubah status
  ketersediaan Resource milik instansi saya agar Resource
  yang tersedia dapat digunakan untuk penanganan Emergency Case.

  Background:
    Given database pengujian telah direset dan akun demo tersedia

  @AUA-01 @positive
  Scenario: Saya melihat dan mengubah status ketersediaan Resource
    Given saya sebagai Instansi memiliki Resource yang tersedia dan tidak tersedia
    When saya membuka halaman Kelola Unit
    Then saya melihat daftar Resource beserta status ketersediaannya
    When saya mengubah status Resource dari tersedia menjadi tidak tersedia
    Then saya melihat status Resource berubah menjadi tidak tersedia
    When saya mengubah kembali status Resource menjadi tersedia
    Then saya melihat status Resource berubah menjadi tersedia

  @AUA-02 @negative
  Scenario: Saya tidak dapat mengubah Resource yang sedang ditugaskan atau mengelola data dasar Resource
    Given saya sebagai Instansi memiliki Resource yang sedang ditugaskan
    When saya membuka halaman Kelola Unit
    Then saya tidak dapat mengubah status ketersediaan Resource yang sedang ditugaskan
    And saya tidak melihat fitur untuk menambah Resource
    And saya tidak melihat fitur untuk mengedit data dasar Resource
    And saya tidak melihat fitur untuk menghapus Resource
