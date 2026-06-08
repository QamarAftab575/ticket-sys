/**
 * useEcho — thin wrapper around window.Echo for type-safe channel subscriptions.
 * Tracks subscribed channels so callers can leave them on unmount.
 */
import { onUnmounted } from 'vue';

// Extend the global Window type so TypeScript doesn't complain
declare global {
    interface Window {
        Echo: any;
        Pusher: any;
    }
}

type EchoChannel = {
    listen: (event: string, callback: (data: any) => void) => EchoChannel;
    stopListening: (event: string) => EchoChannel;
};

const subscribedChannels: Map<string, EchoChannel> = new Map();

function privateChannel(name: string): EchoChannel {
    if (!window.Echo) {
        console.warn('[useEcho] Echo is not initialised yet.');
        return { listen: () => ({ listen: () => ({ listen: () => ({} as any), stopListening: () => ({} as any) }), stopListening: () => ({} as any) }), stopListening: () => ({} as any) };
    }

    if (!subscribedChannels.has(name)) {
        subscribedChannels.set(name, window.Echo.private(name) as EchoChannel);
    }

    return subscribedChannels.get(name)!;
}

function leaveChannel(name: string) {
    if (window.Echo && subscribedChannels.has(name)) {
        window.Echo.leave(name);
        subscribedChannels.delete(name);
    }
}

/**
 * Composable — automatically leaves channels when the calling component unmounts.
 */
export function useEcho() {
    const channelsToLeave: string[] = [];

    function joinProject(projectId: string, handlers: {
        onTaskCreated?: (data: any) => void;
        onTaskUpdated?: (data: any) => void;
        onTaskMoved?: (data: any) => void;
        onCommentCreated?: (data: any) => void;
    }) {
        const channelName = `project.${projectId}`;
        channelsToLeave.push(channelName);
        const ch = privateChannel(channelName);

        if (handlers.onTaskCreated)   ch.listen('.TaskCreated',   handlers.onTaskCreated);
        if (handlers.onTaskUpdated)   ch.listen('.TaskUpdated',   handlers.onTaskUpdated);
        if (handlers.onTaskMoved)     ch.listen('.TaskMoved',     handlers.onTaskMoved);
        if (handlers.onCommentCreated) ch.listen('.CommentCreated', handlers.onCommentCreated);

        return ch;
    }

    function joinUser(userId: string, handlers: {
        onTaskCreated?: (data: any) => void;
        onTaskUpdated?: (data: any) => void;
        onTaskMoved?: (data: any) => void;
        onNotificationCreated?: (data: any) => void;
    }) {
        const channelName = `user.${userId}`;
        channelsToLeave.push(channelName);
        const ch = privateChannel(channelName);

        if (handlers.onTaskCreated)        ch.listen('.TaskCreated',        handlers.onTaskCreated);
        if (handlers.onTaskUpdated)        ch.listen('.TaskUpdated',        handlers.onTaskUpdated);
        if (handlers.onTaskMoved)          ch.listen('.TaskMoved',          handlers.onTaskMoved);
        if (handlers.onNotificationCreated) ch.listen('.NotificationCreated', handlers.onNotificationCreated);

        return ch;
    }

    onUnmounted(() => {
        channelsToLeave.forEach(leaveChannel);
    });

    return { joinProject, joinUser };
}
