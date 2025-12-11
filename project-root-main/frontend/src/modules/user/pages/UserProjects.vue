<template>
  <header class="page-head">
    <div class="header-content">
      <h1>Projects</h1>
      <p class="muted">List of all projects.</p>
    </div>
  </header>

  <main class="container">
    <section class="card">
      <div class="card-title">
        <div class="title-section">
          <h2>All Projects</h2>
          <span class="subtitle muted">{{ filteredProjects.length }} projects found</span>
        </div>
        <div class="search-sort-section">
          <input type="text" v-model="searchTerm" placeholder="Search projects...">
          <button @click="sortBy('name')">Sort by Name</button>
          <button @click="sortBy('advisor_name')">Sort by Advisor</button>
        </div>
      </div>

      <div class="table-container">
        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Advisor</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="project in filteredProjects" :key="project.id">
                <td>{{ project.id }}</td>
                <td>{{ project.name }}</td>
                <td>{{ project.description }}</td>
                <td>{{ project.advisor_name }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <span>Loading projects...</span>
      </div>
      <div v-if="error" class="error-state">
        <p>{{ error }}</p>
      </div>
    </section>
  </main>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const projects = ref([]);
const loading = ref(false);
const error = ref('');
const searchTerm = ref('');
const sortKey = ref('name');
const sortOrder = ref('asc');

async function fetchProjects() {
  loading.value = true;
  error.value = '';
  try {
    const response = await axios.get('/api/get-projects.php');
    if (response.data.status === 'success') {
      projects.value = response.data.projects;
    } else {
      error.value = response.data.message;
    }
  } catch (e) {
    error.value = e.response?.data?.message || e.message || 'Failed to load projects.';
  }
  loading.value = false;
}

const filteredProjects = computed(() => {
  let filtered = projects.value;

  if (searchTerm.value) {
    filtered = filtered.filter(p =>
      p.name.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
      p.advisor_name.toLowerCase().includes(searchTerm.value.toLowerCase())
    );
  }

  filtered.sort((a, b) => {
    let result = 0;
    if (a[sortKey.value] < b[sortKey.value]) result = -1;
    if (a[sortKey.value] > b[sortKey.value]) result = 1;
    return sortOrder.value === 'asc' ? result : -result;
  });

  return filtered;
});

function sortBy(key) {
  if (sortKey.value === key) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortKey.value = key;
    sortOrder.value = 'asc';
  }
}

onMounted(fetchProjects);
</script>

<style scoped>
/* Add your styles here */
</style>
