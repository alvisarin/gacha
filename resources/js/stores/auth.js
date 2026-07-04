import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('user')) || null,
    token: localStorage.getItem('token') || null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    isAdmin: (state) => state.user?.is_admin === true,
  },

  actions: {
    setAuth(user, token) {
      this.user = user;
      this.token = token;
      localStorage.setItem('user', JSON.stringify(user));
      localStorage.setItem('token', token);
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    },

    updateUser(user) {
      this.user = user;
      localStorage.setItem('user', JSON.stringify(user));
    },

    updateCoins(coins) {
      if (this.user) {
        this.user.coins = coins;
        localStorage.setItem('user', JSON.stringify(this.user));
      }
    },

    logout() {
      this.user = null;
      this.token = null;
      localStorage.removeItem('user');
      localStorage.removeItem('token');
      delete axios.defaults.headers.common['Authorization'];
    },

    async fetchUser() {
      try {
        const response = await axios.get('/user');
        this.updateUser(response.data.user);
        return response.data.user;
      } catch (error) {
        this.logout();
        throw error;
      }
    },
  },
});
