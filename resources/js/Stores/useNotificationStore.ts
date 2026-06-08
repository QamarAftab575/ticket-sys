import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useNotificationStore = defineStore('notifications', () => {
    const unreadCount = ref(0);

    function setUnreadCount(count: number) {
        unreadCount.value = count;
    }

    function increment() {
        unreadCount.value += 1;
    }

    function decrement() {
        unreadCount.value = Math.max(0, unreadCount.value - 1);
    }

    return { unreadCount, setUnreadCount, increment, decrement };
});
