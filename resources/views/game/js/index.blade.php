<script>
    $(document).ready(function() {
        const urlParams = new URLSearchParams(window.location.search);
        const room = urlParams.get('room');
        let currentUserNickname = '';
    
        function getNickname() {
            $.ajax({
                url: "{{ route('getNickname') }}",
                type: 'GET',
                success: function(response) {
                    if (response && response.length > 0) {
                        currentUserNickname = response[0].nickname;
                    } else {
                        currentUserNickname = 'Guest';
                    }
                }
            });
        }
    
        if (!room) {
            alert('Parameter room tidak ditemukan di URL');
            return;
        }
    
        let aiTurnRunning = false;
    
        // Render chat bubble-style
        function renderChat(storyLog) {
    const container = $('#chat-container');

    const isAtBottom = container[0].scrollHeight - container.scrollTop() - container.outerHeight() < 50;

    container.html('');

    storyLog.forEach(function(entry) {
        let bubbleClass = '';
        let bubbleAlign = '';

        if (entry.startsWith('GM:')) {
            // Bubble untuk AI (narasi)
            bubbleClass = 'bubble left';
        } else {
            // Bubble aksi dari semua pemain
            bubbleClass = 'bubble right';
        }

        container.append(`<div class="${bubbleClass}">${entry}</div>`);
    });

    if (isAtBottom) {
        container.scrollTop(container[0].scrollHeight);
    }
}


        function finish() {
            $.ajax({
                url: "{{ route('finish') }}",
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    room: room,
                    _token: "{{ csrf_token() }}"
                }),
                success: function(response) {
                    if (response.status) {
                        $('#user-action').val('');
                        $('#roll-dice-btn').hide();
                        fetchGameData();
                    } else {
                        alert('Gagal menyelesaikan cerita: ' + response.message);
                    }
                },
                error: function() {
                    alert('Kesalahan jaringan saat menyelesaikan cerita.');
                }
            });
        }

    
        function fetchGameData() {
            $.ajax({
                url: "{{ route('showGameRoom') }}",
                data: { room: room },
                type: 'GET',
                success: function(response) {
                    renderChat(response.storyLog);
                    $('#current-turn').text(response.currentTurn || 'N/A');
    
                    if (response.pending_action != null && response.currentTurn === currentUserNickname) {
                        $('#roll-dice-btn').show();
                    } else {
                        $('#roll-dice-btn').hide();
                    }
    
                    if (response.currentTurn === currentUserNickname && response.pending_action == null) {
                        $('#action-input').show();
                    } else {
                        $('#action-input').hide();
                    }
    
                    if (response.currentTurn === 'rm' && !aiTurnRunning) {
                        runAITurn();
                    }
                },
                error: function() {
                    console.error('Gagal mengambil data cerita.');
                }
            });
        }
    
        function runAITurn() {
            aiTurnRunning = true;
            $.ajax({
                url: "{{ route('aiTurn') }}",
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ room: room }),
                success: function(response) {
                    if (response.status) {
                        fetchGameData();
                    } else {
                        console.error('Gagal menjalankan AI turn:', response.message);
                    }
                },
                error: function() {
                    console.error('Kesalahan jaringan saat AI turn.');
                },
                complete: function() {
                    aiTurnRunning = false;
                }
            });
        }
    
        $('#roll-dice-btn').click(function() {
            $(this).attr('disabled', true).text('Mengocok...');
            $.ajax({
                url: "{{ route('rollDice') }}",
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    room: room,
                    nickname: currentUserNickname,
                    _token: "{{ csrf_token() }}"
                }),
                success: function(response) {
                    if (response.status) {
                        $('#user-action').val('');
                        $('#roll-dice-btn').hide();
                        fetchGameData();
                    } else {
                        alert('Gagal mengirim aksi: ' + response.message);
                    }
                },
                error: function() {
                    alert('Kesalahan jaringan saat mengirim aksi.');
                },
                complete: function() {
                    $('#roll-dice-btn').attr('disabled', false).text('Kocok Dadu');
                }
            });
        });
    
        $('#send-action-btn').click(function () {
    const actionText = $('#user-action').val().trim();
    if (!actionText) {
        alert('Tindakan tidak boleh kosong.');
        return;
    }

    $(this).attr('disabled', true).text('Mengirim...');

    $.ajax({
        url: "{{ route('actionButton') }}",
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            room: room,
            nickname: currentUserNickname,
            action: actionText,
            _token: "{{ csrf_token() }}"
        }),
        success: function (response) {
            if (response.status) {
                // Kosongkan textarea
                $('#user-action').val('');

                // Tampilkan aksi pemain langsung ke chat sebagai bubble kanan
                const bubble = $('<div>')
                    .addClass('chat-bubble bubble-right')
                    .text(`${currentUserNickname}: ${actionText}`);
                $('#chat-container').append(bubble);
                $('#chat-container').scrollTop($('#chat-container')[0].scrollHeight);

                // Jika butuh roll dadu, tampilkan tombol
                if (response.roll_required === true) {
                    $('#roll-dice-btn').show();
                } else {
                    $('#roll-dice-btn').hide();
                }

                // Kalau ada kelanjutan cerita dari AI, tambahkan juga
                if (response.next_story) {
                    const aiBubble = $('<div>')
                        .addClass('chat-bubble bubble-left')
                        .text('GM: ' + response.next_story);
                    $('#chat-container').append(aiBubble);
                    $('#chat-container').scrollTop($('#chat-container')[0].scrollHeight);
                }
            } else {
                alert('Gagal mengirim aksi: ' + response.message);
            }
        },
        error: function () {
            alert('Kesalahan jaringan saat mengirim aksi.');
        },
        complete: function () {
            $('#send-action-btn').attr('disabled', false).text('Kirim Aksi');
        }
    });
});
$('.finish').on('click', function() {
        let confirmation = confirm("Apakah kamu mau menyelesaikan cerita ini?")
        if (confirmation == true) {
           finish();
        }
    })

$('#roll-dice-btn').on('click', function () {
        const $dice = $('#dice-result');
        $dice.removeClass('dice-rolling'); // Reset animasi

        // Tambahkan angka sementara (simulasi gulungan)
        $dice.text("🎲...");
        void $dice[0].offsetWidth; // Force reflow untuk animasi

        $dice.addClass('dice-rolling');

        // Hasil acak setelah animasi
        setTimeout(() => {
            const result = Math.floor(Math.random() * 20) + 1; // D20
            $dice.text(`🎲 ${result}`);
        }, 800);
    });
    
        $('#roll-dice-btn').hide();
        $('#action-input').hide();
    
        getNickname();
        fetchGameData();
        setInterval(fetchGameData, 5000);
    });


   
    </script>
    