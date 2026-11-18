
Feature: Assignment Management Petugas EC
  Sebagai Petugas Emergency Case
  Saya ingin melihat daftar dan detail Assignment
  Agar dapat memantau penugasan Resource pada Emergency Case

  Background:
    Given Petugas EC telah login

  @assignment @ui
  Scenario: ASG-01 Menampilkan daftar Assignment
    Given terdapat beberapa data Assignment
    When Petugas EC membuka halaman Assignment
    Then sistem menampilkan daftar Assignment
    And sistem menampilkan informasi Emergency Case
    And sistem menampilkan informasi Resource
    And sistem menampilkan status masing-masing Assignment

  @assignment @ui
  Scenario: ASG-02 Melihat detail Assignment
    Given terdapat Assignment yang sudah terbentuk
    When Petugas EC membuka detail Assignment
    Then sistem menampilkan informasi Emergency Case terkait
    And sistem menampilkan informasi Resource yang ditugaskan
    And sistem menampilkan informasi status Assignment

  @assignment @ui
  Scenario: ASG-03 Menampilkan status Assignment
    Given terdapat Assignment dengan status yang berbeda
    When Petugas EC membuka halaman Assignment
    Then sistem menampilkan status setiap Assignment
    And status yang ditampilkan sesuai dengan data tersimpan

  @assignment @ui
  Scenario: ASG-04 Menampilkan kondisi tanpa Assignment
    Given belum terdapat data Assignment
    When Petugas EC membuka halaman Assignment
    Then sistem menampilkan informasi bahwa belum ada Assignment
    And sistem tidak menampilkan data Assignment
    And halaman tetap dapat diakses tanpa error
