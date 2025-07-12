<template>
  <div v-if="visible" class="fixed inset-0 flex items-center justify-center bg-[rgba(0,0,0,0.7)] bg-opacity-50 z-50">
    <div class="bg-white rounded-lg p-6 w-96 shadow-lg">
      <h2 class="text-lg font-semibold mb-4 text-[#333]">Masukkan Alasan Penolakan</h2>
      <textarea v-model="reasonLocal" rows="4" placeholder="Tulis alasan penolakan..."
        class="w-full border border-gray-300 rounded-md p-2 text-sm text-gray-700"></textarea>
      <div class="flex justify-end mt-4 space-x-2">
        <button @click="$emit('cancel')" class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-sm cursor-pointer">Batal</button>
        <button @click="confirmReject" :disabled="!reasonLocal"
          class="px-4 py-2 rounded bg-red-500 hover:bg-red-600 text-white text-sm disabled:opacity-50 cursor-pointer">
          Tolak
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    visible: Boolean,
    reason: String,
  },
  emits: ['cancel', 'confirm'],
  data() {
    return {
      reasonLocal: this.reason || '',
    };
  },
  watch: {
    reason(newVal) {
      this.reasonLocal = newVal;
    },
  },
  methods: {
    confirmReject() {
      this.$emit('confirm', this.reasonLocal);
    },
  },
};
</script>
