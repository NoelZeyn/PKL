<template>
    <div class="flex h-screen bg-gray-100">
        <Sidebar :activeMenu="activeMenu" @update:activeMenu="updateActiveMenu" />
        <div class="flex-1 p-8 pt-4 bg-white">
            <HeaderBar title="Profile" class="mt-3" />
            <div class="my-4 border-b border-gray-300"></div>

            <div class="flex-grow w-full p-8">
                <div class="bg-white border border-gray-300 rounded-lg shadow-sm overflow-hidden">
                    <div class="flex justify-between items-center px-5 py-3 border-b border-gray-300">
                        <h3 class="text-sm font-bold text-gray-900">Data Profil Pengguna</h3>
                    </div>

                    <div class="p-4 md:p-6">
                        <h4 class="text-center text-base font-medium text-gray-900 mb-6">Informasi Profil</h4>

                        <div class="flex justify-center my-5">
                            <img :src="profileImage || defaultProfileImage" alt="Foto Profil"
                                class="w-24 h-24 md:w-28 md:h-28 rounded-full object-cover border-2 border-gray-300" />
                        </div>

                        <div class="space-y-4">
                            <div class="flex flex-col md:flex-row items-start md:items-center gap-2 md:gap-5">
                                <label class="md:w-[150px] font-bold text-sm text-gray-900">Nama Lengkap</label>
                                <input type="text" v-model="formData.nama_lengkap"
                                    class="w-full bg-gray-50 border border-gray-300 rounded-md px-4 py-2 h-10"
                                    readonly />
                            </div>

                            <div class="flex flex-col md:flex-row items-start md:items-center gap-2 md:gap-5">
                                <label class="md:w-[150px] font-bold text-sm text-gray-900">Jabatan</label>
                                <input type="text" v-model="formData.jabatan"
                                    class="w-full bg-gray-50 border border-gray-300 rounded-md px-4 py-2 h-10"
                                    readonly />
                            </div>

                            <div class="flex flex-col md:flex-row items-start md:items-center gap-2 md:gap-5">
                                <label class="md:w-[150px] font-bold text-sm text-gray-900">Kontak</label>
                                <input type="text" v-model="formData.kontak"
                                    class="w-full bg-gray-50 border border-gray-300 rounded-md px-4 py-2 h-10"
                                    readonly />
                            </div>

                            <div class="flex flex-col md:flex-row items-start md:items-center gap-2 md:gap-5">
                                <label class="md:w-[150px] font-bold text-sm text-gray-900">NID</label>
                                <input type="text" v-model="formData.NID"
                                    class="w-full bg-gray-50 border text-gray-500 border-gray-300 rounded-md px-4 py-2 h-10"
                                    disabled />
                            </div>

                            <div class="flex flex-col md:flex-row items-start md:items-center gap-2 md:gap-5">
                                <label class="md:w-[150px] font-bold text-sm text-gray-900">Ubah Password</label>
                                <router-link to="/ubah-password"
                                    class="cursor-pointer px-8 py-2 text-white bg-[#074a5d] border border-[#074a5d] rounded-full font-medium hover:bg-gray-100 hover:text-black focus:outline-none focus:ring-2 focus:ring-[#074a5d]">
                                    Ubah Password
                                </router-link>
                            </div>
                        </div>

                        <div class="flex justify-end mt-8">
                            <router-link to="/profile-edit"
                                class="cursor-pointer px-8 py-2 text-white bg-[#074a5d] border border-[#074a5d] rounded-full font-medium hover:bg-gray-100 hover:text-black focus:outline-none focus:ring-2 focus:ring-[#074a5d]">
                                Edit Profile
                            </router-link>
                        </div>
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
import defaultProfileImage from "@/assets/user.png";

export default {
    name: "Profile",
    components: { Sidebar, HeaderBar },
    data() {
        return {
            activeMenu: "profile",
            formData: {
                nama_lengkap: "",
                jabatan: "",
                kontak: "",
                NID: "",
                foto_profil: "",
            },
            profileImage: null,
            defaultProfileImage,
            isLoading: false,
            error: null,
        };
    },
    mounted() {
        this.fetchProfile();
    },
    methods: {
        async fetchProfile() {
            this.isLoading = true;
            this.error = null;
            try {
                const token = localStorage.getItem("token");
                if (!token) {
                    this.error = "Token tidak ditemukan, silakan login terlebih dahulu.";
                    this.isLoading = false;
                    return;
                }
                const response = await axios.get("http://localhost:8000/api/profile", {
                    headers: {
                        Authorization: `Bearer ${token}`,
                        Accept: "application/json",
                    },
                });

                if (response.data.status === "success") {
                    const user = response.data.data;
                    this.formData.nama_lengkap = user.nama_lengkap || "";
                    this.formData.jabatan = user.jabatan || "";
                    this.formData.kontak = user.kontak || "";
                    this.formData.NID = user.NID || "";
                    this.formData.foto_profil = user.foto_profil || "";
                    this.profileImage = user.foto_profil || null;
                } else {
                    this.error = response.data.message || "Gagal memuat data profil.";
                }
            } catch (error) {
                this.error = error.response?.data?.message || error.message || "Terjadi kesalahan.";
            } finally {
                this.isLoading = false;
            }
        },
        updateActiveMenu(newMenu) {
            this.activeMenu = newMenu;
        },
    },
};
</script>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap");

* {
    font-family: "Inter", sans-serif;
}
</style>
