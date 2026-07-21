<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import Card from '@/Components/Card.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import Alert from '@/Components/Alert.vue';
import { ref } from 'vue';

defineProps({
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post('/connexion', {
        onFinish: () => {
            if (!form.hasErrors) {
                form.reset('password');
            }
        },
    });
};
</script>

<template>
    <AuthLayout title="Connexion" subtitle="Accédez à votre espace client">
        <Card padding="default" shadow="sm">
            <Alert v-if="status" type="success" class="mb-4">
                {{ status }}
            </Alert>

            <Alert v-if="$page.props.flash.error" type="danger" class="mb-4">
                {{ $page.props.flash.error }}
            </Alert>

            <form @submit.prevent="submit" class="space-y-4">
                <Input
                    v-model="form.email"
                    type="email"
                    label="Adresse email"
                    placeholder="votre@email.com"
                    :error="form.errors.email"
                    required
                    autofocus
                />

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-sm font-medium text-slate-700">
                            Mot de passe
                        </label>
                        <Link href="/mot-de-passe-oublie" class="text-xs text-primary-600 hover:text-primary-700 font-medium">
                            Mot de passe oublié ?
                        </Link>
                    </div>
                    <div class="relative">
                        <input
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            class="w-full pl-4 pr-12 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-0 transition bg-white"
                            :class="form.errors.password ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-slate-300 focus:border-primary-500 focus:ring-primary-500'"
                            placeholder="••••••••"
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

                <label class="flex items-center gap-2.5 cursor-pointer">
                    <input
                        v-model="form.remember"
                        type="checkbox"
                        class="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                    />
                    <span class="text-sm text-slate-600 select-none">Se souvenir de moi</span>
                </label>

                <Button
                    type="submit"
                    variant="primary"
                    size="lg"
                    :loading="form.processing"
                    :disabled="form.processing"
                    class="w-full"
                >
                    Se connecter
                </Button>
            </form>

            <div class="relative my-5">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-slate-200"></div>
                </div>
                <div class="relative flex justify-center">
                    <span class="px-3 bg-white text-xs text-slate-400 uppercase tracking-wider">ou</span>
                </div>
            </div>

            <p class="text-center text-sm text-slate-600">
                Pas encore de compte ?
                <Link href="/inscription" class="text-primary-600 font-semibold hover:text-primary-700 transition-colors">
                    Créer un compte
                </Link>
            </p>
        </Card>
    </AuthLayout>
</template>
