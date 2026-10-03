<template>
  <div class="min-h-screen flex items-center justify-center bg-slate-100 px-4 py-12">
    <div class="w-full max-w-md">
      <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-8 sm:p-10">
        <!-- Brand -->
        <div class="flex flex-col items-center text-center mb-8">
          <div class="w-12 h-12 rounded-xl bg-linear-to-br from-blue-600 to-blue-800 flex items-center justify-center shadow-sm mb-4">
            <i class="pi pi-check-square text-white text-xl"></i>
          </div>
          <h1 class="text-2xl font-bold text-slate-900 tracking-tight mb-1.5">{{ appName }}</h1>
          <p class="text-slate-500 text-sm">Sign in to continue to your dashboard</p>
        </div>

        <form @submit.prevent="login" class="space-y-5">
          <div class="flex flex-col gap-1.5">
            <label for="email" class="text-sm font-medium text-slate-700">Email address</label>
            <IconField>
              <InputIcon class="pi pi-envelope text-slate-400" />
              <InputText id="email" v-model="loginForm.email" type="email" placeholder="you@example.com" fluid required autofocus />
            </IconField>
          </div>

          <div class="flex flex-col gap-1.5">
            <div class="flex items-center justify-between">
              <label for="password" class="text-sm font-medium text-slate-700">Password</label>
              <a href="#" class="text-xs font-medium text-blue-600 hover:text-blue-700 transition">Forgot password?</a>
            </div>
            <Password
              id="password"
              v-model="loginForm.password"
              placeholder="••••••••"
              :feedback="false"
              toggleMask
              fluid
              inputClass="w-full"
              required
            />
          </div>

       

          <Message v-if="errorMessage" severity="error" :closable="false" class="text-sm">{{ errorMessage }}</Message>

          <Button type="submit" :loading="isLoading" :label="isLoading ? 'Signing in...' : 'Sign in'" fluid class="bg-linear-to-r from-blue-600 to-blue-700 border-0 font-semibold" />
        </form>
      </div>

      <p class="mt-6 text-center text-xs text-slate-400">© {{ getYear() }} {{ appName }}. All rights reserved.</p>
    </div>
  </div>
</template>
<script setup lang="ts">
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useAppToast } from "@/composables/toast";
import axios from "axios";
import IconField from "primevue/iconfield";
import InputIcon from "primevue/inputicon";
import Password from "primevue/password";
import Checkbox from "primevue/checkbox";
import Message from "primevue/message";

interface LoginForm {
  email: string;
  password: string;
}

const appName = import.meta.env.VITE_APP_NAME ?? "Project Tracker";
const toast = useAppToast();
const loginForm = ref<LoginForm>({
  email: "",
  password: "",
});
const router = useRouter();
const isLoading = ref<boolean>(false);
const baseUrl = import.meta.env.VITE_APP_API_URL;
const errorMessage = ref<string>("");

const login = async () => {
  if (!loginForm.value.email || !loginForm.value.password) {
    errorMessage.value = "Please fill in all fields";
    return;
  }
  isLoading.value = true;
  errorMessage.value = "";

  try {
    await axios.get(`${baseUrl}sanctum/csrf-cookie`);
    const response = await axios.post(`${baseUrl}user/login`, loginForm.value);
    if (response.status == 200) {
      localStorage.removeItem("isLoggedout");
      router.push({ name: "Dashboard" });
    }
  } catch (error: any) {
    const message = error.response?.data?.message || "Unable to sign in";
    errorMessage.value = message;
    toast.error(message);
    isLoading.value = false;
  }
};

const getYear = () => {
  const date = new Date();
  return date.getFullYear();
};
</script>
