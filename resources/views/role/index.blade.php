<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $room = request()->query('room');
    @endphp

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roles {{ $room }}</title>

    <!-- Medieval Font -->
    <link href="https://fonts.googleapis.com/css2?family=MedievalSharp&display=swap" rel="stylesheet">

    <!-- DnD Style -->
    <style>
        body {
            background-image: url('https://images.unsplash.com/photo-1607082349560-9cfdb05b17e5?ixlib=rb-4.0.3&auto=format&fit=crop&w=1950&q=80');
            background-size: cover;
            background-position: center;
            font-family: 'MedievalSharp', cursive;
            color: #f4e4c1;
            margin: 0;
            padding: 30px;
        }

        h1, h2 {
            text-align: center;
            text-shadow: 2px 2px 5px #000;
        }

        #premis {
            max-width: 700px;
            margin: 0 auto 40px auto;
            background: rgba(0, 0, 0, 0.6);
            padding: 20px;
            border: 2px solid #d4af37;
            border-radius: 10px;
        }

        #daftar-role {
            max-width: 700px;
            margin: 0 auto;
            background: rgba(0, 0, 0, 0.6);
            padding: 20px;
            border: 2px solid #d4af37;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        #role {
            display: block;
            margin: 0 auto 20px auto;
            width: 80%;
            padding: 10px;
            font-size: 16px;
            border-radius: 10px;
            background: #f4e4c1;
            color: #000;
            border: 2px solid #d4af37;
        }

        .btn-dnd {
            background: #6b4c3b;
            border: 2px solid #d4af37;
            color: #f4e4c1;
            padding: 10px 25px;
            font-size: 18px;
            border-radius: 8px;
            cursor: pointer;
            display: block;
            margin: 0 auto 20px auto;
            transition: 0.3s ease;
        }

        .btn-dnd:hover {
            background: #d4af37;
            color: #000;
            transform: scale(1.05);
        }

        #user-role-list {
            max-width: 700px;
            margin: 0 auto;
            background: rgba(0, 0, 0, 0.6);
            padding: 20px;
            border: 2px solid #d4af37;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <h1>📜 Premis Cerita</h1>
    <p id="premis">Loading premis petualanganmu...</p>

    <h2>🎭 Daftar Role</h2>
    <div id="daftar-role">Loading roles yang tersedia...</div>

    <textarea id="role" cols="50" rows="10" placeholder="Tulis role di sini..."></textarea>
    <button class="btn-dnd" id="simpanRole">💾 Simpan Role</button>

    <div id="user-role-list">Memuat daftar pemain dan peran...</div>

    @php
        $rm = cekRM($room);
    @endphp

    @if ($rm == true)
        <button class="btn-dnd" id="mulai">⚔️ Mulai Permainan</button>
    @endif

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    @include('role.js.index')
</body>
</html>
