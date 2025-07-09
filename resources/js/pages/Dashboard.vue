<template>
  <div class="flex flex-wrap h-screen bg-gray-100">
    <!-- Sidebar -->
    <Sidebar :activeMenu="activeMenu" @update:activeMenu="updateActiveMenu" />

    <!-- Main Content -->
    <div class="flex-1 p-8 pt-4 bg-white">
      <HeaderBar title="Dashboard" class="mt-3"/>
      <div class="my-4 border-b border-gray-300"></div>

      <!-- Header Section -->
      <div class="flex flex-wrap justify-between items-center mt-[-10px] pt-5 gap-2">
        <div>
          <h3 class="text-lg font-semibold text-gray-800">
            Selamat Datang, {{ user.NID }} {{ user.tingkatan_otoritas }} 👋
          </h3>
        </div>
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg border text-sm font-bold text-gray-800 bg-white border-gray-300">
          <img class="w-5 h-5" :src="iconKalender" alt="icon" />
          <span>{{ formattedDate }}</span>
        </div>
      </div>

      <!-- Menu Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-6">
        <div
          v-for="menu in quickMenus"
          :key="menu.title"
          class="flex items-center gap-3 p-5 rounded-xl bg-white border border-gray-200 shadow-lg hover:bg-blue-50 active:bg-blue-700 active:text-white transition-all cursor-pointer"
          @click="navigateTo(menu.path)"
        >
          <div class="flex items-center justify-center">
            <img :src="menu.icon" class="w-6 h-6 object-cover" :alt="menu.title" />
          </div>
          <p class="font-semibold text-blue-900">{{ menu.title }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Sidebar from "@/components/Sidebar.vue";
import HeaderBar from "@/components/HeaderBar.vue";

import iconKalender from "@/assets/kalender.svg";
import iconLaporan from "@/assets/laporan.svg";
import iconStetoskop from "@/assets/stetoskop.svg";
import iconPasien from "@/assets/pasien.svg";
import iconPosyandu from "@/assets/posko.svg";
import logoImage from "@/assets/posyandu.svg";

export default {
  name: "DashboardComponent",
  components: {
    Sidebar,
    HeaderBar,
  },
  data() {
    return {
                  activeMenu: "dashboard",
      logoImage,
      iconKalender,
      user: {},
      activeMenu: "dashboard",
      today: new Date(),
      quickMenus: [],
    };
  },
  computed: {
    formattedDate() {
      const options = { day: "numeric", month: "long" };
      const dateStr = this.today.toLocaleDateString("id-ID", options);
      const [day, month] = dateStr.split(" ");
      return `${month} ${day}`;
    },
  },
  methods: {
    updateActiveMenu(menu) {
      this.activeMenu = menu;
    },
    navigateTo(path) {
      if (path) this.$router.push(path);
    },
  },
  mounted() {
  const storedUser = localStorage.getItem("user");
  if (storedUser) {
    this.user = JSON.parse(storedUser);
  }
    this.quickMenus = [
      {
        icon: iconPosyandu,
        title: "Admin",
        path: "/posko-posyandu",
      },
      {
        icon: iconStetoskop,
        title: "Pemeriksaan Kesehatan",
        path: "/pemeriksaan",
      },
      {
        icon: iconPasien,
        title: "Warga",
        path: "/datapasien",
      },
      {
        icon: iconLaporan,
        title: "Laporan Kesehatan",
        path: "/laporan-kesehatan",
      },
    ];
  },
};
</script>
