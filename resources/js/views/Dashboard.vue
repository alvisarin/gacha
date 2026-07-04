<template>
  <div class="max-w-6xl mx-auto px-4 py-8">
    <!-- Gacha Section -->
    <div class="card mb-8 bg-gradient-to-br from-purple-900 to-indigo-900 text-white">
      <div class="text-center">
        <h2 class="text-3xl font-bold mb-6">🎰 Roll the Gacha!</h2>

        <!-- Event Selection -->
        <div class="max-w-md mx-auto mb-6">
          <select
            v-model="selectedEventId"
            class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white focus:ring-2 focus:ring-purple-400"
            :disabled="isRolling"
          >
            <option value="" disabled>Select an Event</option>
            <option
              v-for="event in events"
              :key="event.id"
              :value="event.id"
              class="text-gray-900"
            >
              {{ event.title }}
            </option>
          </select>
        </div>

        <!-- Roll Button -->
        <button
          @click="rollGacha"
          :disabled="isRolling || !selectedEventId || authStore.user?.coins < 10"
          class="gacha-btn"
        >
          <span v-if="isRolling" class="flex items-center justify-center">
            <svg class="animate-spin h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            Rolling...
          </span>
          <span v-else>🎲 Roll (10 Coins)</span>
        </button>

        <p v-if="authStore.user?.coins < 10" class="mt-4 text-red-300">
          Not enough coins! You need at least 10 coins to roll.
        </p>
      </div>
    </div>

    <!-- Reward Modal -->
    <Teleport to="body">
      <div
        v-if="showReward"
        class="fixed inset-0 bg-black/70 flex items-center justify-center z-50"
        @click="showReward = false"
      >
        <div
          class="bg-gradient-to-br from-purple-900 to-indigo-900 p-8 rounded-2xl shadow-2xl text-center animate-bounce-in max-w-md mx-4"
          @click.stop
        >
          <div class="text-6xl mb-4">
            {{ getRewardEmoji(lastReward?.drop_rate) }}
          </div>
          <h3 class="text-3xl font-bold text-white mb-2">Congratulations!</h3>
          <p class="text-xl text-purple-200 mb-4">You got:</p>
          <div
            :class="getRewardClass(lastReward?.drop_rate)"
            class="px-6 py-4 rounded-xl text-xl font-bold mb-4"
          >
            {{ lastReward?.name }}
          </div>
          <p class="text-purple-300 text-sm">
            Drop Rate: {{ lastReward?.drop_rate }}%
          </p>
          <button
            @click="showReward = false"
            class="mt-6 btn btn-primary"
          >
            Close
          </button>
        </div>
      </div>
    </Teleport>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="card text-center">
        <div class="text-4xl mb-2">🪙</div>
        <div class="text-3xl font-bold text-gray-900">{{ authStore.user?.coins || 0 }}</div>
        <div class="text-gray-500">Current Coins</div>
      </div>
      <div class="card text-center">
        <div class="text-4xl mb-2">🎰</div>
        <div class="text-3xl font-bold text-gray-900">{{ stats.total_rolls || 0 }}</div>
        <div class="text-gray-500">Total Rolls</div>
      </div>
      <div class="card text-center">
        <div class="text-4xl mb-2">💎</div>
        <div class="text-3xl font-bold text-gray-900">{{ stats.total_coins_spent || 0 }}</div>
        <div class="text-gray-500">Coins Spent</div>
      </div>
    </div>

    <!-- Gacha History -->
    <div class="card">
      <h3 class="text-xl font-bold text-gray-900 mb-4">📜 Your Gacha History</h3>

      <div v-if="loading" class="text-center py-8">
        <div class="animate-spin h-8 w-8 border-4 border-purple-500 border-t-transparent rounded-full mx-auto"></div>
      </div>

      <div v-else-if="history.length === 0" class="text-center py-8 text-gray-500">
        No gacha rolls yet. Try your luck!
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="border-b border-gray-200">
              <th class="text-left py-3 px-4 font-semibold text-gray-600">Date</th>
              <th class="text-left py-3 px-4 font-semibold text-gray-600">Event</th>
              <th class="text-left py-3 px-4 font-semibold text-gray-600">Reward</th>
              <th class="text-right py-3 px-4 font-semibold text-gray-600">Drop Rate</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="log in history"
              :key="log.id"
              class="border-b border-gray-100 hover:bg-gray-50"
            >
              <td class="py-3 px-4 text-gray-600">
                {{ formatDate(log.created_at) }}
              </td>
              <td class="py-3 px-4 text-gray-900">
                {{ log.event?.title }}
              </td>
              <td class="py-3 px-4">
                <span
                  :class="getRewardBadgeClass(log.reward?.drop_rate)"
                  class="px-3 py-1 rounded-full text-sm font-medium"
                >
                  {{ log.reward?.name }}
                </span>
              </td>
              <td class="py-3 px-4 text-right text-gray-600">
                {{ log.reward?.drop_rate }}%
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="flex justify-center mt-6 space-x-2">
        <button
          @click="changePage(pagination.current_page - 1)"
          :disabled="pagination.current_page === 1"
          class="btn btn-secondary"
        >
          Previous
        </button>
        <span class="px-4 py-2 text-gray-600">
          Page {{ pagination.current_page }} of {{ pagination.last_page }}
        </span>
        <button
          @click="changePage(pagination.current_page + 1)"
          :disabled="pagination.current_page === pagination.last_page"
          class="btn btn-secondary"
        >
          Next
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import axios from 'axios';

const authStore = useAuthStore();

const events = ref([]);
const selectedEventId = ref('');
const isRolling = ref(false);
const showReward = ref(false);
const lastReward = ref(null);
const loading = ref(true);
const history = ref([]);
const stats = reactive({
  total_rolls: 0,
  total_coins_spent: 0,
});
const pagination = reactive({
  current_page: 1,
  last_page: 1,
});

const fetchEvents = async () => {
  try {
    const response = await axios.get('/gacha/events');
    events.value = response.data.events;
    if (events.value.length > 0) {
      selectedEventId.value = events.value[0].id;
    }
  } catch (e) {
    console.error('Failed to fetch events:', e);
  }
};

const fetchHistory = async (page = 1) => {
  loading.value = true;
  try {
    const response = await axios.get('/gacha/history', { params: { page } });
    history.value = response.data.data;
    pagination.current_page = response.data.current_page;
    pagination.last_page = response.data.last_page;
  } catch (e) {
    console.error('Failed to fetch history:', e);
  } finally {
    loading.value = false;
  }
};

const fetchStats = async () => {
  try {
    const response = await axios.get('/gacha/stats');
    stats.total_rolls = response.data.total_rolls;
    stats.total_coins_spent = response.data.total_coins_spent;
    authStore.updateCoins(response.data.current_coins);
  } catch (e) {
    console.error('Failed to fetch stats:', e);
  }
};

const rollGacha = async () => {
  if (isRolling.value || !selectedEventId.value) return;

  isRolling.value = true;

  try {
    const response = await axios.post('/gacha/roll', {
      event_id: selectedEventId.value,
    });

    lastReward.value = response.data.reward;
    authStore.updateCoins(response.data.remaining_coins);
    showReward.value = true;

    await Promise.all([fetchHistory(), fetchStats()]);
  } catch (e) {
    alert(e.response?.data?.message || 'Failed to roll gacha');
  } finally {
    isRolling.value = false;
  }
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.last_page) {
    fetchHistory(page);
  }
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleString();
};

const getRewardEmoji = (dropRate) => {
  if (dropRate <= 1) return '🌟';
  if (dropRate <= 10) return '💎';
  if (dropRate <= 30) return '✨';
  return '📦';
};

const getRewardClass = (dropRate) => {
  if (dropRate <= 1) return 'reward-legendary';
  if (dropRate <= 20) return 'reward-rare';
  return 'reward-common';
};

const getRewardBadgeClass = (dropRate) => {
  if (dropRate <= 1) return 'bg-yellow-100 text-yellow-800';
  if (dropRate <= 20) return 'bg-purple-100 text-purple-800';
  return 'bg-gray-100 text-gray-800';
};

onMounted(() => {
  fetchEvents();
  fetchHistory();
  fetchStats();
});
</script>
