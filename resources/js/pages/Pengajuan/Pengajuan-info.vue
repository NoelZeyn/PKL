<template>
  <div class="flex flex-col lg:flex-row min-h-screen bg-gray-100">
    <Sidebar :activeMenu="activeMenu" @update:activeMenu="activeMenu = $event" />

    <div class="flex-1 p-4 sm:p-6 md:p-8 pt-6 bg-white">
      <HeaderBar title="Manajemen Stock Alat" />
      <div class="border-b border-gray-300 my-4"></div>

      <div class="bg-white p-4 sm:p-6 rounded-2xl shadow">
        <div class="flex flex-col gap-4">

          <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-5">
            <label class="sm:min-w-[150px] font-semibold text-sm text-black">Nama Barang</label>
            <select
              v-model="selectedAlatId"
              @change="onAlatChange"
              class="w-full p-2 border border-gray-300 rounded-lg text-sm"
              required
            >
              <option disabled value="">Pilih Barang</option>
              <option v-for="item in alatList" :key="item.id_alat" :value="item.id_alat">
                {{ item.nama_barang }}
              </option>
            </select>
          </div>

          <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-5">
            <label class="sm:min-w-[150px] font-semibold text-sm text-black">Stock Minimal</label>
            <input
              type="number"
              v-model.number="formData.stock_min"
              min="0"
              class="w-full p-2 border border-gray-300 rounded-lg text-sm"
            />
          </div>

          <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-5">
            <label class="sm:min-w-[150px] font-semibold text-sm text-black">Stock Maximal</label>
            <input
              type="number"
              v-model.number="formData.stock_max"
              min="0"
              class="w-full p-2 border border-gray-300 rounded-lg text-sm"
            />
          </div>

          <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-5">
            <label class="sm:min-w-[150px] font-semibold text-sm text-black">Stock Sekarang</label>
            <input
              type="number"
              v-model.number="formData.stock"
              min="0"
              class="w-full p-2 border border-gray-300 rounded-lg text-sm"
            />
          </div>

          <SuccessAlert :visible="showSuccessAlert" :message="successMessage" />

          <div class="flex flex-col sm:flex-row justify-between gap-3 mt-6">
            <router-link to="/manajemen-alat">
              <button
                class="w-full sm:w-auto bg-[#074a5d] cursor-pointer text-white px-4 py-2 rounded-lg hover:bg-[#063843] transition cursor-pointer "
              >
                Kembali
              </button>
            </router-link>
            <button
              @click="submitForm"
              class="w-full sm:w-auto bg-[#074a5d] text-white px-4 py-2 rounded-lg hover:bg-[#063843] transition cursor-pointer"
            >
              Simpan Perubahan Stock
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
import axios from "axios";

export default {
    name: "ManajemenPengajuanInfo",
    components: {
        Sidebar,
        HeaderBar,
    },
    data() {
        return {
            activeMenu: "pengajuan",
            kategoriList: [],
            formData: {
                NID: "",
                nama_barang: "",
                id_kategori_fk: "",
                keterangan: "",
                stock_min: "",
                stock_max: "",
                satuan: "",
                harga_satuan: "",
                harga_estimasi: "",
                tanggal_permintaan: "",
                status: "",
                keterangan: "",
                status_by: "",
                jumlah: "",
                total: "",
            },

        };
    },
    mounted() {
        this.fetchPengajuan();
    },
    methods: {
        formatRupiah(angka) {
            if (!angka) return '-';
            return 'Rp. ' + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        },

        formatTanggal(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            return date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
        },

        statusBadge(status) {
            const map = {
                draft: 'bg-yellow-200 text-yellow-800',
                waiting_approval_1: 'bg-yellow-200 text-yellow-800',
                waiting_approval_2: 'bg-yellow-200 text-yellow-800',
                waiting_approval_3: 'bg-yellow-200 text-yellow-800',
                approved: 'bg-green-200 text-green-800',
                rejected: 'bg-red-200 text-red-800',
            };
            return map[status] || 'bg-gray-200 text-gray-800';
        },

        statusLabel(status) {
            const map = {
                draft: 'Draft',
                waiting_approval_1: 'Approval 1',
                waiting_approval_2: 'Approval 2',
                waiting_approval_3: 'Approval 3',
                approved: 'Disetujui',
                rejected: 'Ditolak',
            };
            return map[status] || status;
        },
        fetchPengajuan() {
            const token = localStorage.getItem("token");
            const id = this.$route.params.id; // Ambil ID dari route params
            axios
                .get(`http://localhost:8000/api/request/${id}`, {
                    headers: {
                        Authorization: `Bearer ${token}`,
                        Accept: "application/json",
                    },
                })
                .then((response) => {
                    this.formData = response.data.data;
                })

                .catch((error) => {
                    console.error("Error fetching alat:", error);
                });
        },
    },
};
</script>
