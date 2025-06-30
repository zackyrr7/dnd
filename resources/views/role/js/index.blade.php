<script>

const urlParams = new URLSearchParams(window.location.search);
const room = urlParams.get('room');




function listRole() {
    $.ajax({
        url: "{{ route('listRole') }}",
        type: "GET",
        data: {
            room: room // variabel room kamu
        },
        success: function(response) {
            let html = '';

            if (response.length === 0) {
                html = '<p>Tidak ada role ditemukan.</p>';
            } else {
                // Ambil JSON string dari generated_roles
                const roleJsonString = response[0].generated_roles;
                let roles = [];

                try {
                    roles = JSON.parse(roleJsonString);
                } catch (e) {
                    console.error("Gagal decode JSON roles:", e);
                }

                if (roles.length > 0) {
                    html += `<p>
                        Kami memiliki ${roles.length} role yang kami sarankan. Tetapi kamu dapat membuat 
        role sesuka mu. Jika kamu membuat role sendiri, mohon tulis dengan lengkap 
        deskripsi role</p>`; // Tampilkan jumlah role
                    html += '<ol>';
                    roles.forEach(function(role) {
                        html += `<li>${role}</li>`;
                    });
                    html += '</ol>';
                } else {
                    html = '<p>Data role tidak valid atau kosong.</p>';
                }
            }

            $('#daftar-role').html(html);
        },
        error: function(xhr) {
            console.error("Gagal memuat role", xhr.responseText);
        }
    });
}

function listRoleUser() {
    $.ajax({
        url: "{{ route('listRoleUser') }}", // pastikan route ini benar
        type: "GET",
        data: {
            room: room // pastikan variabel ini tersedia sebelumnya
        },
        success: function(response) {
            let html = '';

            if (!Array.isArray(response) || response.length === 0) {
                html = '<p>Tidak ada user ditemukan di room ini.</p>';
            } else {
                html += '<div class="user-role-container">';
                response.forEach(function(user, index) {
                    let role = user.role ? user.role : '<span class="user-role">Belum mendapat role</span>';
                    html += `
                        <div class="user-card">
                            <strong>${index + 1}. ${user.nickname}</strong><br>
                            <div>${role}</div>
                        </div>
                    `;
                });
                html += '</div>';
            }

            // Masukkan ke ID yang berbeda dari sebelumnya
            $('#user-role-list').html(html);
        },
        error: function(xhr) {
            console.error("Gagal memuat data role user:", xhr.responseText);
            $('#user-role-list').html('<p style="color:red;">Terjadi kesalahan saat mengambil data.</p>');
        }
    });
}


function cerita() {
    $.ajax({
        url: "{{ route('cerita') }}", // pastikan route ini benar
        type: "GET",
        data: {
            room: room // pastikan variabel ini tersedia sebelumnya
        },
        success: function(response) {
            $('#premis').text(response); 
        },
        error: function(xhr) {
            alert('Gagal Mengambil premis Cerita')
        }
    });
}

function status() {
    $.ajax({
        url: "{{ route('cekCerita') }}", // pastikan route ini benar
        type: "GET",
        data: {
            room: room // pastikan variabel ini tersedia sebelumnya
        },
        success: function(response) {
            if (response.status !== null && response.status !== 0) {
                
                window.location.href = "{{ route('halamanGame') }}" + '?room=' + encodeURIComponent(room);
            }

        },
        error: function(xhr) {
            alert('Gagal Mengambil premis Cerita')
        }
    });
}


$('#simpanRole').on('click', function (event) {
    let role = document.getElementById('role').value;
  
    

    $.ajax({
        url: "{{ route('pickRole') }}",
        type: "POST",
        dataType: 'json',
        data: {
            role: role,
            _token: "{{ csrf_token() }}"
        },
        success: function (response) {
            if (response.status === true) {
                alert("Berhasil menyimpan role")
                document.getElementById('role').value = '';
                listRoleUser();


            } else {
                alert(response.message || 'Gagal Menyimpan role');
            }
        },
        error: function (xhr) {
            alert('Terjadi kesalahan saat mengirim data.');
            console.error(xhr.responseText);
        }
    });
});

$('#mulai').on('click', function (event) {
    $.ajax({
        url: "{{ route('cekRoles') }}",
        type: "POST",
        dataType: 'json',
        data: {
            room:room,
          
            _token: "{{ csrf_token() }}"
        },
        success: function (response) {
            if (response.status === true) {
                
              
   
                $.ajax({
                    url: "{{ route('mulai') }}",
                    type: "POST",
                    dataType: 'json',
                    data: {
                        room:room,
                        
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (response) {
                        if (response.status === true) {
                           
                            window.location.href = "{{ route('halamanGame') }}" + '?room=' + encodeURIComponent(room);


                        } else {
                            alert(response.message);
                        }
                    },
                    error: function (xhr) {
                        alert('Terjadi kesalahan saat mengirim data.');
                        console.error(xhr.responseText);
                    }
                });
            


            } else {
                alert(response.message);
            }
        },
        error: function (xhr) {
            alert('Terjadi kesalahan saat mengirim data.');
            console.error(xhr.responseText);
        }
    });
});


setInterval(listRoleUser, 5000);
setInterval(status, 5000);

    $(document).ready(function() {
        listRole();
        listRoleUser();
        cerita();
    });
</script>