document.addEventListener("contextmenu", (e) => e.preventDefault());
document.addEventListener("contextmenu", focusInput);
var input = document.getElementById("rfid");

function focusInput() {
  input.focus();
}

// Fungsi untuk memastikan input field tetap fokus
function keepFocus(event) {
  if (event.target.id !== "rfid") {
    focusInput();
  }
}

input.addEventListener("blur", function () {
  setTimeout(function () {
    input.focus();
  }, 0);
});
// Saat halaman dimuat
window.onload = function () {
  focusInput();
  document.addEventListener("click", keepFocus);
};

document.addEventListener("keydown", function (event) {
  if (event.key === "Tab") {
    event.preventDefault();
    input.focus();
  }
});

function validateRFID() {
  var rfid = document.forms["absenForm"]["rfid"].value;

  if (!rfid) {
    showAlert("RFID harus diisi");
    clearRFIDField();
    return false;
  } else if (rfid.length !== 10) {
    showAlert("RFID harus berisi 10 karakter");
    clearRFIDField();
    return false;
  }

  return true; // Jika semua validasi lolos, form akan disubmit
}

function showAlert(message) {
  alert(message);
  setTimeout(function () {
    closeAlert(); // Menutup alert secara otomatis
  }, 1000); // Waktu dalam milidetik (1000 ms = 1 detik)
}

function closeAlert() {
  window.alert = function () {}; // Override alert agar bisa tertutup otomatis
}

function clearRFIDField() {
  setTimeout(function () {
    document.forms["absenForm"]["rfid"].value = "";
    document.getElementById("rfid").focus(); // Mengembalikan fokus ke input RFID
  }, 1000); // Waktu dalam milidetik (1000 ms = 1 detik)
}
