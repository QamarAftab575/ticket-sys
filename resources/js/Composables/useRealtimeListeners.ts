/**
 * useRealtimeListeners
 *
 * Subscribes to project.{id} and user.{id} channels via Echo and routes
 * incoming events into either:
 *   - custom inline handlers (for pages with their own local state), or
 *   - Pinia stores (for shared state like MyTasks / notification badge).
 *
 * Usage on a project page (local state):
 *   const { listenToProject } = useRealtimeListeners()
 *   listenToProject(projectId, {
 *     onTaskCreated({ task }) { ... },
 *     onTaskUpdated({ task }) { ... },
 *     onTaskMoved({ task })   { ... },
 *   })
 *
 * Usage for global listeners (AppLayout):
 *   const { listenToUser } = useRealtimeListeners()
 *   listenToUser(userId)
 */
import { useEcho } from '@/Composables/useEcho';
import { useMyTasksStore } from '@/Stores/useMyTasksStore';
import { useNotificationStore } from '@/Stores/useNotificationStore';

type ProjectHandlers = {
    onTaskCreated?: (data: any) => void;
    onTaskUpdated?: (data: any) => void;
    onTaskMoved?: (data: any) => void;
    onCommentCreated?: (data: any) => void;
};

export function useRealtimeListeners() {
    const { joinProject, joinUser } = useEcho();

    /**
     * Subscribe to a project channel.
     * Pass custom handlers to update local page state directly.
     * Call this inside onMounted of a project page.
     */
    function listenToProject(projectId: string, handlers: ProjectHandlers = {}) {
        // reverb functionality disabled
        /*
        joinProject(projectId, {
            onTaskCreated: handlers.onTaskCreated ?? (() => {}),
            onTaskUpdated: handlers.onTaskUpdated ?? (() => {}),
            onTaskMoved:   handlers.onTaskMoved   ?? (() => {}),
            onCommentCreated: handlers.onCommentCreated ?? (() => {}),
        });
        */
    }

    /**
     * Subscribe to the current user's private channel.
     * Updates MyTasks store + notification badge store.
     * Call this once in AppLayout (always mounted).
     */
    function listenToUser(userId: string) {
        const myTasksStore      = useMyTasksStore();
        const notificationStore = useNotificationStore();

        // reverb functionality disabled
        /*
        joinUser(userId, {
            // Tasks assigned to this user come through the user channel
            onTaskCreated({ task }) {
                myTasksStore.addTaskFromEvent(task);
            },
            onTaskUpdated({ task }) {
                myTasksStore.patchTask(task);
            },
            onTaskMoved({ task }) {
                myTasksStore.patchTaskMove(task);
            },
            onNotificationCreated({ unread_count }) {
                notificationStore.setUnreadCount(unread_count);
            },
        });
        */
    }

    return { listenToProject, listenToUser };
}
