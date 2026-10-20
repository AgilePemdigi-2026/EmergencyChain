<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - EmergencyChain</title>
</head>
<body>
    <h1>Daftar EmergencyChain</h1>

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ route('register.store') }}" method="POST">
        @csrf

        <label>Nama lengkap</label>
        <input type="text" name="nama" value="{{ old('nama') }}" required>

        <label>Nomor telepon</label>
        <input type="text" name="no_telp" value="{{ old('no_telp') }}" required>

        <label>Email</label>
        <input type="email" name="email" value="{{ old('email') }}" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>Konfirmasi password</label>
        <input type="password" name="password_confirmation" required>

        <button type="submit">Daftar</button>
    </form>

    <p>Sudah punya akun? <a href="{{ route('login') }}">Login</a></p>
</body>
</html>
