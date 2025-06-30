<script>
    $(document).ready(function () {
        $('#simpan').on('click', function () {
            let nickname = document.getElementById('nickName').value;

            if (!nickname) {
                alert('Nick Name belum terisi');
                return;
            }
        

            $.ajax({
                url: "{{ route('saveNickname') }}",
                type: "POST",
                dataType: 'json',
                data: {
                    nickname: nickname,
                    _token: "{{ csrf_token() }}"
                },
                success: function (response) {
                    if (response.status === true) {
                        alert('Nickname berhasil disimpan');
                        document.cookie = "user_token=" + response.token + "; path=/; max-age=" + 7*24*60*60 + "; SameSite=Lax";
                        window.location.href = "/set-token?token=" + response.token;
                    } else {
                        alert(response.message || 'Gagal menyimpan nickname');
                    }
                },
                error: function (xhr) {
                    alert('Terjadi kesalahan saat mengirim data.');
                    console.error(xhr.responseText);
                }
            });
        });
    });
</script>
