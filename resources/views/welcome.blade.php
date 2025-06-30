<link href="https://fonts.googleapis.com/css2?family=MedievalSharp&display=swap" rel="stylesheet">

<style>
    * {
        box-sizing: border-box;
    }

    body {
        background-image: url('https://images.unsplash.com/photo-1607082349560-9cfdb05b17e5?ixlib=rb-4.0.3&auto=format&fit=crop&w=1950&q=80');
        background-size: cover;
        background-position: center;
        font-family: 'MedievalSharp', cursive;
        color: #f4e4c1;
        margin: 0;
        padding: 0;
        overflow-x: hidden;
    }

    .game-welcome-container {
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 50px 20px;
        min-height: 100vh;
        text-align: center;
        animation: fadeIn 1s ease;
    }

    h1 {
        font-size: 48px;
        margin-bottom: 10px;
        color: #ffd700;
        text-shadow: 2px 2px 6px #000;
        animation: fadeInUp 1s ease;
    }

    p {
        font-size: 22px;
        margin-bottom: 30px;
        animation: fadeInUp 1.2s ease;
    }

    .btn-dnd {
        background: #6b4c3b;
        border: 2px solid #d4af37;
        color: #f4e4c1;
        padding: 14px 28px;
        font-size: 18px;
        border-radius: 10px;
        cursor: pointer;
        margin: 10px;
        transition: all 0.3s ease;
        width: 240px;
        box-shadow: 0 0 10px rgba(0,0,0,0.4);
    }

    .btn-dnd:hover {
        background: #d4af37;
        color: #000;
        transform: scale(1.08);
    }

    .popup-overlay {
        display: none;
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: rgba(0, 0, 0, 0.85);
        z-index: 1000;
    }

    .popup-content {
        background: #2d1b0b;
        color: #f4e4c1;
        padding: 35px 25px;
        width: 90%;
        max-width: 360px;
        margin: 100px auto;
        border: 3px solid #d4af37;
        border-radius: 12px;
        text-align: center;
        animation: fadeInUp 0.5s ease;
    }

    input[type="text"] {
        width: 100%;
        padding: 12px;
        margin: 15px 0;
        border-radius: 6px;
        border: 1px solid #d4af37;
        background: #f4e4c1;
        color: #000;
        font-size: 16px;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to   { opacity: 1; }
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); }
        to   { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="game-welcome-container">
    <h1>Selamat Datang, <span style="color: #d4af37;">{{$nama}}</span>!</h1>
    <h2 id="nickname" hidden>{{$nama}}</h2>

    <p>Pilih jalanmu, petualang:</p>

    <div style="display: flex; flex-direction: column; align-items: center;">
        <a href="{{route('createNickName')}}">
            <button class="btn-dnd">🛡️ Buat Nickname</button>
        </a>
        <a href="{{route('createRoom')}}">
            <button class="btn-dnd">🏰 Buat Room</button>
        </a>
        <button id="joinBtn" class="btn-dnd">🔮 Join Lobby</button>
    </div>
</div>

<!-- Popup Join Lobby -->
<div class="popup-overlay" id="popup">
    <div class="popup-content">
        <h2>Masuk ke Guild</h2>
        <p>Masukkan Kode Room:</p>
        <input type="text" id="roomCodeInput" placeholder="Kode rahasia..." />
        <div style="display: flex; justify-content: space-between; gap: 10px; margin-top: 15px;">
            <button class="btn-dnd" id="closePopup" style="background: #555;">❌ Batal</button>
            <button class="btn-dnd" id="joinRoomBtn">✅ Oke</button>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('#joinBtn').click(function() {
            $('#popup').fadeIn();
        });

        $('#closePopup').click(function() {
            $('#popup').fadeOut();
        });

        $('#joinRoomBtn').click(function() {
            const code = $('#roomCodeInput').val().trim();
            if (code !== '') {
                window.location.href = `/join-room/${code}`;
            } else {
                alert('Masukkan kode guild!');
            }
        });
    });
</script>

@include('jswelcome.welcome')
