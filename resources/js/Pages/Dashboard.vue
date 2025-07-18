<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';

const url = ref('');
const token = ref('');
const bodyJson = ref('{\n  "key": "value"\n}');
const headers = reactive([{ key: '', value: '' }]);
const responseText = ref('');
const loading = ref(false);
const error = ref('');

function addHeader() {
    headers.push({ key: '', value: '' });
}
function removeHeader(idx: number) {
    if (headers.length > 1) headers.splice(idx, 1);
}
async function sendPost() {
    responseText.value = '';
    error.value = '';
    loading.value = true;

    // Validar JSON del body antes de enviarlo al backend
    try {
        JSON.parse(bodyJson.value);
    } catch (e: any) {
        error.value = 'Body JSON inválido: ' + e.message;
        loading.value = false;
        return;
    }

    try {
        const res = await fetch('/api/simulate-post', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                url: url.value,
                token: token.value.trim() || null,
                body: bodyJson.value,
                headers: headers.filter((h) => h.key && h.value),
            }),
        });

        const data = await res.json();

        if (res.ok) {
            // Mostrar respuesta formateada
            responseText.value = JSON.stringify(data, null, 2);
        } else {
            // Mostrar error recibido del backend
            error.value = data.error || 'Error en la solicitud';
        }
    } catch (e: any) {
        error.value = 'Error en la solicitud: ' + e.message;
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                Dashboard: Simulador de POST
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden rounded-lg bg-white p-8 shadow-md dark:bg-gray-900"
                >
                    <form @submit.prevent="sendPost" class="space-y-6">
                        <div>
                            <label
                                class="mb-1 block font-bold text-gray-700 dark:text-gray-200"
                                >URL de destino</label
                            >
                            <input
                                v-model="url"
                                type="text"
                                class="w-full rounded border px-3 py-2 focus:ring-2 focus:ring-blue-400 dark:border-gray-600 dark:bg-gray-800"
                                placeholder="https://api.tu-endpoint.com/post"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1 block font-bold text-gray-700 dark:text-gray-200"
                                >Token Bearer (opcional)</label
                            >
                            <input
                                v-model="token"
                                type="text"
                                class="w-full rounded border px-3 py-2 focus:ring-2 focus:ring-blue-400 dark:border-gray-600 dark:bg-gray-800"
                                placeholder="Escribe tu Bearer token aquí"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1 block font-bold text-gray-700 dark:text-gray-200"
                                >Cabeceras Personalizadas</label
                            >
                            <div class="space-y-2">
                                <div
                                    v-for="(h, idx) in headers"
                                    :key="idx"
                                    class="flex gap-2"
                                >
                                    <input
                                        v-model="h.key"
                                        type="text"
                                        placeholder="Nombre"
                                        class="flex-1 rounded border px-2 py-1 dark:border-gray-600 dark:bg-gray-800"
                                    />
                                    <input
                                        v-model="h.value"
                                        type="text"
                                        placeholder="Valor"
                                        class="flex-1 rounded border px-2 py-1 dark:border-gray-600 dark:bg-gray-800"
                                    />
                                    <button
                                        type="button"
                                        @click="removeHeader(idx)"
                                        class="ml-2 text-red-500 hover:text-red-700"
                                        v-if="headers.length > 1"
                                        aria-label="Eliminar cabecera"
                                    >
                                        🗑️
                                    </button>
                                </div>
                                <button
                                    type="button"
                                    @click="addHeader"
                                    class="ml-1 text-sm text-blue-600 hover:underline"
                                >
                                    + Agregar cabecera
                                </button>
                            </div>
                        </div>
                        <div>
                            <label
                                class="mb-1 block font-bold text-gray-700 dark:text-gray-200"
                                >Body JSON</label
                            >
                            <textarea
                                v-model="bodyJson"
                                rows="6"
                                class="w-full rounded border px-3 py-2 font-mono text-sm focus:ring-2 focus:ring-blue-400 dark:border-gray-600 dark:bg-gray-800"
                            ></textarea>
                        </div>
                        <div class="flex justify-end">
                            <button
                                type="submit"
                                :disabled="loading"
                                class="rounded bg-blue-600 px-6 py-2 text-white shadow hover:bg-blue-700 disabled:opacity-50"
                            >
                                {{ loading ? 'Enviando...' : 'Enviar POST' }}
                            </button>
                        </div>
                    </form>
                    <div
                        v-if="error"
                        class="mt-6 rounded border border-red-300 bg-red-100 p-3 text-red-800"
                        role="alert"
                    >
                        <strong>Error:</strong> {{ error }}
                    </div>

                    <div
                        v-if="responseText"
                        class="mt-6 rounded border bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800"
                    >
                        <h3
                            class="mb-2 font-semibold text-gray-700 dark:text-gray-200"
                        >
                            Respuesta:
                        </h3>
                        <pre
                            class="whitespace-pre-wrap text-sm text-gray-800 dark:text-gray-100"
                            >{{ responseText }}</pre
                        >
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
