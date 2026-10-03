<template>
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
        <Dialog v-model:visible="projectModal" modal :style="{ width: '40rem' }"
            :breakpoints="{ '1199px': '75vw', '575px': '95vw' }"
            :pt="{ header: { class: 'border-b border-slate-100 pb-4' } }">
            <template #header>
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-linear-to-br from-indigo-500 to-blue-600 flex items-center justify-center shadow-sm">
                        <i class="pi pi-folder text-white text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-slate-800">{{ isUpdate ? 'Edit Project' : 'New Project' }}</h2>
                        <p class="text-xs text-slate-400 mt-0.5">{{ isUpdate ? 'Update the project details' : 'Fill in the project details below' }}</p>
                    </div>
                </div>
            </template>
            <form @submit.prevent="save" class="flex flex-col gap-5 pt-2" novalidate>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-4">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-slate-700">
                            Client Name <span class="text-red-400">*</span>
                        </label>
                        <InputText v-model="projectInfo.client_name" placeholder="Client name" fluid :invalid="!!errors.client_name" class="text-sm" />
                        <small v-if="errors.client_name" class="text-xs text-red-500">{{ errors.client_name }}</small>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-slate-700">
                            Project Name <span class="text-red-400">*</span>
                        </label>
                        <InputText v-model="projectInfo.project_name" placeholder="Project name" fluid :invalid="!!errors.project_name" class="text-sm" />
                        <small v-if="errors.project_name" class="text-xs text-red-500">{{ errors.project_name }}</small>
                    </div>
                    <div class="sm:col-span-2 flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-slate-700">Description</label>
                        <Textarea v-model="projectInfo.description" placeholder="Short description" rows="3" autoResize fluid :invalid="!!errors.description" class="text-sm" />
                        <small v-if="errors.description" class="text-xs text-red-500">{{ errors.description }}</small>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-slate-700">
                            Status <span class="text-red-400">*</span>
                        </label>
                        <Select v-model="projectInfo.status" :options="statuses" placeholder="Select status" fluid :invalid="!!errors.status" class="text-sm" />
                        <small v-if="errors.status" class="text-xs text-red-500">{{ errors.status }}</small>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-slate-700">
                            Priority <span class="text-red-400">*</span>
                        </label>
                        <Select v-model="projectInfo.priority" :options="priorities" placeholder="Select priority" fluid :invalid="!!errors.priority" class="text-sm" />
                        <small v-if="errors.priority" class="text-xs text-red-500">{{ errors.priority }}</small>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-slate-700">Start Date</label>
                        <DatePicker v-model="startDate" dateFormat="yy-mm-dd" placeholder="YYYY-MM-DD" showIcon showButtonBar fluid :invalid="!!errors.start_date" class="text-sm" />
                        <small v-if="errors.start_date" class="text-xs text-red-500">{{ errors.start_date }}</small>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-slate-700">Due Date</label>
                        <DatePicker v-model="dueDate" dateFormat="yy-mm-dd" placeholder="YYYY-MM-DD" :minDate="startDate ?? undefined" showIcon showButtonBar fluid :invalid="!!errors.due_date" class="text-sm" />
                        <small v-if="errors.due_date" class="text-xs text-red-500">{{ errors.due_date }}</small>
                    </div>
                </div>
                <div class="flex gap-2 pt-1">
                    <Button type="button" label="Cancel" severity="secondary" outlined fluid @click="projectModal = false" />
                    <Button
                        type="submit"
                        :loading="isSaving"
                        :label="isUpdate ? 'Update Project' : 'Save Project'"
                        fluid
                        class="bg-linear-to-r from-indigo-500 to-blue-600 border-0"
                    />
                </div>
            </form>
        </Dialog>

        <!-- Header -->
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-linear-to-r from-slate-50 to-white">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-linear-to-br from-indigo-500 to-blue-600 flex items-center justify-center shadow-md">
                    <i class="pi pi-folder text-white"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-800">Projects</h3>
                    <p class="text-xs text-slate-400">Track client projects, status and deadlines</p>
                </div>
            </div>
            <button
                type="button"
                @click="openCreate"
                class="flex items-center gap-2 px-4 py-2.5 rounded-lg transition-all duration-200 bg-linear-to-r from-indigo-500 to-blue-600 hover:from-indigo-600 hover:to-blue-700 text-white text-sm font-medium shadow-md hover:shadow-lg active:scale-95"
            >
                <i class="pi pi-plus-circle"></i>
                Add Project
            </button>
        </div>

        <!-- Toolbar: search + filters -->
        <div class="px-6 py-4 border-b border-slate-100 flex flex-col md:flex-row md:items-center gap-3">
            <IconField class="flex-1">
                <InputIcon class="pi pi-search text-slate-400" />
                <InputText v-model="filters.search" placeholder="Search by client, project or description" fluid class="text-sm" />
            </IconField>
            <Select v-model="filters.status" :options="statuses" placeholder="All statuses" showClear class="md:w-48 text-sm" />
            <Select v-model="filters.priority" :options="priorities" placeholder="All priorities" showClear class="md:w-44 text-sm" />
            <Button
                v-if="hasActiveFilters"
                type="button"
                label="Clear"
                icon="pi pi-filter-slash"
                severity="secondary"
                text
                @click="clearFilters"
            />
        </div>

        <!-- Table -->
        <DataTable
            :value="projects"
            lazy
            paginator
            :loading="isLoading"
            :rows="filters.per_page"
            :first="((filters.page ?? 1) - 1) * (filters.per_page ?? 15)"
            :totalRecords="totalRecords"
            :rowsPerPageOptions="[10, 15, 25, 50, 100]"
            :sortField="filters.sort_by"
            :sortOrder="filters.sort_dir === 'asc' ? 1 : -1"
            removableSort
            @page="onPage"
            @sort="onSort"
            tableStyle="min-width: 60rem"
            :pt="{
                table: { class: 'text-sm' },
                thead: { class: 'bg-slate-50' },
                bodyRow: { class: 'hover:bg-slate-50 transition-colors duration-150 border-b border-slate-100' },
            }"
        >
            <template #empty>
                <div class="flex flex-col items-center justify-center py-16 text-slate-400">
                    <i class="pi pi-folder-open text-4xl mb-3 opacity-30"></i>
                    <p class="text-sm font-medium">No projects found</p>
                    <p class="text-xs mt-1">{{ hasActiveFilters ? 'Try adjusting your search or filters' : 'Click "Add Project" to create the first one' }}</p>
                </div>
            </template>

            <Column field="project_name" header="Project" sortable>
                <template #body="{ data }">
                    <p class="text-slate-800 text-sm font-medium leading-tight">{{ data.project_name }}</p>
                    <p v-if="data.description" class="text-slate-400 text-xs mt-0.5 line-clamp-1 max-w-xs">{{ data.description }}</p>
                </template>
            </Column>

            <Column field="client_name" header="Client" sortable>
                <template #body="{ data }">
                    <span class="text-slate-600 text-sm">{{ data.client_name }}</span>
                </template>
            </Column>

            <Column field="status" header="Status" sortable class="w-36">
                <template #body="{ data }">
                    <span :class="['inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium border', statusClass[data.status]]">
                        {{ data.status }}
                    </span>
                </template>
            </Column>

            <Column field="priority" header="Priority" sortable class="w-28">
                <template #body="{ data }">
                    <span :class="['inline-flex items-center gap-1.5 text-xs font-medium', priorityClass[data.priority]]">
                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                        {{ data.priority }}
                    </span>
                </template>
            </Column>

            <Column field="start_date" header="Start Date" sortable class="w-32">
                <template #body="{ data }">
                    <span class="text-slate-500 text-sm">{{ data.start_date ?? '—' }}</span>
                </template>
            </Column>

            <Column field="due_date" header="Due Date" sortable class="w-32">
                <template #body="{ data }">
                    <span :class="['text-sm', isOverdue(data) ? 'text-red-600 font-medium' : 'text-slate-500']">
                        {{ data.due_date ?? '—' }}
                    </span>
                </template>
            </Column>

            <Column header="Actions" class="w-24">
                <template #body="{ data }">
                    <div class="flex items-center gap-1">
                        <button
                            type="button"
                            title="Edit project"
                            @click="view(data.pid)"
                            class="p-1.5 rounded-md text-blue-600 hover:bg-blue-50 hover:text-blue-700 transition-colors duration-150 cursor-pointer"
                        >
                            <i class="pi pi-pencil"></i>
                        </button>
                        <button
                            type="button"
                            title="Delete project"
                            @click="archive(data.pid)"
                            class="p-1.5 rounded-md text-red-400 hover:bg-red-50 hover:text-red-600 transition-colors duration-150 cursor-pointer"
                        >
                            <i class="pi pi-trash"></i>
                        </button>
                    </div>
                </template>
            </Column>
        </DataTable>
    </div>
</template>

<script setup lang="ts">
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import Textarea from 'primevue/textarea';
import type { DataTablePageEvent, DataTableSortEvent } from 'primevue/datatable';
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { storeToRefs } from 'pinia';
import { useProjectStore } from '@/store/Project';
import { Project } from '@/interface/Interfaces';
import { useConfirmToast } from '@/composables/confirm';
import { useAppToast } from "@/composables/toast";

const { showConfirm } = useConfirmToast();
const toast = useAppToast();
const projectStore = useProjectStore();
const { projects, totalRecords, statuses, priorities, filters } = storeToRefs(projectStore);

const projectModal = ref<boolean>(false);
const isUpdate = ref<boolean>(false);
const isLoading = ref<boolean>(false);
const isSaving = ref<boolean>(false);
const errors = ref<Record<string, string>>({});

const emptyProject = (): Project => ({
    pid: '',
    client_name: '',
    project_name: '',
    description: '',
    status: 'Planning',
    priority: 'Medium',
    start_date: null,
    due_date: null
});
const projectInfo = reactive<Project>(emptyProject());
const startDate = ref<Date | null>(null);
const dueDate = ref<Date | null>(null);

const statusClass: Record<string, string> = {
    'Planning': 'bg-slate-50 text-slate-700 border-slate-200',
    'In Progress': 'bg-blue-50 text-blue-700 border-blue-100',
    'On Hold': 'bg-amber-50 text-amber-700 border-amber-100',
    'Completed': 'bg-emerald-50 text-emerald-700 border-emerald-100',
};
const priorityClass: Record<string, string> = {
    'Low': 'text-slate-500',
    'Medium': 'text-amber-600',
    'High': 'text-red-600',
};

const hasActiveFilters = computed(() => !!(filters.value.search || filters.value.status || filters.value.priority));

// Format a Date as YYYY-MM-DD in local time (toISOString would shift by timezone)
const toDateString = (date: Date | null): string | null => {
    if (!date) return null;
    const pad = (n: number) => String(n).padStart(2, '0');
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
};
const fromDateString = (value: string | null): Date | null => {
    if (!value) return null;
    const [y, m, d] = value.split('-').map(Number);
    return new Date(y, m - 1, d);
};

const isOverdue = (project: Project) => {
    if (!project.due_date || project.status === 'Completed') return false;
    return project.due_date < toDateString(new Date())!;
};

watch(projectModal, (open) => {
    if (!open) {
        Object.assign(projectInfo, emptyProject());
        startDate.value = null;
        dueDate.value = null;
        errors.value = {};
        isUpdate.value = false;
    }
});

// Debounce search so we don't hit the API on every keystroke
let searchTimer: ReturnType<typeof setTimeout> | undefined;
watch(() => filters.value.search, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        filters.value.page = 1;
        read();
    }, 350);
});
watch(() => [filters.value.status, filters.value.priority], () => {
    filters.value.page = 1;
    read();
});

onMounted(async () => {
    try {
        await projectStore.options();
    } catch {
        // Fall back to the default option lists in the store
    }
    await read();
});

const read = async () => {
    isLoading.value = true;
    try {
        await projectStore.read();
    } catch (err: any) {
        toast.error(err.response?.data?.message || "Failed to retrieve Projects");
    } finally {
        isLoading.value = false;
    }
};

const onPage = (event: DataTablePageEvent) => {
    filters.value.page = event.page + 1;
    filters.value.per_page = event.rows;
    read();
};

const onSort = (event: DataTableSortEvent) => {
    if (event.sortField && event.sortOrder) {
        filters.value.sort_by = event.sortField as string;
        filters.value.sort_dir = event.sortOrder === 1 ? 'asc' : 'desc';
    } else {
        filters.value.sort_by = 'created_at';
        filters.value.sort_dir = 'desc';
    }
    filters.value.page = 1;
    read();
};

const clearFilters = () => {
    filters.value.search = '';
    filters.value.status = null;
    filters.value.priority = null;
};

const openCreate = () => {
    isUpdate.value = false;
    projectModal.value = true;
};

const validate = (): boolean => {
    const result: Record<string, string> = {};
    if (!projectInfo.client_name?.trim()) result.client_name = 'Client Name is required.';
    if (!projectInfo.project_name?.trim()) result.project_name = 'Project Name is required.';
    if (!statuses.value.includes(projectInfo.status)) result.status = 'Please select a valid status.';
    if (!priorities.value.includes(projectInfo.priority)) result.priority = 'Please select a valid priority.';
    if (startDate.value && dueDate.value && dueDate.value < startDate.value) {
        result.due_date = 'Due Date cannot be earlier than Start Date.';
    }
    errors.value = result;
    return Object.keys(result).length === 0;
};

const save = async () => {
    if (!validate()) return;
    isSaving.value = true;
    const payload: Project = {
        ...projectInfo,
        start_date: toDateString(startDate.value),
        due_date: toDateString(dueDate.value),
    };
    try {
        if (isUpdate.value) {
            await projectStore.update(payload);
            toast.success("Project updated successfully");
        } else {
            await projectStore.create(payload);
            toast.success("Project created successfully");
        }
        projectModal.value = false;
    } catch (err: any) {
        const fieldErrors = err.response?.data?.errors;
        if (fieldErrors) {
            errors.value = Object.fromEntries(
                Object.entries(fieldErrors).map(([field, messages]) => [field, (messages as string[])[0]])
            );
        }
        toast.error(err.response?.data?.message || `Failed to ${isUpdate.value ? 'update' : 'create'} Project`);
    } finally {
        isSaving.value = false;
    }
};

const view = async (pid: string) => {
    try {
        const project = await projectStore.view(pid);
        Object.assign(projectInfo, project);
        startDate.value = fromDateString(project.start_date);
        dueDate.value = fromDateString(project.due_date);
        isUpdate.value = true;
        projectModal.value = true;
    } catch (err: any) {
        toast.error(err.response?.data?.message || "Failed to retrieve Project");
    }
};

const archive = async (pid: string) => {
    showConfirm({
        message: "Are you sure you want to delete this project?",
        header: "Delete Confirmation",
        onAccept: async () => {
            try {
                await projectStore.archive(pid);
                toast.success("Project deleted successfully");
            } catch (err: any) {
                toast.error(err.response?.data?.message || "Failed to delete Project");
            }
        },
        onReject: () => {},
    });
};
</script>
