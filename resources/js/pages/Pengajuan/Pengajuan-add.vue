<template>
  <div class="flex h-screen bg-gray-100">
    <Sidebar :activeMenu="activeMenu" @update:activeMenu="activeMenu = $event" />
    <div class="flex-1 p-8 pt-7 flex flex-col bg-white">
      <HeaderBar title="Form Pengajuan Baru" />
      <div class="border-b border-gray-300 mb-4"></div>

      <div class="bg-white p-6 rounded-2xl shadow">
        <div class="flex flex-col gap-4 mx-9">
          <div class="flex items-center gap-5">
            <label class="min-w-[150px] font-semibold text-sm text-black">NID</label>
            <input type="text" v-model="formData.NID" placeholder="Masukkan NID"
              class="w-full p-2 border border-gray-300 rounded-lg text-sm" />
          </div>

          <div class="flex items-center gap-5">
            <label class="min-w-[150px] font-semibold text-sm text-black">Nama Barang</label>
            <select v-model="formData.id_inventoris_fk" @change="updateHargaSatuan"
              class="w-full p-2 border border-gray-300 rounded-lg text-sm">
              <option disabled value="">Pilih Barang</option>
              <option v-for="item in alatList" :key="item.id_alat" :value="item.id_alat">
                {{ item.nama_barang }}
              </option>
            </select>
          </div>

          <div class="flex items-center gap-5">
            <label class="min-w-[150px] font-semibold text-sm text-black">Jumlah</label>
            <input type="number" v-model="formData.jumlah" min="1" @input="hitungTotal"
              class="w-full p-2 border border-gray-300 rounded-lg text-sm" />
          </div>

          <div class="flex items-center gap-5">
            <label class="min-w-[150px] font-semibold text-sm text-black">Total Harga</label>
            <input type="text" :value="formatRupiah(formData.total)" disabled
              class="w-full p-2 border border-gray-300 rounded-lg text-sm bg-gray-100 text-gray-700" />
          </div>
          <div class="flex items-center gap-5">
            <label class="min-w-[150px] font-semibold text-sm text-black">Keterangan</label>
            <input type="text" v-model="formData.keterangan" @input="hitungTotal"
              class="w-full p-2 border border-gray-300 rounded-lg text-sm" />
          </div>
          <SuccessAlert :visible="showSuccessAlert" :message="successMessage" />
          <div class="flex justify-between items-center mt-6">
            <button @click="submitForm"
              class="bg-[#074a5d] text-white px-4 py-2 rounded-lg hover:bg-[#063843] transition cursor-pointer">
              Simpan Pengajuan
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
      hargaSatuan: 0,
      formData: {
        NID: "",
        id_inventoris_fk: "",
        jumlah: 1,
        total: 0,
        status: "waiting_approval_1",
        tanggal_permintaan: "", // akan diisi otomatis
        keterangan: "",
      },
      showSuccessAlert: false,
      successMessage: "",
    };
  },
  mounted() {
    this.fetchAlat();
    this.formData.tanggal_permintaan = new Date().toISOString().split("T")[0]; // Format YYYY-MM-DD
  },
  methods: {
    fetchAlat() {
      const token = localStorage.getItem("token");
      axios
        .get("http://localhost:8000/api/alat", {
          headers: { Authorization: `Bearer ${token}` },
        })
        .then((res) => {
          this.alatList = res.data.data;
        });
    },
    updateHargaSatuan() {
      const selected = this.alatList.find(item => item.id_alat === this.formData.id_inventoris_fk);
      this.hargaSatuan = selected ? selected.harga_satuan : 0;
      this.hitungTotal();
    },
    hitungTotal() {
      this.formData.total = this.formData.jumlah * this.hargaSatuan;
    },
    formatRupiah(angka) {
      if (!angka) return "-";
      return "Rp. " + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    },
    async submitForm() {
      const token = localStorage.getItem("token");
      try {
        await axios.post("http://localhost:8000/api/request", this.formData, {
          headers: {
            Authorization: `Bearer ${token}`,
            Accept: "application/json",
          },
        }).then(() => {
          this.successMessage = "Pengajuan berhasil disimpan.";
          this.showSuccessAlert = true;
          setTimeout(() => {
            this.showSuccessAlert = false;
            this.$router.push("/pengajuan");
          }, 2500);
        });
      } catch (error) {
        console.error(error);
        alert("Gagal menyimpan pengajuan.");
      }
    },
  },
};
</script>
