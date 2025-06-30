<script>

const joinBtn = document.getElementById('joinBtn');
  const popup = document.getElementById('popup');
  const closePopup = document.getElementById('closePopup');

  // Show popup on button click
  joinBtn.addEventListener('click', () => {
    popup.style.display = 'flex'; // flex to center content
  });

  // Close popup on close button click
  closePopup.addEventListener('click', () => {
    popup.style.display = 'none';
  });

  // Optional: close popup if clicking outside the popup content
  popup.addEventListener('click', (e) => {
    if (e.target === popup) {
      popup.style.display = 'none';
    }
  });



  $('#joinRoomBtn').on('click', function() {
    let kode = document.getElementById('roomCodeInput').value;
    let room = kode;
    let nickname = document.getElementById('nickname').innerText;
    if (!nickname) {
        return alert("Nickname belum dimasukkan");
    }

    if (!kode) {
        return alert("Kode belum dimasukkan");
    }

    $.ajax({
        url: "{{route('joinRoom')}}",
        type: "POST",
        dataType: 'json',
        data: {
            kode: kode,
            _token: "{{csrf_token()}}"
        },
          success: function (response) {
        
                    if (response.status === true) {
                        window.location.href = '/room?kode=' + encodeURIComponent(room);
                    } else {
                        alert(response.message || 'Gagal bergabung ke dalam room');
                    }
                },
    })
  })
</script>