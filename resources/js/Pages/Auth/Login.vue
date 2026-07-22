<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({ status: String });

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post('/connexion', {
        onFinish: () => { if (!form.hasErrors) form.reset('password'); },
    });
};
</script>

<template>
    <AuthLayout title="Bon retour 👋" subtitle="Connectez-vous à votre espace client">

        <!-- Flash success (ex: email vérifié) -->
        <div v-if="status" class="mb-5 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-xl flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ status }}
        </div>

        <!-- Erreur globale -->
        <div v-if="$page.props.flash?.error" class="mb-5 px-4 py-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ $page.props.flash.error }}
        </div>

        <form @submit.prevent="submit" class="space-y-4">

            <!-- Email -->
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

            <!-- Mot de passe -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="text-sm font-medium text-slate-700">
                        Mot de passe <span class="text-red-500">*</span>
                    </label>
                    <Link href="/mot-de-passe-oublie" class="text-xs text-slate-500 hover:text-slate-900 transition-colors font-medium">
                        Mot de passe oublié ?
                    </Link>
                </div>
                <div class="relative">
                    <input
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="w-full pl-4 pr-11 py-2.5 border rounded-xl text-sm transition focus:outline-none focus:ring-2 focus:ring-offset-0 bg-white"
                        :class="form.errors.password
                            ? 'border-red-300 focus:border-red-400 focus:ring-red-100'
                            : 'border-slate-200 focus:border-slate-400 focus:ring-slate-100'"
                    />
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition-colors"
                    >
                        <svg v-if="!showPassword" class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg v-else class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
                <p v-if="form.errors.password" class="mt-1.5 text-xs text-red-600">{{ form.errors.password }}</p>
            </div>

            <!-- Se souvenir -->
            <label class="flex items-center gap-2.5 cursor-pointer select-none">
                <input
                    v-model="form.remember"
                    type="checkbox"
                    class="w-4 h-4 rounded border-slate-300 text-slate-900 focus:ring-slate-500"
                />
                <span class="text-sm text-slate-600">Se souvenir de moi</span>
            </label>

            <!-- Bouton submit -->
            <button
                type="submit"
                :disabled="form.processing"
                class="w-full py-3 bg-slate-900 text-white text-sm font-semibold rounded-xl
                       hover:bg-slate-800 active:scale-[0.98] transition-all duration-150
                       disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2"
            >
                <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                {{ form.processing ? 'Connexion...' : 'Se connecter' }}
            </button>
        </form>

        <!-- Séparateur -->
        <div class="relative my-6">
            <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-100"></div></div>
            <div class="relative flex justify-center">
                <span class="px-3 bg-white text-xs text-slate-400 uppercase tracking-widest">ou</span>
            </div>
        </div>

        <!-- Lien inscription -->
        <p class="text-center text-sm text-slate-600">
            Pas encore de compte ?
            <Link href="/inscription" class="font-semibold text-slate-900 hover:underline underline-offset-2 transition-colors">
                Créer un compte
            </Link>
        </p>

    </AuthLayout>
</template>
