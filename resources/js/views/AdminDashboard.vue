<template>
  <div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-white mb-8">⚙️ Admin Dashboard</h1>

    <!-- Tabs -->
    <div class="flex space-x-4 mb-6">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        @click="activeTab = tab.id"
        :class="[
          'px-6 py-3 rounded-lg font-semibold transition-all',
          activeTab === tab.id
            ? 'bg-purple-600 text-white'
            : 'bg-gray-700 text-gray-300 hover:bg-gray-600'
        ]"
      >
        {{ tab.icon }} {{ tab.label }}
      </button>
    </div>

    <!-- Events Tab -->
    <div v-if="activeTab === 'events'" class="card">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-gray-900">Events Management</h2>
        <button @click="openEventModal()" class="btn btn-primary">
          + Create Event
        </button>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="border-b border-gray-200">
              <th class="text-left py-3 px-4 font-semibold text-gray-600">Title</th>
              <th class="text-left py-3 px-4 font-semibold text-gray-600">Status</th>
              <th class="text-left py-3 px-4 font-semibold text-gray-600">Rewards</th>
              <th class="text-left py-3 px-4 font-semibold text-gray-600">Drop Rate</th>
              <th class="text-right py-3 px-4 font-semibold text-gray-600">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="event in events"
              :key="event.id"
              class="border-b border-gray-100 hover:bg-gray-50"
            >
              <td class="py-3 px-4 font-medium text-gray-900">{{ event.title }}</td>
              <td class="py-3 px-4">
                <span
                  :class="event.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                  class="px-3 py-1 rounded-full text-sm font-medium"
                >
                  {{ event.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="py-3 px-4 text-gray-600">{{ event.rewards?.length || 0 }}</td>
              <td class="py-3 px-4">
                <span
                  :class="getTotalDropRateClass(event)"
                  class="px-3 py-1 rounded-full text-sm font-medium"
                >
                  {{ calculateTotalDropRate(event) }}%
                </span>
              </td>
              <td class="py-3 px-4 text-right space-x-2">
                <button
                  @click="selectEvent(event)"
                  class="text-blue-600 hover:text-blue-800 font-medium"
                >
                  Rewards
                </button>
                <button
                  @click="openEventModal(event)"
                  class="text-purple-600 hover:text-purple-800 font-medium"
                >
                  Edit
                </button>
                <button
                  @click="deleteEvent(event)"
                  class="text-red-600 hover:text-red-800 font-medium"
                >
                  Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Rewards Tab -->
    <div v-if="activeTab === 'rewards'" class="card">
      <div class="flex justify-between items-center mb-6">
        <div>
          <h2 class="text-xl font-bold text-gray-900">Rewards Management</h2>
          <p v-if="selectedEvent" class="text-gray-500">
            Event: {{ selectedEvent.title }}
            (Total: {{ calculateTotalDropRate(selectedEvent) }}%)
          </p>
        </div>
        <div class="flex space-x-3">
          <select
            v-model="selectedEventId"
            @change="loadRewards"
            class="input max-w-xs"
          >
            <option value="">Select Event</option>
            <option v-for="event in events" :key="event.id" :value="event.id">
              {{ event.title }}
            </option>
          </select>
          <button
            @click="openRewardModal()"
            :disabled="!selectedEventId"
            class="btn btn-primary"
          >
            + Add Reward
          </button>
        </div>
      </div>

      <div v-if="!selectedEventId" class="text-center py-12 text-gray-500">
        Select an event to manage rewards
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="border-b border-gray-200">
              <th class="text-left py-3 px-4 font-semibold text-gray-600">Name</th>
              <th class="text-left py-3 px-4 font-semibold text-gray-600">Drop Rate</th>
              <th class="text-left py-3 px-4 font-semibold text-gray-600">Rarity</th>
              <th class="text-right py-3 px-4 font-semibold text-gray-600">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="reward in rewards"
              :key="reward.id"
              class="border-b border-gray-100 hover:bg-gray-50"
            >
              <td class="py-3 px-4 font-medium text-gray-900">{{ reward.name }}</td>
              <td class="py-3 px-4 text-gray-600">{{ reward.drop_rate }}%</td>
              <td class="py-3 px-4">
                <span :class="getRarityClass(reward.drop_rate)" class="px-3 py-1 rounded-full text-sm font-medium">
                  {{ getRarityLabel(reward.drop_rate) }}
                </span>
              </td>
              <td class="py-3 px-4 text-right space-x-2">
                <button
                  @click="openRewardModal(reward)"
                  class="text-purple-600 hover:text-purple-800 font-medium"
                >
                  Edit
                </button>
                <button
                  @click="deleteReward(reward)"
                  class="text-red-600 hover:text-red-800 font-medium"
                >
                  Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Gacha Logs Tab -->
    <div v-if="activeTab === 'logs'" class="card">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-gray-900">Global Gacha Logs</h2>
        <button @click="fetchLogs" class="btn btn-secondary">
          🔄 Refresh
        </button>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="border-b border-gray-200">
              <th class="text-left py-3 px-4 font-semibold text-gray-600">Date</th>
              <th class="text-left py-3 px-4 font-semibold text-gray-600">User</th>
              <th class="text-left py-3 px-4 font-semibold text-gray-600">Event</th>
              <th class="text-left py-3 px-4 font-semibold text-gray-600">Reward</th>
              <th class="text-right py-3 px-4 font-semibold text-gray-600">Coins</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="log in logs"
              :key="log.id"
              class="border-b border-gray-100 hover:bg-gray-50"
            >
              <td class="py-3 px-4 text-gray-600">{{ formatDate(log.created_at) }}</td>
              <td class="py-3 px-4 text-gray-900">{{ log.user?.name }}</td>
              <td class="py-3 px-4 text-gray-600">{{ log.event?.title }}</td>
              <td class="py-3 px-4">
                <span :class="getRarityClass(log.reward?.drop_rate)" class="px-3 py-1 rounded-full text-sm font-medium">
                  {{ log.reward?.name }}
                </span>
              </td>
              <td class="py-3 px-4 text-right text-gray-600">{{ log.coins_spent }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="logsPagination.last_page > 1" class="flex justify-center mt-6 space-x-2">
        <button
          @click="changeLogsPage(logsPagination.current_page - 1)"
          :disabled="logsPagination.current_page === 1"
          class="btn btn-secondary"
        >
          Previous
        </button>
        <span class="px-4 py-2 text-gray-600">
          Page {{ logsPagination.current_page }} of {{ logsPagination.last_page }}
        </span>
        <button
          @click="changeLogsPage(logsPagination.current_page + 1)"
          :disabled="logsPagination.current_page === logsPagination.last_page"
          class="btn btn-secondary"
        >
          Next
        </button>
      </div>
    </div>

    <!-- Event Modal -->
    <Teleport to="body">
      <div v-if="showEventModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md mx-4">
          <h3 class="text-xl font-bold text-gray-900 mb-4">
            {{ editingEvent ? 'Edit Event' : 'Create Event' }}
          </h3>
          <form @submit.prevent="saveEvent">
            <div class="mb-4">
              <label class="label">Title</label>
              <input v-model="eventForm.title" type="text" required class="input" />
            </div>
            <div class="mb-6">
              <label class="flex items-center">
                <input v-model="eventForm.is_active" type="checkbox" class="mr-2" />
                <span class="text-gray-700">Active</span>
              </label>
            </div>
            <div class="flex justify-end space-x-3">
              <button type="button" @click="showEventModal = false" class="btn btn-secondary">
                Cancel
              </button>
              <button type="submit" :disabled="savingEvent" class="btn btn-primary">
                {{ savingEvent ? 'Saving...' : 'Save' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- Reward Modal -->
    <Teleport to="body">
      <div v-if="showRewardModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md mx-4">
          <h3 class="text-xl font-bold text-gray-900 mb-4">
            {{ editingReward ? 'Edit Reward' : 'Add Reward' }}
          </h3>
          <form @submit.prevent="saveReward">
            <div class="mb-4">
              <label class="label">Name</label>
              <input v-model="rewardForm.name" type="text" required class="input" />
            </div>
            <div class="mb-4">
              <label class="label">Drop Rate (%)</label>
              <input
                v-model.number="rewardForm.drop_rate"
                type="number"
                step="0.01"
                min="0.01"
                max="100"
                required
                class="input"
              />
              <p class="text-sm text-gray-500 mt-1">
                Remaining: {{ remainingDropRate.toFixed(2) }}%
              </p>
            </div>
            <div class="flex justify-end space-x-3">
              <button type="button" @click="showRewardModal = false" class="btn btn-secondary">
                Cancel
              </button>
              <button type="submit" :disabled="savingReward" class="btn btn-primary">
                {{ savingReward ? 'Saving...' : 'Save' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import axios from 'axios';

const tabs = [
  { id: 'events', label: 'Events', icon: '📅' },
  { id: 'rewards', label: 'Rewards', icon: '🎁' },
  { id: 'logs', label: 'Gacha Logs', icon: '📊' },
];

const activeTab = ref('events');

// Events
const events = ref([]);
const showEventModal = ref(false);
const editingEvent = ref(null);
const savingEvent = ref(false);
const eventForm = reactive({ title: '', is_active: true });

// Rewards
const selectedEventId = ref('');
const selectedEvent = computed(() => events.value.find(e => e.id === selectedEventId.value));
const rewards = ref([]);
const showRewardModal = ref(false);
const editingReward = ref(null);
const savingReward = ref(false);
const rewardForm = reactive({ name: '', drop_rate: 0 });

const remainingDropRate = computed(() => {
  const currentTotal = rewards.value.reduce((sum, r) => {
    if (editingReward.value && r.id === editingReward.value.id) return sum;
    return sum + parseFloat(r.drop_rate);
  }, 0);
  return 100 - currentTotal;
});

// Logs
const logs = ref([]);
const logsPagination = reactive({ current_page: 1, last_page: 1 });

// Fetch Events
const fetchEvents = async () => {
  try {
    const response = await axios.get('/admin/events');
    events.value = response.data.data;
  } catch (e) {
    console.error('Failed to fetch events:', e);
  }
};

// Event CRUD
const openEventModal = (event = null) => {
  editingEvent.value = event;
  eventForm.title = event?.title || '';
  eventForm.is_active = event?.is_active ?? true;
  showEventModal.value = true;
};

const saveEvent = async () => {
  savingEvent.value = true;
  try {
    if (editingEvent.value) {
      await axios.put(`/admin/events/${editingEvent.value.id}`, eventForm);
    } else {
      await axios.post('/admin/events', eventForm);
    }
    await fetchEvents();
    showEventModal.value = false;
  } catch (e) {
    alert(e.response?.data?.message || 'Failed to save event');
  } finally {
    savingEvent.value = false;
  }
};

const deleteEvent = async (event) => {
  if (!confirm(`Delete event "${event.title}"? This will also delete all rewards.`)) return;
  try {
    await axios.delete(`/admin/events/${event.id}`);
    await fetchEvents();
    if (selectedEventId.value === event.id) {
      selectedEventId.value = '';
      rewards.value = [];
    }
  } catch (e) {
    alert(e.response?.data?.message || 'Failed to delete event');
  }
};

const selectEvent = (event) => {
  selectedEventId.value = event.id;
  activeTab.value = 'rewards';
  loadRewards();
};

// Rewards
const loadRewards = async () => {
  if (!selectedEventId.value) {
    rewards.value = [];
    return;
  }
  try {
    const response = await axios.get(`/admin/events/${selectedEventId.value}/rewards`);
    rewards.value = response.data.rewards;
  } catch (e) {
    console.error('Failed to load rewards:', e);
  }
};

const openRewardModal = (reward = null) => {
  editingReward.value = reward;
  rewardForm.name = reward?.name || '';
  rewardForm.drop_rate = reward?.drop_rate || 0;
  showRewardModal.value = true;
};

const saveReward = async () => {
  savingReward.value = true;
  try {
    if (editingReward.value) {
      await axios.put(
        `/admin/events/${selectedEventId.value}/rewards/${editingReward.value.id}`,
        rewardForm
      );
    } else {
      await axios.post(`/admin/events/${selectedEventId.value}/rewards`, rewardForm);
    }
    await loadRewards();
    await fetchEvents();
    showRewardModal.value = false;
  } catch (e) {
    alert(e.response?.data?.message || e.response?.data?.errors?.drop_rate?.[0] || 'Failed to save reward');
  } finally {
    savingReward.value = false;
  }
};

const deleteReward = async (reward) => {
  if (!confirm(`Delete reward "${reward.name}"?`)) return;
  try {
    await axios.delete(`/admin/events/${selectedEventId.value}/rewards/${reward.id}`);
    await loadRewards();
    await fetchEvents();
  } catch (e) {
    alert(e.response?.data?.message || 'Failed to delete reward');
  }
};

// Logs
const fetchLogs = async (page = 1) => {
  try {
    const response = await axios.get('/admin/gacha-logs', { params: { page, per_page: 20 } });
    logs.value = response.data.data;
    logsPagination.current_page = response.data.current_page;
    logsPagination.last_page = response.data.last_page;
  } catch (e) {
    console.error('Failed to fetch logs:', e);
  }
};

const changeLogsPage = (page) => {
  if (page >= 1 && page <= logsPagination.last_page) {
    fetchLogs(page);
  }
};

// Helpers
const formatDate = (dateString) => new Date(dateString).toLocaleString();

const calculateTotalDropRate = (event) => {
  if (!event?.rewards) return 0;
  return event.rewards.reduce((sum, r) => sum + parseFloat(r.drop_rate), 0).toFixed(2);
};

const getTotalDropRateClass = (event) => {
  const total = parseFloat(calculateTotalDropRate(event));
  if (Math.abs(total - 100) < 0.01) return 'bg-green-100 text-green-800';
  return 'bg-red-100 text-red-800';
};

const getRarityClass = (dropRate) => {
  if (dropRate <= 1) return 'bg-yellow-100 text-yellow-800';
  if (dropRate <= 20) return 'bg-purple-100 text-purple-800';
  return 'bg-gray-100 text-gray-800';
};

const getRarityLabel = (dropRate) => {
  if (dropRate <= 1) return 'Legendary';
  if (dropRate <= 5) return 'Epic';
  if (dropRate <= 20) return 'Rare';
  return 'Common';
};

onMounted(() => {
  fetchEvents();
  fetchLogs();
});
</script>
