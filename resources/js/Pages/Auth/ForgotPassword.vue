<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({ status: String });

const form = useForm({ email: '' });
const submit = () => { form.post('/mot-de-passe-oublie'); };
</script>

<template>
    <AuthLayout title="Mot de passe oublié ?" subtitle="Entrez votre email pour recevoir un lien de réinitialisation">

        <div v-if="status" class="mb-5 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-xl flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ status }}
        </div>

        <div v-if="$page.props.flash?.error" class="mb-5 px-4 py-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ $page.props.flash.error }}
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                    Adresse email <span class="text-red-500">*</span>
                </label>
                <input
                    v-model="form.email"
                    type="email"
                    autocomplete="email"
                    autofocus
                    placeholder="votre@email.com"
                    class="w-full px-4 py-2.5 border rounded-xl text-sm transition focus:outline-none focus:ring-2 focus:ring-offset-0 bg-white"
                    :class="form.errors.email
                        ? 'border-red-300 focus:border-red-400 focus:ring-red-100'
                        : 'border-slate-200 focus:border-slate-400 focus:ring-slate-100'"
                />
                <p v-if="form.errors.email" class="mt-1.5 text-xs text-red-600">{{ form.errors.email }}</p>
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="w-full py-3 bg-slate-900 text-white text-sm font-semibold rounded-xl hover:bg-slate-800 active:scale-[0.98] transition-all disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2"
            >
                <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                {{ form.processing ? 'Envoi...' : 'Envoyer le lien' }}
            </button>
        </form>

        <div class="mt-6 text-center">
            <Link href="/connexion" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-900 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour à la connexion
            </Link>
        </div>

    </AuthLayout>
</template>
