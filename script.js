// =================== SETUP ===================
let cart = [];

const cartBox = document.getElementById("cart");
const totalText = document.getElementById("totalText");
const totalField = document.getElementById("totalField");
const itemsField = document.getElementById("itemsField");
const ongkirField = document.getElementById("ongkirField");
const tipeLayananSelect = document.getElementById("tipe_layanan");
const wilayahSelect = document.getElementById("wilayah");
const wilayahBox = document.getElementById("wilayahBox");

// Alamat & Telp Box
const alamatBox = document.getElementById("alamatBox");
const alamatField = document.getElementById("alamat");
const telpBox = document.getElementById("telpBox");
const telpField = document.getElementById("telp");

// Ongkir per wilayah
const ongkirWilayah = {
    "Tarakan Barat": 5000,
    "Tarakan Timur": 10000,
    "Tarakan Utara": 5000,
    "Tarakan Tengah": 8000
};


let ongkir = 0;

// =================== EVENT TIPE LAYANAN ===================
tipeLayananSelect.addEventListener("change", () => {

    if (tipeLayananSelect.value === "Di Antar") {
        wilayahBox.style.display = "block";
        alamatBox.style.display = "block";
        telpBox.style.display = "block";
        alamatField.required = true;
        telpField.required = true;

    } else if (tipeLayananSelect.value === "Take Away") {
        wilayahBox.style.display = "none";
        alamatBox.style.display = "none";
        telpBox.style.display = "block";

        alamatField.required = false;
        telpField.required = true;
        ongkir = 0;
        ongkirField.value = 0;
        updateTotal();

    } else { // Makan di Tempat
        wilayahBox.style.display = "none";
        alamatBox.style.display = "none";
        telpBox.style.display = "none";

        alamatField.required = false;
        telpField.required = false;

        ongkir = 0;
        ongkirField.value = 0;
        updateTotal();
    }
});

// =================== EVENT PILIH WILAYAH ===================
wilayahSelect.addEventListener("change", () => {
    ongkir = ongkirWilayah[wilayahSelect.value] || 0;
    ongkirField.value = ongkir;
    updateTotal();
});

// =================== TAMBAH KE KERANJANG ===================
document.querySelectorAll(".add").forEach(btn => {
    btn.addEventListener("click", () => {
        const id = btn.dataset.id;
        let name = btn.dataset.name;
        const price = parseInt(btn.dataset.price);

        // NORMALISASI NAMA SUPAYA SAMA DENGAN menu.json
       const nameNormalized = name.trim();


        let exist = cart.find(x => x.id == id);

        if (exist) {
            exist.qty++;
        } else {
            cart.push({
                id: id,
                name: nameNormalized,
                price: price,
                qty: 1
            });
        }

        renderCart();
        updateTotal();
    });
});



// =================== RENDER CART ===================
function renderCart() {
    cartBox.innerHTML = "";

    cart.forEach((item, i) => {
        let displayName = item.name.replace(/\b\w/g, c => c.toUpperCase()); // kapitalisasi

        let div = document.createElement("div");
        div.innerHTML = `
            <p><strong>${displayName}</strong><br>
            ${item.qty} × Rp ${item.price.toLocaleString('id-ID')}
            = Rp ${(item.qty * item.price).toLocaleString('id-ID')}
            </p>
            <button class="hapus" data-i="${i}" style="background:red;color:white;padding:5px;border:none;border-radius:6px;">
                Hapus
            </button>
            <hr>
        `;
        cartBox.appendChild(div);
    });

    document.querySelectorAll(".hapus").forEach(btn => {
        btn.addEventListener("click", () => {
            cart.splice(btn.dataset.i, 1);
            renderCart();
            updateTotal();
        });
    });

    // simpan item yang sudah NORMALIZED
    itemsField.value = JSON.stringify(cart);
}


// =================== HITUNG TOTAL ===================
function updateTotal() {
    let subtotal = 0;
    cart.forEach(i => subtotal += i.price * i.qty);

    const total = subtotal + ongkir;

    totalText.textContent = "Rp " + total.toLocaleString("id-ID");
    totalField.value = total;
}
