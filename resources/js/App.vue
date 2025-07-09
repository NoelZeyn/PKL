<template>
  <div>
    <router-view></router-view>
  </div>
</template>

<script>
export default {
  created() {
    const token = localStorage.getItem('token');
    if (token) {
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    }
  }
};
</script>

<style scoped>
nav {
  background: #007bff;
  padding: 10px;
  text-align: center;
}

ul {
  list-style: none;
  padding: 0;
  display: flex;
  justify-content: center;
}

li {
  margin: 0 15px;
}

a {
  color: white;
  text-decoration: none;
  font-weight: bold;
}

a:hover {
  text-decoration: underline;
}
</style>
