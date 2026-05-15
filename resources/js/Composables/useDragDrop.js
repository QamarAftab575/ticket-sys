/**
 * useDragDrop — lightweight HTML5 drag & drop for sections and tasks.
 * No external library. Optimistic-first: state updates before API call.
 */
import { ref } from 'vue'

export function useDragDrop({ onTaskMove, onSectionReorder }) {
  // ── drag state ──────────────────────────────────────────────────────────
  const dragging = ref(null)          // { type: 'task'|'section', id, sectionId }
  const dragOverSection = ref(null)   // section id currently hovered
  const dragOverTask = ref(null)      // task id currently hovered (insert-before indicator)

  // ── task drag ────────────────────────────────────────────────────────────
  function taskDragStart(event, task) {
    dragging.value = { type: 'task', id: task.id, sectionId: task.section_id }
    event.dataTransfer.effectAllowed = 'move'
    event.dataTransfer.setData('text/plain', task.id)
    // ghost opacity via CSS class added in next tick
    requestAnimationFrame(() => event.target.classList.add('dragging'))
  }

  function taskDragEnd(event) {
    event.target.classList.remove('dragging')
    dragging.value = null
    dragOverSection.value = null
    dragOverTask.value = null
  }

  function taskDragOver(event, sectionId, taskId = null) {
    if (!dragging.value || dragging.value.type !== 'task') return
    event.preventDefault()
    event.dataTransfer.dropEffect = 'move'
    dragOverSection.value = sectionId
    dragOverTask.value = taskId
  }

  function taskDrop(event, sectionId, tasks, insertBeforeTaskId = null) {
    event.preventDefault()
    if (!dragging.value || dragging.value.type !== 'task') return

    const taskId = dragging.value.id
    const fromSectionId = dragging.value.sectionId

    // Compute target position
    let position = tasks.length
    if (insertBeforeTaskId) {
      const idx = tasks.findIndex(t => t.id === insertBeforeTaskId)
      position = idx >= 0 ? idx : tasks.length
    }

    dragging.value = null
    dragOverSection.value = null
    dragOverTask.value = null

    onTaskMove?.({ taskId, fromSectionId, toSectionId: sectionId, position })
  }

  // ── section drag ─────────────────────────────────────────────────────────
  const sectionDragOver = ref(null)   // section id drop target

  function sectionDragStart(event, section) {
    dragging.value = { type: 'section', id: section.id }
    event.dataTransfer.effectAllowed = 'move'
    event.dataTransfer.setData('text/plain', section.id)
    requestAnimationFrame(() => event.target.classList.add('dragging'))
  }

  function sectionDragEnd(event) {
    event.target.classList.remove('dragging')
    dragging.value = null
    sectionDragOver.value = null
  }

  function sectionDragOverHandler(event, sectionId) {
    if (!dragging.value || dragging.value.type !== 'section') return
    event.preventDefault()
    event.dataTransfer.dropEffect = 'move'
    sectionDragOver.value = sectionId
  }

  function sectionDrop(event, targetSectionId, sections) {
    event.preventDefault()
    if (!dragging.value || dragging.value.type !== 'section') return

    const fromId = dragging.value.id
    dragging.value = null
    sectionDragOver.value = null

    if (fromId === targetSectionId) return

    const ids = sections.map(s => s.id)
    const fromIdx = ids.indexOf(fromId)
    const toIdx = ids.indexOf(targetSectionId)
    if (fromIdx === -1 || toIdx === -1) return

    // Build reordered id list
    const reordered = [...ids]
    reordered.splice(fromIdx, 1)
    reordered.splice(toIdx, 0, fromId)

    onSectionReorder?.({ reorderedIds: reordered })
  }

  return {
    dragging,
    dragOverSection,
    dragOverTask,
    sectionDragOver,
    // task
    taskDragStart,
    taskDragEnd,
    taskDragOver,
    taskDrop,
    // section
    sectionDragStart,
    sectionDragEnd,
    sectionDragOverHandler,
    sectionDrop,
  }
}
