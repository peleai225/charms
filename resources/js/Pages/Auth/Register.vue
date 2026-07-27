<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const showPassword = ref(false);
const showConfirm  = ref(false);

const submit = () => {
    form.post('/inscription', {
        onFinish: () => { if (!form.hasErrors) form.reset('password', 'password_confirmation'); },
    });
};

// force même classe input pour DRY
const inputClass = (hasError) => [
    'w-full px-4 py-2.5 border rounded-xl text-sm transition focus:outline-none focus:ring-2 focus:ring-offset-0 bg-white',
    hasError
        ? 'border-red-300 focus:border-red-400 focus:ring-red-100'
        : 'border-slate-200 focus:border-slate-400 focus:ring-slate-100',
].join(' ');
</script>

<template>
    <AuthLayout title="Créer un compte" subtitle="Rejoignez des milliers de clients satisfaits">

        <!-- Erreur globale -->
        <div v-if="$page.props.flash?.error" class="mb-5 px-4 py-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ $page.props.flash.error }}
        </div>

        <form @submit.prevent="submit" class="space-y-4">

            <!-- Prénom + Nom -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Prénom <span class="text-red-500">*</span></label>
                    <input
                        v-model="form.first_name"
                        type="text"
                        autocomplete="given-name"
                        placeholder="Jean"
                        :class="inputClass(form.errors.first_name)"
                    />
                    <p v-if="form.errors.first_name" class="mt-1 text-xs text-red-600">{{ form.errors.first_name }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Nom <span class="text-red-500">*</span></label>
                    <input
                        v-model="form.last_name"
                        type="text"
                        autocomplete="family-name"
                        placeholder="Dupont"
                        :class="inputClass(form.errors.last_name)"
                    />
                    <p v-if="form.errors.last_name" class="mt-1 text-xs text-red-600">{{ form.errors.last_name }}</p>
                </div>
            </div>

            <!-- Email -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Email <span class="text-red-500">*</span></label>
                <input
                    v-model="form.email"
                    type="email"
                    autocomplete="email"
                    placeholder="votre@email.com"
                    :class="inputClass(form.errors.email)"
                />
                <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
            </div>

            <!-- Téléphone -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                    Téléphone
                    <span class="text-slate-400 font-normal text-xs ml-1">(optionnel)</span>
                </label>
                <div class="flex">
                    <span class="inline-flex items-center px-3.5 rounded-l-xl border border-r-0 border-slate-200 bg-slate-50 text-slate-500 text-sm font-medium">
                        +225
                    </span>
                    <input
                        v-model="form.phone"
                        type="tel"
                        autocomplete="tel"
                        placeholder="07 07 07 07 07"
                        class="flex-1 px-4 py-2.5 border border-slate-200 rounded-r-xl text-sm focus:outline-none focus:ring-2 focus:ring-slate-100 focus:border-slate-400 transition bg-white"
                        :class="form.errors.phone ? 'border-red-300' : ''"
                    />
                </div>
                <p v-if="form.errors.phone" class="mt-1 text-xs text-red-600">{{ form.errors.phone }}</p>
            </div>

            <!-- Mot de passe -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Mot de passe <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        autocomplete="new-password"
                        placeholder="Minimum 8 caractères"
                        class="w-full pl-4 pr-11 py-2.5 border rounded-xl text-sm transition focus:outline-none focus:ring-2 focus:ring-offset-0 bg-white"
                        :class="form.errors.password
                            ? 'border-red-300 focus:border-red-400 focus:ring-red-100'
                            : 'border-slate-200 focus:border-slate-400 focus:ring-slate-100'"
                    />
                    <button type="button" @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition-colors">
                        <svg v-if="!showPassword" class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg v-else class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
                <p v-if="form.errors.password" class="mt-1 text-xs text-red-600">{{ form.errors.password }}</p>
            </div>

            <!-- Confirmation mot de passe -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Confirmer le mot de passe <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input
                        v-model="form.password_confirmation"
                        :type="showConfirm ? 'text' : 'password'"
                        autocomplete="new-password"
                        placeholder="Même mot de passe"
                        class="w-full pl-4 pr-11 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-slate-100 focus:border-slate-400 transition bg-white"
                    />
                    <button type="button" @click="showConfirm = !showConfirm"
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition-colors">
                        <svg v-if="!showConfirm" class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg v-else class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- CGV -->
            <div>
                <label class="flex items-start gap-2.5 cursor-pointer">
                    <input
                        v-model="form.terms"
                        type="checkbox"
                        required
                        class="w-4 h-4 mt-0.5 rounded border-slate-300 text-slate-900 focus:ring-slate-500 flex-shrink-0"
                    />
                    <span class="text-sm text-slate-600 select-none leading-relaxed">
                        J'accepte toutes les
                        <Link href="/legal/conditions-generales" target="_blank" class="font-medium text-slate-900 hover:underline underline-offset-2">Conditions</Link>,
                        <Link href="/legal/politique-de-confidentialite" target="_blank" class="font-medium text-slate-900 hover:underline underline-offset-2">Politique de confidentialité</Link>
                        et les Frais applicables
                    </span>
                </label>
                <p v-if="form.errors.terms" class="mt-1 ml-6 text-xs text-red-600">{{ form.errors.terms }}</p>
            </div>

            <!-- Bouton submit -->
            <button
                type="submit"
                :disabled="form.processing"
                class="w-full py-3 bg-slate-900 text-white text-sm font-semibold rounded-xl
                       hover:bg-slate-800 active:scale-[0.98] transition-all duration-150
                       disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2 mt-1"
            >
                <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                {{ form.processing ? 'Création...' : "S'inscrire" }}
            </button>
        </form>

        <!-- Lien connexion -->
        <p class="mt-5 text-center text-sm text-slate-600">
            Déjà un compte ?
            <Link href="/connexion" class="font-semibold text-slate-900 hover:underline underline-offset-2 transition-colors">
                Se connecter
            </Link>
        </p>

    </AuthLayout>
</template>
