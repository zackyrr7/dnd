<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Game Room</title>
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <!-- Medieval Font -->
    <link href="https://fonts.googleapis.com/css2?family=MedievalSharp&display=swap" rel="stylesheet">

    <style>
        body {
            background-image: url('https://images.unsplash.com/photo-1607082349560-9cfdb05b17e5?ixlib=rb-4.0.3&auto=format&fit=crop&w=1950&q=80');
            background-size: cover;
            background-position: center;
            font-family: 'MedievalSharp', cursive;
            color: #f4e4c1;
            padding: 30px;
            margin: 0;
        }

        h2, h4 {
            text-align: center;
            text-shadow: 2px 2px 4px #000;
        }

        #room-name {
            background: rgba(0, 0, 0, 0.6);
            padding: 5px 10px;
            border-radius: 8px;
            color: #ffd700;
        }

        #current-turn {
            color: #d4af37;
        }

        #chat-container {
            overflow-y: auto;
            padding: 15px;
            height: 400px;
            background: rgba(0,0,0,0.6);
            border: 2px solid #d4af37;
            border-radius: 10px;
            color: #f4e4c1;
        }

        .bubble {
            max-width: 80%;
            padding: 12px 18px;
            border-radius: 15px;
            margin: 10px 0;
            clear: both;
            font-size: 16px;
            line-height: 1.5em;
            word-wrap: break-word;
            box-shadow: 0 0 5px rgba(0,0,0,0.3);
        }

        .left {
            background: rgba(255, 255, 255, 0.8);
            float: left;
            text-align: left;
            color: #000;
        }

        .right {
            background: rgba(212, 175, 55, 0.9);
            float: right;
            text-align: right;
            color: #000;
        }

        .bubble::after {
            content: "";
            display: table;
            clear: both;
        }

        #action-input {
            margin-top: 20px;
            background: rgba(0,0,0,0.6);
            padding: 15px;
            border-radius: 10px;
            border: 2px solid #d4af37;
        }

        #user-action {
            width: 100%;
            max-width: 100%;
            padding: 10px;
            border-radius: 8px;
            background: #f4e4c1;
            color: #000;
            border: 2px solid #d4af37;
            font-size: 15px;
            resize: vertical;
            box-sizing: border-box; /* Ini penting agar padding tidak menambah lebar */
        }

        button {
            background: #6b4c3b;
            border: 2px solid #d4af37;
            color: #f4e4c1;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 10px;
            transition: 0.3s ease;
        }

        button:hover {
            background: #d4af37;
            color: #000;
            transform: scale(1.05);
        }

        .container {
            max-width: 700px;
            margin: 0 auto;
        }

        .finish {
            display: flex;
           justify-content: center;
           margin-top: 20px;
        }

        .finish-container {
    display: flex;
    justify-content: center;
    margin-top: 20px;
}

        @keyframes shakeDice {
            0% { transform: rotate(0deg) scale(1); }
            25% { transform: rotate(10deg) scale(1.1); }
            50% { transform: rotate(-10deg) scale(1.1); }
            75% { transform: rotate(5deg) scale(1.05); }
            100% { transform: rotate(0deg) scale(1); }
        }

        .dice-rolling {
            animation: shakeDice 0.8s ease-in-out;
            display: inline-block;
            color: #ffd700;
            text-shadow: 2px 2px 5px #000;
        }

    </style>
</head>
<body>
    <div class="container">
        <h2>🛡️ Room: <span id="room-name">{{ request('room') }}</span></h2>
        <h4>🎲 Giliran Sekarang: <strong id="current-turn">...</strong></h4>

        <div id="chat-container">
            <!-- Chat akan dimuat di sini -->
        </div>

        <button id="roll-dice-btn" style="display: none;">🎲 Kocok Dadu</button>

        <!-- Form input cerita -->
        <div id="action-input" style="display: none;">
            <textarea id="user-action" rows="3" placeholder="Ketik aksi / cerita kamu di sini..."></textarea>
            <button id="send-action-btn">📜 Kirim Aksi</button>
        </div>
    </div>

    @php
        $rm = cekRM(request('room'));   
    @endphp

   

    @if ($rm === true)
    <div class="finish-container">
        <button class="finish">Selesaikan Cerita</button>
    </div>
@endif

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    @include('game.js.index')
</body>
</html>
