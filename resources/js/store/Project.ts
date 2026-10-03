import { defineStore } from "pinia";
import { ref } from "vue";
import { Project, ProjectFilters } from "@/interface/Interfaces";
import axios from "axios";

export const useProjectStore = defineStore("project", () => {
    const baseUrl = import.meta.env.VITE_APP_API_URL;
    const projects = ref<Project[]>([])
    const totalRecords = ref<number>(0)
    const statuses = ref<string[]>(['Planning', 'In Progress', 'On Hold', 'Completed'])
    const priorities = ref<string[]>(['Low', 'Medium', 'High'])
    const filters = ref<ProjectFilters>({
        search: '',
        status: null,
        priority: null,
        sort_by: 'created_at',
        sort_dir: 'desc',
        page: 1,
        per_page: 15
    })

    const options = async () => {
        const response = await axios.get(`${baseUrl}api/projects/options`);
        statuses.value = response.data.data.statuses;
        priorities.value = response.data.data.priorities;
    }
    const read = async () => {
        // Drop empty values so the API doesn't validate blank filters
        const params = Object.fromEntries(
            Object.entries(filters.value).filter(([, value]) => value !== null && value !== '')
        );
        const response = await axios.get(`${baseUrl}api/projects`, { params });
        projects.value = response.data.data.data;
        totalRecords.value = response.data.data.total;
    }
    const view = async (pid: string): Promise<Project> => {
        const response = await axios.get(`${baseUrl}api/projects/${pid}`);
        return response.data.data;
    }
    const create = async (data: Project) => {
        await axios.post(`${baseUrl}api/projects`, data);
        await read();
    }
    const update = async (data: Project) => {
        await axios.put(`${baseUrl}api/projects/${data.pid}`, data);
        await read();
    }
    const archive = async (pid: string) => {
        await axios.delete(`${baseUrl}api/projects/${pid}`);
        await read();
    }
    return {
        archive,
        create,
        filters,
        options,
        priorities,
        projects,
        read,
        statuses,
        totalRecords,
        update,
        view
    }
})
