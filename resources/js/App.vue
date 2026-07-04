<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 via-purple-900 to-gray-900">
    <nav v-if="authStore.isAuthenticated" class="bg-gray-800 shadow-lg">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <router-link to="/dashboard" class="text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-pink-400">
              🎰 Gacha System
            </router-link>
          </div>
          <div class="flex items-center space-x-4">
            <div class="flex items-center bg-gray-700 rounded-full px-4 py-2">
              <span class="text-yellow-400 mr-2">🪙</span>
              <span class="text-white font-semibold">{{ authStore.user?.coins || 0 }}</span>
            </div>
            <span class="text-gray-300">{{ authStore.user?.name }}</span>
            <router-link
              v-if="authStore.isAdmin"
              to="/admin"
              class="text-purple-400 hover:text-purple-300 font-medium"
            >
              Admin Panel
            </router-link>
            <button
              @click="logout"
              class="text-gray-400 hover:text-white transition-colors"
            >
              Logout
            </button>
          </div>
        </div>
      </div>
    </nav>

    <main>
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { useAuthStore } from './stores/auth';
import axios from 'axios';

const router = useRouter();
const authStore = useAuthStore();

const logout = async () => {
  try {
    await axios.post('/logout');
  } catch (e) {
    // Ignore errors
  }
  authStore.logout();
  router.push('/login');
};
</script>
