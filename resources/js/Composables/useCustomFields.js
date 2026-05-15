import { ref } from 'vue'

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content

/**
 * Composable for managing project custom fields and task field values.
 * Shared state per project via a module-level cache.
 */
const cache = {}

export function useCustomFields(projectId) {
    if (!cache[projectId]) {
        cache[projectId] = {
            fields: ref([]),
            loading: ref(false),
            fetched: ref(false),
        }
    }

    const { fields, loading, fetched } = cache[projectId]

    async function fetchFields() {
        if (fetched.value) return
        loading.value = true
        try {
            const res = await fetch(`/api/projects/${projectId}/custom-fields`)
            if (!res.ok) throw new Error('Failed to fetch custom fields')
            const data = await res.json()
            fields.value = data.data ?? []
            fetched.value = true
        } catch (e) {
            console.error(e)
        } finally {
            loading.value = false
        }
    }

    async function createField(payload) {
        const res = await fetch(`/api/projects/${projectId}/custom-fields`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify(payload),
        })
        if (!res.ok) {
            const err = await res.json()
            throw err
        }
        const data = await res.json()
        fields.value.push(data.data)
        return data.data
    }

    async function updateField(fieldId, payload) {
        const res = await fetch(`/api/custom-fields/${fieldId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify(payload),
        })
        if (!res.ok) {
            const err = await res.json()
            throw err
        }
        const data = await res.json()
        const idx = fields.value.findIndex(f => f.id === fieldId)
        if (idx !== -1) fields.value[idx] = data.data
        return data.data
    }

    async function toggleFieldActive(fieldId) {
        // Optimistic
        const idx = fields.value.findIndex(f => f.id === fieldId)
        if (idx !== -1) fields.value[idx] = { ...fields.value[idx], is_active: !fields.value[idx].is_active }

        const res = await fetch(`/api/custom-fields/${fieldId}/toggle-active`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken() },
        })
        if (!res.ok) {
            // Revert
            if (idx !== -1) fields.value[idx] = { ...fields.value[idx], is_active: !fields.value[idx].is_active }
            return
        }
        const data = await res.json()
        if (idx !== -1) fields.value[idx] = data.data
        return data.data
    }

    async function deleteField(fieldId) {
        const res = await fetch(`/api/custom-fields/${fieldId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken() },
        })
        if (!res.ok) throw new Error('Failed to delete field')
        fields.value = fields.value.filter(f => f.id !== fieldId)
    }

    async function setTaskFieldValue(taskId, fieldId, value) {
        const res = await fetch(`/api/tasks/${taskId}/custom-fields/${fieldId}/value`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify({ value }),
        })
        if (!res.ok) {
            const err = await res.json()
            throw err
        }
        return (await res.json()).data
    }

    /** Get active fields only (for column display) */
    const activeFields = () => fields.value.filter(f => f.is_active)

    return {
        fields,
        loading,
        fetchFields,
        createField,
        updateField,
        toggleFieldActive,
        deleteField,
        setTaskFieldValue,
        activeFields,
    }
}
