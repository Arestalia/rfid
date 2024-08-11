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
  if (rfid === "") {
    alert("RFID harus diisi");
    return false;
  }
}
