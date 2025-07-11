<template>
    <div>
        <!-- Hamburger Button (mobile only) -->
        <button
            class="fixed top-2.5 left-2.5 z-[1001] bg-white border border-gray-300 px-3 py-2 text-lg cursor-pointer md:hidden"
            @click="toggleSidebar">
            ☰
        </button>

        <!-- Sidebar -->
        <aside :class="[
            'transition-transform duration-300 z-[1000] fixed md:static top-0 left-0 min-h-screen w-[320px] bg-white border-r border-gray-200 pl-7 pt-7 flex flex-col gap-6',
            { '-translate-x-full': !isSidebarOpen && isMobile },
        ]">
            <!-- Logo -->
            <div class="flex items-center gap-1 border-b border-gray-200 pb-2">
                <img :src="logoImage" alt="Logo" class="w-[35px] object-cover rounded-t-[10px] mt-[-5px]" />
                <span class="logo-text text-[20px] font-bold text-[#08607a] font-['Protest_Strike']">System ATK
                    PLN</span>
            </div>

            <!-- Menu Utama -->
            <div class="flex flex-col gap-2">
                <p class="text-sm font-semibold text-[#b0b385] mb-1">
                    Main Menu
                </p>
                <ul>
                    <router-link to="/dashboard" class="block">
                        <li :class="menuClass('dashboard')">
                            <img src="@/assets/dashboard.svg" class="w-5" alt="Dashboard" />
                            <span>Dashboard</span>
                        </li>
                    </router-link>
                    <router-link v-if="role === 'superadmin'" to="/manajemen-akun" class="block">
                        <li :class="menuClass('manajemenAkun')">
                            <img src="@/assets/profil.svg" class="w-5" alt="Verifikasi" />
                            <span>Manajemen Akun</span>
                        </li>
                    </router-link>
                    <router-link v-if="role === 'superadmin' || role === 'admin'"
                        to="/manajemen-approval" class="block">
                        <li :class="menuClass('manajemenApproval')">
                            <img src="@/assets/profil.svg" class="w-5" alt="Approval" />
                            <span>{{ getApprovalMenuName(role) }}</span>
                        </li>
                    </router-link>
                    <router-link v-if="role !== 'user_review'" to="/manajemen-alat" class="block">
                        <li :class="menuClass('manajemenAlat')" @click="setActive('manajemenAlat')">
                            <img src="@/assets/laporan1.svg" class="w-5" alt="Iaporan" />
                            <span>Manajemen Alat</span>
                        </li>
                    </router-link>
                    <router-link v-if="role !== 'user_review'" to="/pengajuan" class="block">
                        <li :class="menuClass('pengajuan')" @click="setActive('pengajuan')">
                            <img src="@/assets/laporan1.svg" class="w-5" alt="pengajuan" />
                            <span>Pengajuan ATK</span>
                        </li>
                    </router-link>

                </ul>
            </div>

            <!-- Menu Admin -->
            <div class="flex flex-col gap-2" >
                <p class="text-sm font-semibold text-[#b0b385] mb-1">Laporan</p>
                <ul>
                    <router-link to="/grafik" class="block">
                        <li :class="menuClass('grafik')" @click="setActive('grafik')">
                            <img src="@/assets/pasien.svg" class="w-5" alt="grafik" />
                            <span>Grafik ATK</span>
                        </li>
                    </router-link>
                    <router-link to="/laporan-ATK" class="block">
                        <li :class="menuClass('laporanATK')" @click="setActive('laporanATK')">
                            <img src="@/assets/pasien.svg" class="w-5" alt="laporanATK" />
                            <span>Data ATK</span>
                        </li>
                    </router-link>
                    <router-link v-if="role !== 'user'"  to="/laporan-history-atk"
                        class="block">
                        <li :class="menuClass('laporanHistoryATK')" @click="setActive('laporanHistoryATK')">
                            <img src="@/assets/pasien.svg" class="w-5" alt="laporanHistoryATK" />
                            <span>Laporan History ATK</span>
                        </li>
                    </router-link>
                    <router-link v-if="role !== 'user'" to="/laporan-pemakaian"
                        class="block">
                        <li :class="menuClass('laporanPemakaian')" @click="setActive('laporanPemakaian')">
                            <img src="@/assets/pasien.svg" class="w-5" alt="laporanPemakaian" />
                            <span>History Pemakaian ATK</span>
                        </li>
                    </router-link>
                    <router-link v-if="role !== 'user'"  to="/laporan-approval"
                        class="block">
                        <li :class="menuClass('laporanApproval')" @click="setActive('laporanApproval')">
                            <img src="@/assets/pasien.svg" class="w-5" alt="laporanApproval" />
                            <span>History Approval ATK</span>
                        </li>
                    </router-link>
                    <router-link v-if="role !== 'user'"  to="/laporan-pengajuan"
                        class="block">
                        <li :class="menuClass('laporanPengajuan')" @click="setActive('laporanPengajuan')">
                            <img src="@/assets/pasien.svg" class="w-5" alt="laporanPengajuan" />
                            <span>Laporan Pengajuan ATK</span>
                        </li>
                    </router-link>
                </ul>
            </div>
            <div class="flex flex-col gap-2">
                <p class="text-sm font-semibold text-[#b0b385] mb-1">Admin</p>
                <ul>
                    <router-link to="/profile" class="block">
                        <li :class="menuClass('profile')">
                            <img src="@/assets/profil.svg" class="w-5" alt="Profile" />
                            <span>Profile</span>
                        </li>
                    </router-link>
                    <li class="flex items-center gap-2 px-3 py-2 rounded-md text-sm text-[#7d7f81] hover:bg-slate-100 cursor-pointer"
                        @click="showModalConfirm = true">
                        <img src="@/assets/SignOut.svg" class="w-5" alt="Keluar" />
                        <span>Keluar</span>
                    </li>
                </ul>
            </div>

            <ModalConfirm :visible="showModalConfirm" title="Konfirmasi Logout"
                message="Apakah Anda yakin ingin keluar?" @confirm="logout" @cancel="showModalConfirm = false" />
        </aside>
    </div>
</template>

<script>
import axios from "axios";
import logoImage from "@/assets/PLN.svg";
import ModalConfirm from "@/components/ModalConfirm.vue";

export default {
    name: "Sidebar",
    components: { ModalConfirm },
    props: ["activeMenu"],
    emits: ["update:activeMenu"],
    data() {
        const storedUser = localStorage.getItem("user");
        const parsedUser = storedUser ? JSON.parse(storedUser) : {};
        return {
            logoImage,
            showModalConfirm: false,
            role: parsedUser.tingkatan_otoritas || "",  // ✅ Ambil role dengan benar
            isSidebarOpen: true,
            isMobile: window.innerWidth < 768,
        };
    },
    mounted() {
        window.addEventListener("resize", this.checkScreenSize);

        const storedUser = localStorage.getItem("user");
        const parsedUser = storedUser ? JSON.parse(storedUser) : {};
        this.role = parsedUser.tingkatan_otoritas || "";
    },
    beforeUnmount() {
        window.removeEventListener("resize", this.checkScreenSize);
    },
    methods: {
        getApprovalMenuName(role) {
            const roleToName = {
                admin: 'Manajemen Approval Level 1',
                superadmin: 'Manajemen Approval Level 2',
                anggaran: 'Manajemen Approval Anggaran',
            };
            return roleToName[role] || 'Manajemen Approval';
        },
        setActive(menu) {
            this.$emit("update:activeMenu", menu);
        },
        logout() {
            axios
                .post(
                    "http://localhost:8000/api/logout",
                    {},
                    {
                        headers: {
                            Authorization: `Bearer ${localStorage.getItem(
                                "token"
                            )}`,
                        },
                    }
                )
                .then(() => {
                    localStorage.clear();
                    sessionStorage.clear();
                    this.showModalConfirm = false;
                    this.$router.push("/login");
                })
                .catch((error) => {
                    console.error("Logout gagal:", error);
                    alert("Gagal logout. Silakan coba lagi.");
                });
        },
        toggleSidebar() {
            if (this.isMobile) {
                this.isSidebarOpen = !this.isSidebarOpen;
            }
        },
        checkScreenSize() {
            this.isMobile = window.innerWidth < 768;
            if (!this.isMobile) {
                this.isSidebarOpen = true;
            }
        },
        menuClass(name) {
            const isActive = this.activeMenu === name;
            return [
                "flex items-center gap-2 px-3 py-2 rounded-md text-sm cursor-pointer w-full",
                isActive
                    ? "bg-[#84bbd1] text-[#074a5d] font-semibold"
                    : "text-[#7d7f81] hover:bg-slate-100",
            ];
        },
    },
};
</script>

<style scoped>
.logo-text {
    font-family: "Protest Strike", sans-serif;
    font-size: 26px;
    color: #08607a;
}
</style>
