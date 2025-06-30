<link href="https://fonts.googleapis.com/css2?family=MedievalSharp&display=swap" rel="stylesheet">

<style>
    body {
        margin: 0;
        padding: 0;
        font-family: 'MedievalSharp', cursive;
        background: linear-gradient(rgba(0,0,0,0.85), rgba(0,0,0,0.85)), 
            url('https://images.unsplash.com/photo-1526336024174-e58f5cdd8e13?auto=format&fit=crop&w=1950&q=80') no-repeat center center fixed;
        background-size: cover;
        color: #f4e4c1;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
    }

    .nickname-form-container {
        background: rgba(35, 24, 10, 0.95);
        padding: 40px 30px;
        border-radius: 15px;
        border: 2px solid #d4af37;
        box-shadow: 0 0 20px #d4af37aa;
        max-width: 480px;
        width: 90%;
        text-align: center;
        animation: fadeIn 1s ease;
    }

    .nickname-form-container h2 {
        font-size: 30px;
        margin-bottom: 25px;
        color: #ffd700;
        text-shadow: 2px 2px 4px #000;
    }

    label {
        font-size: 18px;
        display: block;
        margin-bottom: 10px;
        text-align: left;
        color: #f4e4c1;
    }

    input[type="text"] {
        width: 100%;
        padding: 12px;
        font-size: 16px;
        border-radius: 8px;
        border: 2px solid #d4af37;
        background: #fefae0;
        color: #000;
        margin-bottom: 25px;
        transition: 0.3s ease;
    }

    input[type="text"]:focus {
        outline: none;
        border-color: #ffd700;
        box-shadow: 0 0 8px #ffd700aa;
    }

    .btn-dnd {
        background: #6b4c3b;
        border: 2px solid #d4af37;
        color: #f4e4c1;
        padding: 12px 25px;
        font-size: 18px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        width: 100%;
        margin-bottom: 15px;
        text-shadow: 1px 1px 2px #000;
    }

    .btn-dnd:hover {
        background: #d4af37;
        color: #000;
        transform: scale(1.05);
        box-shadow: 0 0 15px #d4af37cc;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(30px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 500px) {
        .nickname-form-container {
            padding: 30px 20px;
        }

        .nickname-form-container h2 {
            font-size: 24px;
        }

        .btn-dnd {
            font-size: 16px;
        }
    }
</style>

<div class="nickname-form-container">
    <button onclick="window.history.back()" class="btn-dnd" style="background:#444; border-color:#999; margin-bottom: 25px;">🔙 Kembali</button>

    <h2>🔰 Pilih Nama Petualangmu</h2>
    <label for="nickName">Nickname</label>
    <input type="text" id="nickName" placeholder="cth: Eldrin, Nyx, Kaelar...">
    <button id="simpan" class="btn-dnd">⚔️ Simpan Nickname</button>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

@include('create_nickname.js.index')
