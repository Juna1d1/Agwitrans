//FUNGSI REGIS
function register(){
    const username = document.getElementById("regUser").value.trim();
    const noHp     = document.getElementById("regNoHp").value.trim();
    const password = document.getElementById("regPass").value.trim();

    // VALIDASI
    if(!username || !noHp || !password){
        document.getElementById("regMsg").innerText = "Semua field wajib diisi!";
        return;
    }
    if(!/^(08|\+628)[0-9]{8,12}$/.test(noHp)){
        document.getElementById("regMsg").innerText = "Nomor HP tidak valid!";
        return;
    }
    if(password.length < 5){
        document.getElementById("regMsg").innerText = "Password minimal 5 karakter!";
        return;
    }

    // kirim data
    let data = new FormData();
    data.append("username", username);
    data.append("no_hp", noHp);
    data.append("password", password);

    fetch("auth/Register.php",{
        method:"POST",
        body:data
    })
    .then(res => res.text())
    .then(res => {
        document.getElementById("regMsg").innerText = res;
        if(res.toLowerCase().includes("berhasil")){
            document.getElementById("regUser").value = "";
            document.getElementById("regNoHp").value = "";
            document.getElementById("regPass").value = "";
        }
    })
    .catch(err => {
        document.getElementById("regMsg").innerText = "Terjadi error!";
        console.error(err);
    });
}

function showRegister(){
    document.getElementById("loginBox").classList.add("hidden");
    document.getElementById("registerBox").classList.remove("hidden");
}

//FUNGSI LOGIN
function login() {
    const username = document.querySelector('input[name="username"]').value;
    const password = document.querySelector('input[name="password"]').value;

    fetch("auth/login.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: `username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`
    })
    .then(res => res.text())
    .then(res => {
        if(res === "success"){
            window.location.href = "user/dashboard.php";
        } else {
            document.getElementById("loginMsg").innerText = res;
        }
    });
}

function showLogin(){
    document.getElementById("registerBox").classList.add("hidden");
    document.getElementById("loginBox").classList.remove("hidden");
}

