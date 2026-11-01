
@extends('layouts.petugas-ec')

@section('title', 'Hospital Matching - EmergencyChain')

@section('content')

<div class="mx-auto max-w-7xl">

    <div class="mb-6">
        <a
            href="{{ route('petugas.ec.cases.show', [
                'caseId' => $case->case_id
            ]) }}"
            class="text-sm font-medium text-blue-500 hover:underline"
        >
            ← Kembali ke Detail Emergency Case
        </a>
    </div>

    <section class="rounded-xl border border-slate-100
                    bg-white p-8 text-center shadow-sm">

        <div class="mx-auto flex h-16 w-16 items-center
                    justify-center rounded-full bg-rose-50">
            <span class="text-2xl text-red-500">+</span>
        </div>

        <h1 class="mt-5 text-xl font-semibold text-slate-700">
            Hospital Matching
        </h1>

        <p class="mt-2 text-sm text-slate-500">
            Emergency Case #{{ $case->case_id }}
        </p>

        <span class="mt-4 inline-flex rounded-full bg-amber-100
                     px-3 py-1 text-xs font-semibold text-amber-700">
            Belum Tersedia
        </span>

        <p class="mx-auto mt-5 max-w-lg text-sm leading-relaxed
                  text-slate-500">
            Fitur Hospital Matching belum diimplementasikan.
            Halaman ini nantinya digunakan untuk menampilkan
            proses pencocokan rumah sakit dan informasi rujukan
            berdasarkan kebutuhan Emergency Case.
        </p>

        <a
            href="{{ route('petugas.ec.cases.show', [
                'caseId' => $case->case_id
            ]) }}"
            class="mt-6 inline-flex rounded-lg bg-red-500
                   px-5 py-3 text-sm font-semibold text-white
                   hover:bg-red-600"
        >
            Kembali ke Detail Emergency Case
        </a>

    </section>

</div>

@endsection
