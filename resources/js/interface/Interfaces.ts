export interface User {
    pid?: string;
    name: string;
    email: string;
    password: string;
}

export interface Project {
    pid?: string;
    client_name: string;
    project_name: string;
    description: string | null;
    status: string;
    priority: string;
    start_date: string | null;
    due_date: string | null;
    created_at?: string;
}

export interface ProjectFilters {
    search?: string;
    status?: string | null;
    priority?: string | null;
    sort_by?: string;
    sort_dir?: 'asc' | 'desc';
    page?: number;
    per_page?: number;
}
