<template>
  <div class="flex flex-col md:flex-row min-h-screen bg-gray-100">
    <!-- Sidebar -->
    <Sidebar :activeMenu="activeMenu" @update:activeMenu="activeMenu = $event" />

    <!-- Main Content -->
    <div class="flex-1 p-4 md:p-8 pt-6 flex flex-col bg-white">
      <HeaderBar title="Form Pengajuan Baru" />
      <div class="border-b border-gray-300 mb-4"></div>

      <div class="bg-white p-4 md:p-6 rounded-2xl shadow w-full max-w-5xl mx-auto">
        <div class="flex flex-col gap-6">

          <!-- Alert Pengajuan Ditutup -->
          <div v-if="pengajuanDitutup"
            class="bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded text-sm">
            {{ pengajuanMessage }}
          </div>

          <!-- Form Items -->
          <div v-for="(item, index) in formData.items" :key="index"
            class="border border-gray-200 p-4 rounded-lg shadow-sm space-y-4">
            <!-- Header & Remove Button -->
            <div class="flex justify-between items-center">
              <h4 class="font-semibold text-sm text-[#333]">Pengajuan Barang {{ index + 1 }}</h4>
              <button v-if="formData.items.length > 1 && !pengajuanDitutup" @click="removeItem(index)"
                class="text-red-500 text-xs hover:underline cursor-pointer">
                Hapus
              </button>
            </div>

            <!-- NID -->
            <div class="flex flex-col md:flex-row items-start md:items-center gap-2 md:gap-5">
              <label class="w-full md:w-[150px] font-semibold text-sm text-black">NID</label>
              <input type="text" v-model="item.NID" placeholder="Masukkan NID"
                class="w-full p-2 border border-gray-300 rounded-lg text-sm" :disabled="pengajuanDitutup" />
            </div>

            <!-- Nama Barang -->
            <div class="flex flex-col md:flex-row items-start md:items-center gap-2 md:gap-5">
              <label class="w-full md:w-[150px] font-semibold text-sm text-black">Nama Barang</label>
              <select v-model="item.id_inventoris_fk" @change="updateHargaSatuan(index)"
                class="w-full p-2 border border-gray-300 rounded-lg text-sm" :disabled="pengajuanDitutup">
                <option disabled value="">Pilih Barang</option>
                <option v-for="alat in alatList" :key="alat.id_alat" :value="alat.id_alat">
                  {{ alat.nama_barang }}
                </option>
              </select>
            </div>

            <!-- Jumlah -->
            <div class="flex flex-col md:flex-row items-start md:items-center gap-2 md:gap-5">
              <label class="w-full md:w-[150px] font-semibold text-sm text-black">Jumlah</label>
              <input type="number" v-model.number="item.jumlah" min="1" @input="hitungTotal(index)"
                class="w-full p-2 border border-gray-300 rounded-lg text-sm" :disabled="pengajuanDitutup" />
            </div>

            <!-- Total Harga -->
            <div class="flex flex-col md:flex-row items-start md:items-center gap-2 md:gap-5">
              <label class="w-full md:w-[150px] font-semibold text-sm text-black">Total Harga</label>
              <input type="text" :value="formatRupiah(item.total)" disabled
                class="w-full p-2 border border-gray-300 rounded-lg text-sm text-gray-500 bg-gray-100" />
            </div>

            <!-- Keterangan -->
            <div class="flex flex-col md:flex-row items-start md:items-start gap-2 md:gap-5">
              <label class="w-full md:w-[150px] font-semibold text-sm text-black">Keterangan</label>
              <textarea v-model="item.keterangan" placeholder="Contoh: Kebutuhan operasional"
                class="w-full p-2 border border-gray-300 rounded-lg text-sm resize-y"
                :disabled="pengajuanDitutup"></textarea>
            </div>
          </div>

          <!-- Button Tambah -->
          <button @click="addItem" :disabled="pengajuanDitutup"
            class="mt-2 w-fit bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg cursor-pointer disabled:opacity-50">
            + Tambah Pengajuan Barang
          </button>

          <!-- Alert Sukses -->
          <SuccessAlert :visible="showSuccessAlert" :message="successMessage" />

          <!-- Navigasi -->
          <div class="flex flex-col md:flex-row justify-between items-center gap-4 mt-6">
            <router-link to="/pengajuan" class="w-full md:w-auto">
              <button
                class="w-full md:w-auto bg-[#074a5d] text-white px-4 py-2 rounded-lg hover:bg-[#063843] transition">
                Kembali
              </button>
            </router-link>

            <button @click="submitForm" :disabled="pengajuanDitutup"
              class="w-full md:w-auto bg-[#074a5d] text-white px-4 py-2 rounded-lg hover:bg-[#063843] transition disabled:opacity-50">
              Simpan Semua Pengajuan
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>


<script>
import Sidebar from "@/components/Sidebar.vue";
import HeaderBar from "@/components/HeaderBar.vue";
import SuccessAlert from "@/components/SuccessAlert.vue";
import axios from "axios";

export default {
  name: "FormPengajuanBaru",
  components: { Sidebar, HeaderBar, SuccessAlert },
  data() {
    return {
      activeMenu: "pengajuan",
      alatList: [],
      pengajuanDitutup: false,
      pengajuanMessage: "",
      formData: {
        tanggal_permintaan: new Date().toISOString().split("T")[0],
        status: "waiting_approval_1",
        items: [
          { NID: "", id_inventoris_fk: "", jumlah: "", total: 0, keterangan: "" }
        ]
      },
      showSuccessAlert: false,
      successMessage: "",
    };
  },
  async mounted() {
    await this.cekStatusPengajuan();
    if (!this.pengajuanDitutup) {
      this.fetchAlat();
    }
  },
  methods: {
    async cekStatusPengajuan() {
      try {
        const res = await axios.get("http://localhost:8000/api/pengaturan-pengajuan");
        const { is_open, tanggal_mulai, tanggal_selesai } = res.data;

        const today = new Date();
        const mulai = tanggal_mulai ? new Date(tanggal_mulai) : null;
        const selesai = tanggal_selesai ? new Date(tanggal_selesai) : null;

        if (!is_open) {
          this.pengajuanDitutup = true;
          this.pengajuanMessage = "Pengajuan saat ini ditutup oleh admin.";
          return;
        }

        if (mulai && today < mulai) {
          this.pengajuanDitutup = true;
          this.pengajuanMessage = `Pengajuan belum dimulai. Buka mulai ${mulai.toLocaleDateString("id-ID")}.`;
          return;
        }

        if (selesai && today > selesai) {
          this.pengajuanDitutup = true;
          this.pengajuanMessage = `Pengajuan telah ditutup sejak ${selesai.toLocaleDateString("id-ID")}.`;
          return;
        }

        this.pengajuanDitutup = false;
      } catch (err) {
        console.error("Gagal memuat status pengajuan:", err);
        this.pengajuanDitutup = false;
      }
    },

    fetchAlat() {
      const token = localStorage.getItem("token");
      axios.get("http://localhost:8000/api/alat", {
        headers: { Authorization: `Bearer ${token}` },
      }).then(res => {
        this.alatList = res.data.data;
      });
    },

    addItem() {
      this.formData.items.push({ NID: "", id_inventoris_fk: "", jumlah: "", total: 0, keterangan: "" });
    },

    removeItem(index) {
      this.formData.items.splice(index, 1);
    },

    updateHargaSatuan(index) {
      const selectedItem = this.formData.items[index];
      const selectedAlat = this.alatList.find(alat => alat.id_alat === selectedItem.id_inventoris_fk);
      const harga = selectedAlat ? selectedAlat.harga_satuan : 0;
      selectedItem.total = selectedItem.jumlah * harga;
    },

    hitungTotal(index) {
      const selectedItem = this.formData.items[index];
      const selectedAlat = this.alatList.find(alat => alat.id_alat === selectedItem.id_inventoris_fk);
      const harga = selectedAlat ? selectedAlat.harga_satuan : 0;
      selectedItem.total = selectedItem.jumlah * harga;
    },

    formatRupiah(angka) {
      if (!angka) return "-";
      return "Rp. " + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    },

    async submitForm() {
      const token = localStorage.getItem("token");
      try {
        await axios.post("http://localhost:8000/api/request-multiple", this.formData, {
          headers: { Authorization: `Bearer ${token}` },
        });
        this.successMessage = "Semua pengajuan berhasil disimpan.";
        this.showSuccessAlert = true;
        setTimeout(() => {
          this.showSuccessAlert = false;
          this.$router.push("/pengajuan");
        }, 2000);
      } catch (error) {
        console.error(error);
        alert("Gagal menyimpan pengajuan.");
      }
    },
  },
};
</script>

<style scoped>
th,
td {
  padding: 8px 10px;
  text-align: center;
  font-size: 12px;
  border: 1px solid #ccc;
  word-wrap: break-word;
}
</style>
