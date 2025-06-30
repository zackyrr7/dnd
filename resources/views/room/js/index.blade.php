<script>

$('#leftRoom').on('click', function () {
            $.ajax({
                url: "{{ route('leftRoom') }}",
                type: "POST",
                dataType: 'json',
                data: {
                   
                    _token: "{{ csrf_token() }}"
                },
                success: function (response) {
                    if (response.status === true) {
                        window.location.href = "/"
                    } else {
                        alert(response.message || 'Gagal Left room');
                    }
                },
                error: function (xhr) {
                    alert('Terjadi kesalahan saat mengirim data.');
                    console.error(xhr.responseText);
                }
            });
        });


$('#buttonReady').on('click', function () {
    
            $.ajax({
                url: "{{ route('ready') }}",
                type: "POST",
                dataType: 'json',
                data: {
                   
                    _token: "{{ csrf_token() }}"
                },
                success: function (response) {
                    if (response.status === true) {
                        fetchUserList();
                        if (response.ready == 0) {
                        buttonReady.innerText = 'Ready';
                    } else {
                        buttonReady.innerText = 'Not Ready';
                    }
                        
                    } else {
                        alert(response.message || 'Gagal Ready');
                    }
                },
                error: function (xhr) {
                    alert('Terjadi kesalahan saat mengirim data.');
                    console.error(xhr.responseText);
                }
            });
        });

    function fetchUserList() {
        let kode = document.getElementById('room-code').textContent;
    
        $.ajax({
            url: "{{ route('list.room') }}",
            type: "GET",
            data: {
                kode: kode
            },
            success: function(response) {
                let html = '';
    
                if (response.length === 0) {
                    html = '<p>Tidak ada pengguna di dalam room.</p>';
                } else {
                    response.forEach(function(user) {
                        // Tandai jika dia adalah RM
                        let labelRM = user.urutan === 0 ? ' <strong>(RM)</strong>' : '';
    
                        // Tampilkan status ready
                        let status = (user.ready && user.ready != 0) ? 'Ready' : 'Belum Ready';
    
                        html += `<div class="user"><p>${user.nickname}${labelRM} - <em>${status}</em></p></div>`;
                    });
                }
    
                $('#daftar-user').html(html);
            },
            error: function(xhr) {
                console.error("Gagal memuat daftar pengguna", xhr.responseText);
            }
        });
    }



    $('#mulai').on('click', function (event) {
    event.preventDefault(); // 🛑 Mencegah refresh jika ada dalam form
    let kode = document.getElementById('room-code').textContent;
    

    $.ajax({
        url: "{{ route('readyAll') }}",
        type: "POST",
        dataType: 'json',
        data: {
            kode: kode,
            _token: "{{ csrf_token() }}"
        },
        success: function (response) {
            if (response.status === true) {
                // Cek apakah user adalah room master
                if (response.is_master) {
                    $.ajax({
                        url: "{{ route('pilihCerita') }}",
                        type: "GET",
                        success: function (data) {
                            showCeritaPopup(data);
                        },
                        error: function () {
                            alert("Gagal mengambil data cerita.");
                        }
                    });
                } else {
                    showLoadingScreen("Room master sedang memilih cerita...");
                }

                if (response.ready == 0) {
                    buttonReady.innerText = 'Ready';
                } else {
                    buttonReady.innerText = 'Not Ready';
                }

            } else {
                alert(response.message || 'Gagal Ready');
            }
        },
        error: function (xhr) {
            alert('Terjadi kesalahan saat mengirim data.');
            console.error(xhr.responseText);
        }
    });
});



function showCeritaPopup(data) {
    let ceritaList = '<ul>';
    data.forEach(function (item) {
        ceritaList += `
            <li style="margin-bottom: 10px;">
                <strong>${item.judul}</strong><br>
                <br>
                ${item.premis}<br>
                <button onclick="pilihCerita(${item.id})">Pilih Cerita Ini</button>
            </li>
        `;
    });
    ceritaList += '</ul>';

    const popup = `
        <div id="ceritaModal" class="modal" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; justify-content: center; align-items: center; z-index: 9999;">
            <div class="modal-content" style="background: black; padding: 20px; border-radius: 5px; max-width: 500px;">
                <h3>Pilih Cerita</h3>
                ${ceritaList}
                <button onclick="closeCeritaModal()">Tutup</button>
            </div>
        </div>
    `;

    $('body').append(popup);
}


function closeCeritaModal() {
    let kode = document.getElementById('room-code').textContent.trim();
    $.ajax({
        url: "{{ route('batalPilihCerita') }}",
        type: "GET",
        dataType: "json",
        data: {
            room: kode
        },
        success: function(response) {
            hideLoadingScreen();
           
        },
        error: function() {
            console.error('Gagal cek status cerita');
        }
    });
    $('#ceritaModal').remove();
}

function showLoadingScreen(message) {
    const loader = `
        <div id="loadingScreen" class="loading">
            <p>${message}</p>
        </div>`;
    $('body').append(loader);

    // Optional: remove after timeout
    setTimeout(() => $('#loadingScreen').remove(), 5000);
}

function cekStatusCerita() {
    let kode = document.getElementById('room-code').textContent.trim();
    $.ajax({
        url: "{{ route('cekStatusCerita') }}",
        type: "GET",
        dataType: "json",
        data: {
            room: kode
        },
        success: function(response) {
          
            if (response.status === 0) {
                showLoadingScreen("Room master sedang memilih cerita...");
            } else if (response.status === null) {
                hideLoadingScreen();
                // mulai game atau redirect dll
            
            } else {
                window.location.href = "{{ route('halamanRole') }}" + '?room=' + encodeURIComponent(kode);
            }
        },
        error: function() {
            console.error('Gagal cek status cerita');
        }
    });
}


function pilihCerita(idCerita) {
    let kode = document.getElementById('room-code').textContent.trim();
    $.ajax({
        url: "{{ route('simpanPilihCerita') }}", // pastikan route ini sesuai
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            id_cerita: idCerita
        },
        success: function(response) {
            // window.location.href = "{{ route('halamanRole') }}" + '?room=' + encodeURIComponent(kode);
            $.ajax({
        url: "{{ route('getRole') }}", // pastikan route ini sesuai
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            id_cerita: idCerita
        },
        success: function(response) {
            window.location.href = "{{ route('halamanRole') }}" + '?room=' + encodeURIComponent(kode);
           
          
        },
        error: function(xhr) {
            alert("Gagal memilih cerita");
            console.error(xhr.responseText);
        }
    });
          
        },
        error: function(xhr) {
            alert("Gagal memilih cerita");
            console.error(xhr.responseText);
        }
    });
}


// Jalankan polling setiap 3 detik

let status = document.getElementById('status').value;
if (status == false) {
    setInterval(cekStatusCerita, 3000);
}



    // Jalankan setiap 5 detik
    setInterval(fetchUserList, 5000);
    
    // Jalankan saat pertama kali halaman dibuka
    $(document).ready(function() {
        fetchUserList();
    });
    </script>
    