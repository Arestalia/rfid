document.addEventListener("DOMContentLoaded", function () {
  var kelasSelect = document.getElementById("kelas");
  var jurusanSelect = document.getElementById("jurusan");
  var subKelasSelect = document.getElementById("sub_kelas");

  kelasSelect.addEventListener("change", function () {
    resetJurusanAndSubKelas();
    updateSubKelasOptions();
  });

  jurusanSelect.addEventListener("change", function () {
    var jurusan = this.value;
    updateSubKelasOptions(jurusan);
  });

  function resetJurusanAndSubKelas() {
    jurusanSelect.selectedIndex = 0; // Reset jurusan
    subKelasSelect.innerHTML = ""; // Clear sub_kelas options
    var defaultOption = document.createElement("option");
    defaultOption.value = "";
    defaultOption.text = "Semua";
    subKelasSelect.appendChild(defaultOption);
  }

  function updateSubKelasOptions(jurusan) {
    // Clear existing options
    subKelasSelect.innerHTML = "";

    // Default option
    var defaultOption = document.createElement("option");
    defaultOption.value = "";
    defaultOption.text = "Semua";
    subKelasSelect.appendChild(defaultOption);
    var kelas = document.getElementById("kelas").value;

    if (jurusan === "AKL") {
      addSubKelasOptions("AKL", 5);
    } else if (jurusan === "PPLG") {
      addSubKelasOptions("PPLG", 2);
    } else if (jurusan == "TKJ") {
      addSubKelasOptions("TKJ", 3);
    } else if (jurusan == "DKV") {
      addSubKelasOptions("DKV", 2);
    } else if (jurusan == "MPLB") {
      if (kelas.length > 0 && kelas == "XI") {
        return addSubKelasOptions("MPLB", 1);
      }

      addSubKelasOptions("MPLB", 2);
    } else if (jurusan == "ULW") {
      addSubKelasOptions("ULW", 2);
    } else if (jurusan == "BDP") {
      addSubKelasOptions("BDP", 3);
    } else if (jurusan == "TABUS") {
      addSubKelasOptions("TABUS", 2);
    } else if (jurusan == "KULINER") {
      addSubKelasOptions("KULINER", 2);
    } else if (jurusan == "PHT") {
      addSubKelasOptions("PHT", 3);
    }
  }

  function addSubKelasOptions(jurusan, number) {
    for (var i = 1; i <= number; i++) {
      var option = document.createElement("option");
      option.value = jurusan + " " + i;
      option.text = i;
      subKelasSelect.appendChild(option);
    }
  }
});

function toggleModal() {
  let modal = document.getElementById("filterModal");
  modal.style.display = modal.style.display === "flex" ? "none" : "flex";
}

function toggleModalAdd() {
  let modalAdd = document.getElementById("addModal");
  modalAdd.style.display = modalAdd.style.display === "flex" ? "none" : "flex";
}

document.addEventListener("DOMContentLoaded", (event) => {
  var messageDiv = document.getElementById("message");
  if (messageDiv) {
    messageDiv.style.display = "block";
    setTimeout(() => {
      messageDiv.style.display = "none";
    }, 1000);
  }
});
