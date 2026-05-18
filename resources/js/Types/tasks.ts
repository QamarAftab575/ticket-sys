// Task Priority Levels
export type TaskPriority = 'low' | 'medium' | 'high' | 'urgent';

// Task Status
export type TaskStatus = 'to_do' | 'in_progress' | 'blocked' | 'in_review' | 'complete';

// Task Visibility
export type TaskVisibility = 'everyone' | 'only_me';

// Recurrence Pattern
export type RecurrencePattern = 'daily' | 'weekly' | 'monthly' | null;

// Custom Field Types
export type CustomFieldType = 'text' | 'number' | 'dropdown' | 'date' | 'checkbox';

// User (minimal)
export interface User {
  id: string;
  name: string;
  email: string;
  avatar?: string | null;
}

// Tag
export interface Tag {
  id: string;
  name: string;
  color: string;
  project_id: string;
}

// Custom Field
export interface CustomField {
  id: string;
  name: string;
  type: CustomFieldType;
  project_id: string;
  options?: string[]; // For dropdown type
}

// Custom Field Value
export interface CustomFieldValue {
  id: string;
  custom_field_id: string;
  task_id: string;
  value: string | number | boolean | null;
  custom_field?: CustomField;
}

// Attachment
export interface Attachment {
  id: string;
  task_id: string;
  file_name: string;
  file_path: string;
  file_size: number;
  mime_type: string;
  uploaded_by: User;
  created_at: string;
}

// Comment Reaction
export interface CommentReaction {
  id: string;
  comment_id: string;
  user_id: string;
  emoji: string;
  user?: User;
}

// Comment
export interface Comment {
  id: string;
  task_id: string;
  user_id: string;
  content: string;
  edited_at?: string | null;
  created_at: string;
  updated_at: string;
  user?: User;
  reactions?: CommentReaction[];
  attachments?: Attachment[];
}

// Task Activity
export interface TaskActivity {
  id: string;
  task_id: string;
  user_id: string;
  action: string;
  old_value?: string | null;
  new_value?: string | null;
  created_at: string;
  user?: User;
}

// Task Dependency
export interface TaskDependency {
  id: string;
  name: string;
  status: string;
  completed_at?: string | null;
  due_date?: string | null;
  start_date?: string | null;
  assignee?: { id: string; name: string; avatar?: string | null } | null;
  pivot?: {
    dependency_type: 'blocked_by' | 'blocks';
  };
}

// Subtask (same structure as Task but nested)
export interface Subtask {
  id: string;
  name: string;
  description?: string;
  completed: boolean;
  completed_at?: string;
  assignee_id?: string;
  due_date?: string;
  priority: TaskPriority;
  assignee?: User;
  subtasks?: Subtask[];
}

// Main Task
export interface Task {
  id: string;
  project_id: string;
  section_id?: string;
  name: string;
  description?: string;
  status: TaskStatus;
  priority: TaskPriority;
  visibility: TaskVisibility;
  completed: boolean;
  completed_at?: string;
  completed_by_id?: string;
  assignee_id?: string;
  due_date?: string;
  start_date?: string;
  is_milestone: boolean;
  recurrence_pattern?: RecurrencePattern;
  created_at: string;
  updated_at: string;
  
  // Relations
  assignee?: User;
  creator?: User;
  completedBy?: User;
  project?: {
    id: string;
    name: string;
    color?: string;
    icon?: string;
  };
  section?: {
    id: string;
    name: string;
  };
  tags?: Tag[];
  subtasks?: Subtask[];
  dependencies?: TaskDependency[];
  dependents?: TaskDependency[];
  customFieldValues?: CustomFieldValue[];
  comments?: Comment[];
  attachments?: Attachment[];
  activities?: TaskActivity[];
}

// Task Detail (full task with all relations)
export interface TaskDetail extends Task {
  comments: Comment[];
  attachments: Attachment[];
  activities: TaskActivity[];
  customFieldValues: CustomFieldValue[];
  dependencies: TaskDependency[];
  dependents: TaskDependency[];
  subtasks: Subtask[];
}

// Filter Options
export interface TaskFilter {
  assigneeIds?: string[];
  dueDateOption?: 'today' | 'tomorrow' | 'this_week' | 'overdue' | 'no_date';
  priorities?: TaskPriority[];
  statuses?: TaskStatus[];
  tagIds?: string[];
  customFieldFilters?: Record<string, string | number | boolean>;
  projectIds?: string[];
  searchQuery?: string;
}

// Sort Options
export interface TaskSort {
  field: 'name' | 'due_date' | 'priority' | 'created_at' | 'assignee' | 'status' | 'project_name';
  direction: 'asc' | 'desc';
}

// Grouping Options
export type TaskGroupBy = 'assignee' | 'due_date' | 'priority' | 'section' | 'project' | 'status' | '' | null;

// Column Configuration
export interface TaskColumn {
  id: string;
  label: string;
  visible: boolean;
  width?: number;
  sortable: boolean;
}

// Task List State
export interface TaskListState {
  tasks: Task[];
  selectedTaskId?: string;
  filters: TaskFilter;
  sort: TaskSort[];
  groupBy: TaskGroupBy;
  columns: TaskColumn[];
  loading: boolean;
  error?: string;
}

// Task Form Data
export interface TaskFormData {
  name: string;
  description?: string;
  priority: TaskPriority;
  visibility: TaskVisibility;
  assignee_id?: string;
  due_date?: string;
  start_date?: string;
  section_id?: string;
  tag_ids?: string[];
  is_milestone?: boolean;
  recurrence_pattern?: RecurrencePattern;
  custom_fields?: Record<string, string | number | boolean>;
}

// API Response
export interface ApiResponse<T> {
  data: T;
  message?: string;
}

export interface ApiListResponse<T> {
  data: T[];
  meta?: {
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
  };
}

// Pagination
export interface Pagination {
  total: number;
  perPage: number;
  currentPage: number;
  lastPage: number;
}
