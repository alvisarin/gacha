<template>
  <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
      <div class="card">
        <div class="text-center mb-8">
          <h1 class="text-3xl font-bold text-gray-900">🎰 Create Account</h1>
          <p class="mt-2 text-gray-600">Join and get 500 coins free!</p>
        </div>

        <form @submit.prevent="register" class="space-y-6">
          <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            {{ error }}
          </div>

          <div>
            <label for="name" class="label">Full Name</label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              required
              class="input"
              placeholder="Enter your name"
            />
          </div>

          <div>
            <label for="email" class="label">Email address</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              class="input"
              placeholder="Enter your email"
            />
          </div>

          <div>
            <label for="password" class="label">Password</label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              required
              minlength="8"
              class="input"
              placeholder="Create a password (min 8 characters)"
            />
          </div>

          <div>
            <label for="password_confirmation" class="label">Confirm Password</label>
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              type="password"
              required
              class="input"
              placeholder="Confirm your password"
            />
          </div>

          <button
            type="submit"
            :disabled="isLoading"
            class="w-full btn btn-primary py-3"
          >
            <span v-if="isLoading">Creating account...</span>
            <span v-else>Register & Get 500 Coins</span>
          </button>
        </form>

        <div class="mt-6 text-center">
          <p class="text-gray-600">
            Already have an account?
            <router-link to="/login" class="text-primary-600 hover:text-primary-500 font-medium">
              Sign in here
            </router-link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import axios from 'axios';

const router = useRouter();
const authStore = useAuthStore();

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});

const isLoading = ref(false);
const error = ref('');

const register = async () => {
  if (form.password !== form.password_confirmation) {
    error.value = 'Passwords do not match';
    return;
  }

  isLoading.value = true;
  error.value = '';

  try {
    const response = await axios.post('/register', form);
    authStore.setAuth(response.data.user, response.data.token);
    router.push('/dashboard');
  } catch (e) {
    if (e.response?.data?.errors) {
      error.value = Object.values(e.response.data.errors).flat().join(' ');
    } else {
      error.value = e.response?.data?.message || 'Registration failed. Please try again.';
    }
  } finally {
    isLoading.value = false;
  }
};
</script>
