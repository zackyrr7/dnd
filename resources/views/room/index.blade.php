<!DOCTYPE html>
<html>
<head>
    <title>Guild Room</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Konstanta JS -->
    <script>
        const ROOM_CODE = "{{ $room }}";
        const LIST_ROOM_URL = "{{ route('list.room') }}";
    </script>

    <!-- Font dan Style Medieval -->
    <link href="https://fonts.googleapis.com/css2?family=MedievalSharp&display=swap" rel="stylesheet">
    <style>
        body {
            background-image: url('https://images.unsplash.com/photo-1607082349560-9cfdb05b17e5?ixlib=rb-4.0.3&auto=format&fit=crop&w=1950&q=80');
            background-size: cover;
            background-position: center;
            font-family: 'MedievalSharp', cursive;
            color: #f4e4c1;
            margin: 0;
            padding: 20px;
        }

        h2 {
            text-align: center;
            font-size: 36px;
            text-shadow: 2px 2px 5px #000;
        }

        #daftar-user {
            border: 3px solid #d4af37;
            background: rgba(0, 0, 0, 0.7);
            color: #f4e4c1;
            padding: 20px;
            width: 350px;
            margin: 30px auto;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(212, 175, 55, 0.6);
        }

        .user {
            padding: 10px 0;
            border-bottom: 1px solid #d4af37;
        }

        .btn-dnd {
            background: #6b4c3b;
            border: 2px solid #d4af37;
            color: #f4e4c1;
            padding: 10px 20px;
            font-size: 18px;
            border-radius: 8px;
            cursor: pointer;
            margin: 0 10px;
            transition: 0.3s ease;
        }

        .btn-dnd:hover {
            background: #d4af37;
            color: #000;
            transform: scale(1.05);
        }

        .modal, .loading {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .modal {
            background: rgba(0,0,0,0.6);
        }

        .modal-content {
            background: #2d1b0b;
            padding: 20px;
            border-radius: 10px;
            color: #f4e4c1;
            width: 300px;
            border: 2px solid #d4af37;
        }

        .loading {
            background: rgba(0,0,0,0.8);
            color: #fff;
            font-size: 24px;
        }
    </style>
</head>
<body>

    <h2>📜 Daftar Petualang di Guild: <span id="room-code">{{ $room }}</span></h2>

    <div id="daftar-user">
        <p>🔍 Memuat daftar petualang...</p>
    </div>

    @php
        $status = cekRM($room);
        $token = token();
        $cekReady = DB::select("SELECT ready from users where token = '$token'");
        $tombol = $cekReady[0]->ready == 1 ? 'Unready' : 'Ready';
    @endphp

    <input type="text" id="status" value="{{$status}}" hidden class="form-control">

    <div style="display: flex; justify-content: center; margin-top: 20px;">
        @if ($status == true)
            <a href="">
                <button class="btn-dnd" id="leftRoom">🏃 Tinggalkan Guild</button>
                <button class="btn-dnd" id="mulai">⚔️ Mulai Pertempuran</button>
            </a>
        @else
            <button class="btn-dnd" id="leftRoom">🏃 Tinggalkan Guild</button>
            <button class="btn-dnd" id="buttonReady">{{ $tombol }}</button>
        @endif
    </div>

</body>
</html>

@include('room.js.index')
