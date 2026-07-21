<script setup>
import { useForm } from '@inertiajs/vue3';
import Card from '@/Components/Card.vue';
import Button from '@/Components/Button.vue';
import Alert from '@/Components/Alert.vue';
import { ref } from 'vue';

const props = defineProps({
    token: String,
    email: String,
    status: String,
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showPasswordConfirm = ref(false);

const submit = () => {
    form.post('/mot-de-passe/reset', {
        onFinish: () => {
            if (!form.hasErrors) {
                form.reset('password', 'password_confirmation');
            }
        },
    });
};
</script>

<template>
    <div class="min-h-screen bg-slate-50 flex flex-col justify-center py-12 px-4">
        <div class="max-w-sm mx-auto w-full mt-12 mb-20">
            <!-- Icône + Titre -->
            <div class="text-center mb-8">
                <div class="w-14 h-14 bg-primary-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-slate-900">Nouveau mot de passe</h1>
                <p class="mt-1 text-sm text-slate-500">Choisissez un mot de passe sécurisé</p>
            </div>

            <Card padding="default" shadow="sm">
                <Alert v-if="status" type="success" class="mb-4">
                    {{ status }}
                </Alert>

                <Alert v-if="$page.props.flash.error" type="danger" class="mb-4">
                    {{ $page.props.flash.error }}
                </Alert>

                <form @submit.prevent="submit" class="space-y-4">
                    <!-- Email (lecture seule) -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Adresse email
                        </label>
                        <input
                            :value="email"
                            type="email"
                            readonly
                            class="w-full px-4 py-2 bg-slate-100 border border-slate-200 rounded-lg text-slate-500 text-sm cursor-not-allowed"
                        />
                    </div>

                    <!-- Nouveau mot de passe -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Nouveau mot de passe <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                class="w-full pl-4 pr-12 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-0 transition bg-white"
                                :class="form.errors.password ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-slate-300 focus:border-primary-500 focus:ring-primary-500'"
                                placeholder="Minimum 8 caractères"
                                required
                                autofocus
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 transition-colors"
                            >
                                <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">
                            {{ form.errors.password }}
                        </p>
                        <p class="mt-1 text-xs text-slate-400">
                            8 caractères minimum, une majuscule, une minuscule et un chiffre.
                        </p>
                    </div>

                    <!-- Confirmer mot de passe -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Confirmer le mot de passe <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                v-model="form.password_confirmation"
                                :type="showPasswordConfirm ? 'text' : 'password'"
                                class="w-full pl-4 pr-12 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-0 focus:ring-primary-500 focus:border-primary-500 transition bg-white"
                                placeholder="Même mot de passe"
                                required
                            />
                            <button
                                type="button"
                                @click="showPasswordConfirm = !showPasswordConfirm"
                                class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 transition-colors"
                            >
                                <svg v-if="!showPasswordConfirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <Button
                        type="submit"
                        variant="primary"
                        size="lg"
                        :loading="form.processing"
                        :disabled="form.processing"
                        class="w-full"
                    >
                        Réinitialiser le mot de passe
                    </Button>
                </form>
            </Card>
        </div>
    </div>
</template>
