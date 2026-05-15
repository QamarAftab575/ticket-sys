import { ref, computed } from 'vue'
import { useCustomFields } from './useCustomFields'

export function useListViewColumns(projectId) {
    const defaultColumns = [
        { id: 'name',     label: 'Task name', type: 'text', required: true },
        { id: 'assignee', label: 'Assignee',  type: 'user', required: false },
        { id: 'due_date', label: 'Due date',  type: 'date', required: false },
    ]

    const visibleColumns  = ref([...defaultColumns])
    const hiddenColumns   = ref([])
    const columnWidths    = ref({ name: '300px', assignee: '150px', due_date: '120px' })

    const { fields, fetchFields, createField } = useCustomFields(projectId)

    // ── persistence ──────────────────────────────────────────────────────────

    const _storageKey = () => `project_${projectId}_columns`

    const saveColumns = () => {
        localStorage.setItem(_storageKey(), JSON.stringify({
            visible: visibleColumns.value,
            hidden:  hiddenColumns.value,
            widths:  columnWidths.value,
        }))
    }

    const initializeColumns = async () => {
        await fetchFields()

        const stored = localStorage.getItem(_storageKey())
        if (stored) {
            try {
                const data = JSON.parse(stored)
                visibleColumns.value = data.visible || [...defaultColumns]
                hiddenColumns.value  = data.hidden  || []
                columnWidths.value   = data.widths  || columnWidths.value
            } catch {
                // ignore corrupt storage
            }
        }

        // Sync: add any active custom fields not yet in visible/hidden columns
        fields.value.filter(f => f.is_active).forEach(f => {
            const alreadyTracked =
                visibleColumns.value.find(c => c.id === f.id) ||
                hiddenColumns.value.find(c => c.id === f.id)
            if (!alreadyTracked) {
                visibleColumns.value.push(_fieldToColumn(f))
                columnWidths.value[f.id] = '150px'
            }
        })

        saveColumns()
    }

    // ── column helpers ────────────────────────────────────────────────────────

    const _fieldToColumn = (field) => ({
        id:         field.id,
        label:      field.name,
        type:       field.field_type,
        required:   false,
        isCustom:   true,
    })

    const addColumn = (field) => {
        if (visibleColumns.value.find(c => c.id === field.id)) return
        hiddenColumns.value = hiddenColumns.value.filter(c => c.id !== field.id)
        visibleColumns.value.push(field)
        columnWidths.value[field.id] = columnWidths.value[field.id] || '150px'
        saveColumns()
    }

    const hideColumn = (columnId) => {
        const col = visibleColumns.value.find(c => c.id === columnId)
        if (!col || col.required) return
        visibleColumns.value = visibleColumns.value.filter(c => c.id !== columnId)
        hiddenColumns.value.push(col)
        saveColumns()
    }

    const showColumn = (columnId) => {
        const col = hiddenColumns.value.find(c => c.id === columnId)
        if (!col) return
        hiddenColumns.value = hiddenColumns.value.filter(c => c.id !== columnId)
        visibleColumns.value.push(col)
        saveColumns()
    }

    const resizeColumn = (columnId, delta) => {
        const current = parseInt(columnWidths.value[columnId] || '150')
        columnWidths.value[columnId] = `${Math.max(80, current + delta)}px`
        saveColumns()
    }

    const reorderColumns = (fromIndex, toIndex) => {
        const col = visibleColumns.value.splice(fromIndex, 1)[0]
        visibleColumns.value.splice(toIndex, 0, col)
        saveColumns()
    }

    /**
     * Called when user creates a new field via AddFieldModal.
     * Persists to backend, then adds as a visible column.
     */
    const createCustomField = async (fieldData) => {
        const newField = await createField(fieldData)
        const col = _fieldToColumn(newField)
        addColumn(col)
        return newField
    }

    // ── computed ──────────────────────────────────────────────────────────────

    const allColumns = computed(() => [...visibleColumns.value, ...hiddenColumns.value])

    /** Fields that exist in the project but are not yet visible columns */
    const availableFields = computed(() => {
        return fields.value
            .filter(f => f.is_active)
            .filter(f => !visibleColumns.value.find(c => c.id === f.id))
            .map(_fieldToColumn)
    })

    return {
        visibleColumns,
        hiddenColumns,
        columnWidths,
        allColumns,
        availableFields,
        fields,
        initializeColumns,
        saveColumns,
        addColumn,
        hideColumn,
        showColumn,
        resizeColumn,
        reorderColumns,
        createCustomField,
    }
}
