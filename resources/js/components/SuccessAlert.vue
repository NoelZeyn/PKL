<template>
  <div v-if="visible" class="alert-container">
    <div class="alert-success">
      <span class="icon">✅</span>
      <div class="message">{{ message }}</div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    visible: {
      type: Boolean,
      required: true
    },
    message: {
      type: String,
      default: "Berhasil!"
    }
  }
};
</script>

<style scoped>
.alert-container {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 9999;
  animation: fadeInOut 3s ease-in-out forwards;
}

.alert-success {
  background-color: #d1fae5;
  color: #065f46;
  padding: 12px 20px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.icon {
  margin-right: 10px;
  font-size: 1.2rem;
}

@keyframes fadeInOut {
  0% { opacity: 0; transform: translateY(-10px); }
  10% { opacity: 1; transform: translateY(0); }
  90% { opacity: 1; transform: translateY(0); }
  100% { opacity: 0; transform: translateY(-10px); }
}

@media screen and (max-width: 768px) {
  .alert-container {
    top: 10px;
    right: 10px;
    left: 10px;
    transform: none;
    width: auto;
    max-width: 90%;
  }

  .alert-success {
    font-size: 14px;
    padding: 10px 16px;
    border-radius: 6px;
  }

  .icon {
    font-size: 1rem;
    margin-right: 8px;
  }
}

</style>
