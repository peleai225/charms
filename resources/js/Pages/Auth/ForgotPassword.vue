<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import Card from '@/Components/Card.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import Alert from '@/Components/Alert.vue';

defineProps({
    status: String,
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post('/mot-de-passe-oublie');
};
</script>

<template>
    <div class="min-h-screen bg-slate-50 flex flex-col justify-center py-12 px-4">
        <div class="max-w-sm mx-auto w-full mt-12 mb-20">
            <!-- Icône + Titre -->
            <div class="text-center mb-8">
                <div class="w-14 h-14 bg-primary-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-slate-900">Mot de passe oublié ?</h1>
                <p class="mt-2 text-sm text-slate-500 max-w-xs mx-auto">
                    Entrez votre adresse email et nous vous enverrons un lien pour réinitialiser votre mot de passe.
                </p>
            </div>

            <Card padding="default" shadow="sm">
                <Alert v-if="status" type="success" class="mb-5">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="font-medium">{{ status }}</p>
                    </div>
                </Alert>

                <Alert v-if="$page.props.flash.error" type="danger" class="mb-5">
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

                    <Button
                        type="submit"
                        variant="primary"
                        size="lg"
                        :loading="form.processing"
                        :disabled="form.processing"
                        class="w-full"
                    >
                        Envoyer le lien de réinitialisation
                    </Button>
                </form>

                <div class="mt-5 text-center">
                    <Link href="/connexion" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-primary-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Retour à la connexion
                    </Link>
                </div>
            </Card>
        </div>
    </div>
</template>
