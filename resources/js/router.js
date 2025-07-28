import { createRouter, createWebHistory } from "vue-router";

// Import components
import Login from "./pages/Authentication/Login.vue";
import Register from "./pages/Authentication/Register.vue";
import RegisterNext from "./pages/Authentication/Register-next.vue";
import Dashboard from "./pages/Dashboard.vue";
import LoginTransition from "./pages/Authentication/LoginTransition.vue";
import ForgotPassword from "./pages/Authentication/ForgotPassword.vue"; 
import PendingAccess from "./pages/Authentication/PendingAccess.vue";
import InactiveAccess from "./pages/Authentication/InactiveAccess.vue";
import ManajemenAkun from "./pages/superadmin/ManajemenAkun.vue";
import Profile from "./pages/Profile/Profile.vue";
import AlatTulis from "./pages/Pengadaan/AlatTulis.vue";
import AlatAdd from "./pages/Pengadaan/Alat-add.vue";
import AlatEdit from "./pages/Pengadaan/Alat-edit.vue";
import AlatInfo from "./pages/Pengadaan/Alat-info.vue";
import Pengajuan from "./pages/Pengajuan/Pengajuan.vue";
import PengajuanInfo from "./pages/Pengajuan/Pengajuan-info.vue";
import PengajuanAdd from "./pages/Pengajuan/Pengajuan-add.vue";
import AlatPemakaian from "./pages/Pengadaan/Alat-pemakaian.vue";
import AlatStock from "./pages/Pengadaan/Alat-stock.vue";
import Grafik from "./pages/user_review/Grafik.vue";
import ManajemenApproval from "./pages/approval/ManajemenApproval.vue";
import LaporanPemakaian from "./pages/user_review/LaporanPemakaian.vue";
import LaporanATK from "./pages/user_review/LaporanATK.vue";
import LaporanApproval from "./pages/user_review/LaporanApproval.vue";
import LaporanPengajuan from "./pages/user_review/LaporanPengajuan.vue";
import LaporanHistoryATK from "./pages/user_review/LaporanHistoryATK.vue";
import PengajuanAtkAdd from "./pages/Pengajuan/Pengajuan-atk-add.vue";
import PengaturanPengajuan from "./pages/Pengajuan/Pengaturan-pengajuan.vue";
import ProfileEdit from "./pages/Profile/Profile-edit.vue";
import UbahPassword from "./pages/Profile/UbahPassword.vue";
import SuratBeritaAcara from "./pages/SuratBeritaAcara.vue";
import TambahAkun from "./pages/superadmin/TambahAkun.vue";


// Fungsi validasi token
const isTokenValid = () => {
    const token = localStorage.getItem("token");
    const expiry = localStorage.getItem("token_expiry");

    // Jika token atau expiry tidak ada
    if (!token || !expiry) {
        localStorage.clear();
        sessionStorage.clear();
        return false;
    }

    // Jika waktu sudah habis
    if (Date.now() >= parseInt(expiry)) {
        localStorage.clear();
        sessionStorage.clear();
        return false;
    }

    return true;
};


// Daftar route
const routes = [
    // Public routes (tidak butuh token)
    { path: "/", redirect: "/login", meta: { title: "Register" } },
    { path: "/login", component: Login, meta: { title: "Login" } },
    { path: "/register", component: Register, meta: { title: "Register" } },
    { path: "/register-next", component: RegisterNext, meta: { title: "RegisterNext" } },
    { path: "/loginTransition", component: LoginTransition, meta: { title: "Login Transition" } },
    { path: "/forgot-password", component: ForgotPassword, meta: { title: "Forgot Password" } },
    { path: "/pending-access", component: PendingAccess, meta: { title: "Pending Access" } },
    { path: "/inactive-access", component: InactiveAccess, meta: { title: "Inactive Access" } },

    // Protected routes (butuh token)
    { path: "/dashboard", component: Dashboard, meta: { requiresAuth: true, title: "Dashboard" } },
    { path: "/manajemen-akun", component: ManajemenAkun, meta: { requiresAuth: true, allowedRoles: ["superadmin"],  title: "Manajemen Akun" } },
    { path: "/tambah-akun", component: TambahAkun, meta: { requiresAuth: true, allowedRoles: ["superadmin"],  title: "Tambah Akun" } },
    { path: "/manajemen-akun", component: ManajemenAkun, meta: { requiresAuth: true, allowedRoles: ["superadmin"],  title: "Manajemen Akun" } },
    { path: "/manajemen-approval", component: ManajemenApproval, meta: { requiresAuth: true, allowedRoles: ["admin", "superadmin", "asman", "manajer", "anggaran"],  title: "Manajemen Approval" } },
    { path: "/profile", component: Profile, meta: { requiresAuth: true, title: "Profile" } },
    { path: "/profile-edit", component: ProfileEdit, meta: { requiresAuth: true, title: "Profile Edit"}},
    { path: "/ubah-password", component: UbahPassword, meta: { requiresAuth: true, title: "Ubah Password"}},

    { path: "/manajemen-alat", component: AlatTulis, meta: { requiresAuth: true,  disallowedRoles: ["user_review"], title: "Alat Tulis" } },
    { path: "/:pathMatch(.*)*", redirect: "/login", meta: { title: "Not Found" } }, // Catch-all route
    { path: "/alat-add", component: AlatAdd, meta: { requiresAuth: true, allowedRoles: ["admin", "superadmin"], title: "Tambah Alat" } },
    { path: "/alat-edit/:id", component: AlatEdit, meta: { requiresAuth: true, allowedRoles: ["superadmin", "admin"], title: "Edit Alat" } },
    { path: "/alat-info/:id", component: AlatInfo, meta: { requiresAuth: true, disallowedRoles: ["user_review"], title: "Info Alat" } },
    { path: "/alat-pemakaian", component: AlatPemakaian, meta: { requiresAuth: true, disallowedRoles: ["user_review"], title: "Pemakaian Alat" } },
    { path: "/alat-stock", component: AlatStock, meta: { requiresAuth: true, disallowedRoles: ["user_review"], title: "Stock Alat" } },

    { path: "/pengajuan", component: Pengajuan, meta: { requiresAuth: true, disallowedRoles: ["user_review"], title: "Pengajuan" } },
    { path: "/pengajuan-setting", component: PengaturanPengajuan, meta: { requiresAuth: true, allowedRoles: ["admin", "superadmin"], title: "Pengajuan Setting" } },
    { path: "/pengajuan-info/:id", component: PengajuanInfo, meta: { requiresAuth: true,  disallowedRoles: ["user_review"], title: "Info Pengajuan" } },
    { path: "/pengajuan-add", component: PengajuanAdd, meta: { requiresAuth: true,  disallowedRoles: ["user_review"], title: "Tambah Pengajuan" } },
    { path: "/pengajuan-atk-add", component: PengajuanAtkAdd, meta: { requiresAuth: true, disallowedRoles: ["user_review"], title: "Tambah Pengajuan Jenis ATK" } },

    { path: "/grafik", component: Grafik, meta: { requiresAuth: true, title: "Grafik RAB Temporary"} },
    { path: "/laporan-pemakaian", component: LaporanPemakaian, meta: { requiresAuth: true, disallowedRoles: ["user"], title: "Laporan Pemakaian Alat"} },
    { path: "/laporan-ATK", component: LaporanATK, meta: { requiresAuth: true,  disallowedRoles: ["user"], title: "Laporan ATK" } },
    { path: "/laporan-approval", component: LaporanApproval, meta: { requiresAuth: true, disallowedRoles: ["user"], title: "Laporan Approval" } },
    { path: "/laporan-pengajuan", component: LaporanPengajuan, meta: { requiresAuth: true, disallowedRoles: ["user"], title: "Laporan Pengajuan" } },
    { path: "/laporan-history-atk", component: LaporanHistoryATK, meta: { requiresAuth: true, disallowedRoles: ["user"], title: "Riwayat Manajemen ATK" } },

    { path: "/surat-ba", component: SuratBeritaAcara, meta: { requiresAuth: true, title: "Surat Berita Acara" } },
];

// Membuat router
const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Middleware validasi token
router.beforeEach((to, from, next) => {
  const tokenValid = isTokenValid();

  if (to.meta.requiresAuth && !tokenValid) {
    localStorage.clear();
    sessionStorage.clear();
    next("/dashboard");
    return;
  }
  const user = JSON.parse(localStorage.getItem("user"));
  if (to.meta.disallowedRoles && user) {
    const userRole = user.tingkatan_otoritas;
    if (to.meta.disallowedRoles.includes(userRole)) {
      alert("Akses ditolak. Anda tidak diizinkan membuka halaman ini.");
      return next("/"); // bisa ganti dengan route lain
    }
  }
  // ✅ Validasi role (hanya jika route mengatur allowedRoles)
  if (to.meta.allowedRoles && user) {
    const userRole = user.tingkatan_otoritas;
    if (!to.meta.allowedRoles.includes(userRole)) {
      alert("Akses ditolak. Anda tidak memiliki izin untuk halaman ini.");
      return next("/dashboard");
    }
  }

  next();
});



// Set judul halaman
router.afterEach((to) => {
    if (to.meta?.title) {
        document.title = to.meta.title;
    }
});

export default router;
