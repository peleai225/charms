<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import Card from '@/Components/Card.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import Alert from '@/Components/Alert.vue';
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
const showPasswordConfirm = ref(false);

const submit = () => {
    form.post('/inscription', {
        onFinish: () => {
            if (!form.hasErrors) {
                form.reset('password', 'password_confirmation');
            }
        },
    });
};
</script>

<template>
    <AuthLayout title="Créer un compte" subtitle="Rejoignez notre communauté">
        <Card padding="default" shadow="sm">
            <Alert v-if="$page.props.flash.error" type="danger" class="mb-4">
                {{ $page.props.flash.error }}
            </Alert>

            <form @submit.prevent="submit" class="space-y-4">
                <!-- Prénom + Nom -->
                <div class="grid grid-cols-2 gap-3">
                    <Input
                        v-model="form.first_name"
                        label="Prénom"
                        placeholder="Jean"
                        :error="form.errors.first_name"
                        required
                    />
                    <Input
                        v-model="form.last_name"
                        label="Nom"
                        placeholder="Dupont"
                        :error="form.errors.last_name"
                        required
                    />
                </div>

                <!-- Email -->
                <Input
                    v-model="form.email"
                    type="email"
                    label="Email"
                    placeholder="votre@email.com"
                    :error="form.errors.email"
                    required
                />

                <!-- Téléphone -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        Téléphone <span class="text-slate-400 font-normal text-xs">(optionnel)</span>
                    </label>
                    <div class="flex">
                        <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-slate-300 bg-slate-100 text-slate-500 text-sm">
                            +225
                        </span>
                        <input
                            v-model="form.phone"
                            type="tel"
                            class="flex-1 px-4 py-2 border border-slate-300 rounded-r-lg focus:outline-none focus:ring-2 focus:ring-offset-0 focus:ring-primary-500 focus:border-primary-500 transition"
                            placeholder="07 07 07 07 07"
                        />
                    </div>
                    <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">
                        {{ form.errors.phone }}
                    </p>
                </div>

                <!-- Mot de passe -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        Mot de passe <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            class="w-full pl-4 pr-12 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-0 transition bg-white"
                            :class="form.errors.password ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-slate-300 focus:border-primary-500 focus:ring-primary-500'"
                            placeholder="Minimum 8 caractères"
                            required
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
                </div>

                <!-- Confirmation mot de passe -->
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

                <!-- CGV -->
                <div>
                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input
                            v-model="form.terms"
                            type="checkbox"
                            class="w-4 h-4 mt-0.5 rounded border-slate-300 text-primary-600 focus:ring-primary-500 flex-shrink-0"
                            required
                        />
                        <span class="text-sm text-slate-600 select-none leading-relaxed">
                            J'accepte les
                            <Link href="/legal/conditions-generales" target="_blank" class="text-primary-600 hover:underline font-medium">
                                conditions générales
                            </Link>
                            et la
                            <Link href="/legal/politique-de-confidentialite" target="_blank" class="text-primary-600 hover:underline font-medium">
                                politique de confidentialité
                            </Link>
                            <span class="text-red-500">*</span>
                        </span>
                    </label>
                    <p v-if="form.errors.terms" class="mt-1 ml-6 text-sm text-red-600">
                        {{ form.errors.terms }}
                    </p>
                </div>

                <Button
                    type="submit"
                    variant="primary"
                    size="lg"
                    :loading="form.processing"
                    :disabled="form.processing"
                    class="w-full mt-2"
                >
                    S'inscrire
                </Button>
            </form>

            <p class="mt-5 text-center text-sm text-slate-600">
                Déjà un compte ?
                <Link href="/connexion" class="text-primary-600 font-semibold hover:text-primary-700 transition-colors">
                    Connexion
                </Link>
            </p>
        </Card>
    </AuthLayout>
</template>
