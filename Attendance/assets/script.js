document.addEventListener("contextmenu", (e) => e.preventDefault());
document.addEventListener("contextmenu", focusInput);

function focusInput() {
  document.getElementById("rfid").focus();
}

// Fungsi untuk memastikan input field tetap fokus
function keepFocus(event) {
  if (event.target.id !== "rfid") {
    focusInput();
  }
}

// Saat halaman dimuat
window.onload = function () {
  focusInput();
  document.addEventListener("click", keepFocus);
};

function validateRFID() {
  var rfid = document.forms["absenForm"]["rfid"].value;
  if (rfid === "") {
    alert("RFID harus diisi");
    return false;
  }
}
