<template>
    <div class="flex min-h-[50vh] bg-gray-100">
        <Sidebar :activeMenu="activeMenu" @update:activeMenu="updateActiveMenu" />
        <div class="flex-1 p-8 pt-4 bg-white">
            <HeaderBar title="Edit Profile" class="mt-3" />
            <div class="my-4 border-b border-gray-300"></div>

            <div class="bg-white border border-gray-300 rounded-lg shadow mt-6">
                <div class="p-6">
                    <h3 class="text-sm font-bold text-gray-900 border-b pb-4">
                        Edit Profil Pengguna
                    </h3>

                    <form @submit.prevent="submitProfile" class="mt-4 space-y-4">
                        <div class="flex flex-col sm:flex-row items-center gap-4">
                            <label class="w-full sm:w-40 text-sm font-semibold text-gray-700">Nama Lengkap</label>
                            <input v-model="form.nama_lengkap" type="text" required
                                class="flex-1 p-2 border border-gray-300 rounded-md bg-gray-50" />
                        </div>

                        <div class="flex flex-col sm:flex-row items-center gap-4">
                            <label class="w-full sm:w-40 text-sm font-semibold text-gray-700">Jabatan</label>
                            <input v-model="form.jabatan" type="text"
                                class="flex-1 p-2 border border-gray-300 rounded-md bg-gray-50" />
                        </div>

                        <div class="flex flex-col sm:flex-row items-center gap-4">
                            <label class="w-full sm:w-40 text-sm font-semibold text-gray-700">Kontak</label>
                            <input v-model="form.kontak" type="text"
                                class="flex-1 p-2 border border-gray-300 rounded-md bg-gray-50" />
                        </div>

                        <div class="flex flex-col sm:flex-row items-center gap-4">
                            <label class="w-full sm:w-40 text-sm font-semibold text-gray-700">Foto Profil</label>
                            <input type="file" @change="handleFileChange" accept="image/*"
                                class="flex-1 p-2 flex-1 p-2 border border-gray-300 rounded-md bg-gray-50" />
                        </div>

                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mt-6">
                            <button type="submit"
                                class="cursor-pointer px-8 py-2 text-white bg-[#074a5d] border border-[#074a5d] rounded-full font-medium hover:bg-gray-100 hover:text-black focus:outline-none focus:ring-2 focus:ring-[#074a5d]">
                                Simpan Perubahan
                            </button>
                            <router-link to="/profile"
                                class="cursor-pointer px-8 py-2 text-white bg-[#074a5d] border border-[#074a5d] rounded-full font-medium hover:bg-gray-100 hover:text-black focus:outline-none focus:ring-2 focus:ring-[#074a5d]">
                                Batal
                            </router-link>
                        </div>
                    </form>

                    <SuccessAlert :visible="showSuccessAlert" :message="successMessage" />

                    <div class="my-4 border-t border-gray-300"></div>
                    <div v-if="message" class="text-green-600">
                        {{ message }}
                    </div>
                    <div v-if="error" class="text-red-600">{{ error }}</div>
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
    name: "ProfileEdit",
    components: { Sidebar, HeaderBar, SuccessAlert },
    data() {
        return {
            activeMenu: "profile",
            form: {
                nama_lengkap: "",
                jabatan: "",
                posisi: "",
                kontak: "",
            },
            file: null, // Inisialisasi file di data
            message: "",
            error: "",
            showSuccessAlert: false,
            successMessage: "",
        };
    },
    mounted() {
        this.fetchProfile();
    },
    methods: {
        updateActiveMenu(newMenu) {
            this.activeMenu = newMenu;
        },
        handleFileChange(e) {
            this.file = e.target.files[0];
        },
        async fetchProfile() {
            try {
                const token = localStorage.getItem("token");
                const id = localStorage.getItem("id");
                const res = await axios.get(
                    "http://localhost:8000/api/profile",
                    {
                        headers: { Authorization: `Bearer ${token}` },
                    }
                );
                if (res.data.status === "success") {
                    const user = res.data.data;
                    this.form = {
                        nama_lengkap: user.nama_lengkap,
                        jabatan: user.jabatan,
                        posisi: user.posisi || "",
                        kontak: user.kontak || "",
                    };
                }
            } catch (err) {
                this.error =
                    err.response?.data?.message || "Gagal mengambil data.";
            }
        },
        async submitProfile() {
            try {
                const token = localStorage.getItem("token");
                const formData = new FormData();
                formData.append("_method", "PUT");
                formData.append("nama_lengkap", this.form.nama_lengkap);
                formData.append("jabatan", this.form.jabatan);
                formData.append("posisi", this.form.posisi);
                formData.append("kontak", this.form.kontak);
                if (this.file) {
                    formData.append("foto_profil", this.file);
                }
                const res = await axios.post(
                    "http://localhost:8000/api/profile",
                    formData,
                    {
                        headers: {
                            Authorization: `Bearer ${token}`,
                            "Content-Type": "multipart/form-data",
                        },
                    }
                );
                if (res.data.status === "success") {
                    this.successMessage = "Profil berhasil diperbarui.";
                    this.showSuccessAlert = true;
                    this.error = "";

                    // ✅ Tutup alert dan redirect setelah 3 detik
                    setTimeout(() => {
                        this.showSuccessAlert = false;
                        this.$router.push("/profile");
                    }, 3000);
                } else {
                    this.error =
                        res.data.message || "Gagal memperbarui profil.";
                }
            } catch (err) {
                // Tampilkan error validasi dari Laravel jika ada
                if (
                    err.response?.status === 422 &&
                    err.response?.data?.errors
                ) {
                    const errors = err.response.data.errors;
                    this.error = Object.values(errors).flat().join(", ");
                } else {
                    this.error =
                        err.response?.data?.message ||
                        "Terjadi kesalahan saat memperbarui profil.";
                }
            }
        },
    },
};
</script>
