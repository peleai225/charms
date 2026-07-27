<script setup>
import FrontLayout from '@/Layouts/FrontLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Card from '@/Components/Card.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import Alert from '@/Components/Alert.vue';

defineProps({
    whatsapp_number: String,
});

const form = useForm({
    order_number: '',
    email: '',
});

const submit = () => {
    form.get('/suivi-commande/resultat', {
        preserveState: true,
    });
};
</script>

<template>
    <FrontLayout>
        <Head title="Suivi de commande" />

        <!-- Breadcrumb -->
        <div class="bg-white border-b border-slate-100">
            <div class="container mx-auto px-4 py-3">
                <nav class="flex items-center gap-2 text-sm text-slate-500">
                    <Link href="/" class="hover:text-slate-900 transition-colors">Accueil</Link>
                    <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                    <span class="text-slate-900 font-medium">Suivi de commande</span>
                </nav>
            </div>
        </div>

        <div class="min-h-[70vh] flex items-center bg-slate-50 py-12 px-4">
            <div class="max-w-md mx-auto w-full">
                <!-- Icon -->
                <div class="text-center mb-6">
                    <div class="w-14 h-14 bg-primary-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-slate-900">Suivre ma commande</h1>
                    <p class="text-sm text-slate-500 mt-1">Entrez votre numéro de commande reçu par SMS ou WhatsApp</p>
                </div>

                <!-- Error messages -->
                <Alert v-if="$page.props.flash?.error" type="danger" class="mb-4">
                    {{ $page.props.flash.error }}
                </Alert>

                <!-- Form -->
                <Card padding="default" shadow="sm">
                    <form @submit.prevent="submit" class="space-y-4">
                        <Input
                            v-model="form.order_number"
                            label="Numéro de commande"
                            placeholder="CMD-2024-XXXX"
                            :error="form.errors.order_number"
                            required
                        />

                        <Input
                            v-model="form.email"
                            type="email"
                            label="Email de la commande"
                            placeholder="votre@email.com"
                            :error="form.errors.email"
                            required
                        />

                        <Button
                            type="submit"
                            variant="primary"
                            size="lg"
                            :loading="form.processing"
                            :disabled="form.processing"
                            class="w-full"
                        >
                            Rechercher
                        </Button>
                    </form>
                </Card>

                <!-- Auth link -->
                <p v-if="$page.props.auth.user" class="text-center text-sm text-slate-500 mt-4">
                    <Link href="/mon-compte/commandes" class="text-primary-600 hover:underline font-medium">
                        Voir toutes mes commandes
                    </Link>
                </p>
                <p v-else class="text-center text-sm text-slate-500 mt-4">
                    Vous avez un compte ?
                    <Link href="/connexion" class="text-primary-600 hover:underline font-medium">Connectez-vous</Link>
                </p>

                <!-- Aide -->
                <div v-if="whatsapp_number" class="mt-6 p-4 bg-slate-100 rounded-xl text-center">
                    <p class="text-sm text-slate-600 mb-2">Vous n'avez pas reçu votre numéro de commande ?</p>
                    <a
                        :href="`https://wa.me/${whatsapp_number.replace(/[^0-9]/g, '')}?text=${encodeURIComponent('Bonjour, je n\'ai pas reçu mon numéro de commande.')}`"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-[#25D366] text-white text-sm font-medium rounded-lg hover:opacity-90 transition-opacity"
                    >
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        Contactez-nous sur WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </FrontLayout>
</template>
