<script setup>
import FrontLayout from '@/Layouts/FrontLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Card from '@/Components/Card.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import Select from '@/Components/Select.vue';
import Textarea from '@/Components/Textarea.vue';
import Alert from '@/Components/Alert.vue';
import { ref } from 'vue';

const props = defineProps({
    contact_email: String,
    contact_phone: String,
    contact_address: String,
    whatsapp_number: String,
});

const form = useForm({
    name: '',
    email: '',
    subject: '',
    message: '',
});

const subjectOptions = [
    { value: 'order', label: 'Question sur une commande' },
    { value: 'product', label: 'Question sur un produit' },
    { value: 'return', label: 'Retour / Remboursement' },
    { value: 'delivery', label: 'Problème de livraison' },
    { value: 'partnership', label: 'Partenariat' },
    { value: 'other', label: 'Autre' },
];

const faqs = [
    { q: 'Délais de livraison ?', a: '24–72h pour Abidjan, 3–7 jours pour les autres villes.' },
    { q: 'Comment suivre ma commande ?', a: 'Via la page "Suivi de commande" avec votre numéro reçu par SMS/WhatsApp.' },
    { q: 'Modes de paiement acceptés ?', a: 'Orange Money, MTN MoMo, carte bancaire (CinetPay) et paiement à la livraison.' },
];

const openFaq = ref(null);

const submit = () => {
    form.post('/contact', {
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <FrontLayout>
        <Head title="Nous contacter">
            <meta name="description" content="Contactez notre équipe pour toute question sur vos commandes, produits ou livraisons. Réponse rapide garantie." />
        </Head>

        <!-- Breadcrumb -->
        <div class="bg-white border-b border-slate-100">
            <div class="container mx-auto px-4 py-3">
                <nav class="flex items-center gap-2 text-sm text-slate-500">
                    <Link href="/" class="hover:text-slate-900 transition-colors">Accueil</Link>
                    <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                    <span class="text-slate-900 font-medium">Contact</span>
                </nav>
            </div>
        </div>

        <div class="container mx-auto px-4 py-10 max-w-5xl">
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-slate-900">Nous contacter</h1>
                <p class="text-slate-500 text-sm mt-1">Notre équipe est disponible pour répondre à toutes vos questions.</p>
            </div>

            <div class="grid lg:grid-cols-2 gap-8">
                <!-- Left: Form -->
                <Card padding="default" shadow="sm">
                    <h2 class="text-base font-bold text-slate-900 mb-5">Envoyez-nous un message</h2>

                    <Alert v-if="$page.props.flash.success" type="success" class="mb-5" dismissible>
                        {{ $page.props.flash.success }}
                    </Alert>

                    <Alert v-if="$page.props.flash.error" type="danger" class="mb-5">
                        {{ $page.props.flash.error }}
                    </Alert>

                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="grid sm:grid-cols-2 gap-4">
                            <Input
                                v-model="form.name"
                                label="Nom complet"
                                placeholder="Votre nom"
                                :error="form.errors.name"
                                required
                            />
                            <Input
                                v-model="form.email"
                                type="email"
                                label="Email"
                                placeholder="votre@email.com"
                                :error="form.errors.email"
                                required
                            />
                        </div>

                        <Select
                            v-model="form.subject"
                            label="Sujet"
                            placeholder="Choisir un sujet"
                            :options="subjectOptions"
                            :error="form.errors.subject"
                            required
                        />

                        <Textarea
                            v-model="form.message"
                            label="Message"
                            placeholder="Votre message..."
                            :rows="5"
                            :error="form.errors.message"
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
                            Envoyer le message
                        </Button>
                    </form>
                </Card>

                <!-- Right: Contact info -->
                <div class="space-y-4">
                    <!-- WhatsApp CTA -->
                    <div v-if="whatsapp_number" class="bg-[#25D366] rounded-2xl p-5 text-white">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-bold text-sm">Réponse rapide sur WhatsApp</p>
                                <p class="text-white/80 text-xs">Disponible 7j/7</p>
                            </div>
                        </div>
                        <a
                            :href="`https://wa.me/${whatsapp_number.replace(/[^0-9]/g, '')}?text=${encodeURIComponent('Bonjour, j\'ai une question.')}`"
                            target="_blank"
                            rel="noopener"
                            class="block w-full text-center py-2.5 bg-white text-[#25D366] font-bold text-sm rounded-xl hover:bg-white/90 transition-colors"
                        >
                            Écrire sur WhatsApp
                        </a>
                    </div>

                    <!-- Contact details -->
                    <Card padding="default" shadow="sm" class="space-y-4">
                        <!-- Phone -->
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 bg-blue-50 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 mb-0.5">Téléphone</p>
                                <a v-if="contact_phone" :href="`tel:${contact_phone.replace(/[^0-9+]/g, '')}`" class="text-sm font-semibold text-blue-600 hover:underline">
                                    {{ contact_phone }}
                                </a>
                                <p v-else class="text-sm text-slate-400">Non renseigné</p>
                            </div>
                        </div>

                        <div class="border-t border-slate-100"></div>

                        <!-- Email -->
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 bg-slate-50 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 mb-0.5">Email</p>
                                <a v-if="contact_email" :href="`mailto:${contact_email}`" class="text-sm font-semibold text-blue-600 hover:underline">
                                    {{ contact_email }}
                                </a>
                                <p v-else class="text-sm text-slate-400">Non renseigné</p>
                            </div>
                        </div>

                        <div class="border-t border-slate-100"></div>

                        <!-- Address -->
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 bg-slate-50 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 mb-0.5">Adresse</p>
                                <p class="text-sm text-slate-700 whitespace-pre-line">{{ contact_address || 'Non renseignée' }}</p>
                            </div>
                        </div>

                        <div class="border-t border-slate-100"></div>

                        <!-- Hours -->
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 bg-slate-50 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 mb-0.5">Horaires</p>
                                <p class="text-sm text-slate-700">Lun–Sam : 8h–20h</p>
                                <p class="text-sm text-slate-700">Dim : 10h–18h</p>
                            </div>
                        </div>
                    </Card>

                    <!-- FAQ rapide -->
                    <Card padding="default" shadow="sm">
                        <h3 class="text-sm font-bold text-slate-900 mb-3">Questions fréquentes</h3>
                        <div class="space-y-1.5">
                            <div v-for="(faq, index) in faqs" :key="index" class="border border-slate-100 rounded-xl overflow-hidden">
                                <button
                                    @click="openFaq = openFaq === index ? null : index"
                                    class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-slate-50 transition-colors"
                                >
                                    <span class="text-xs font-semibold text-slate-800">{{ faq.q }}</span>
                                    <svg
                                        class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200 flex-shrink-0 ml-2"
                                        :class="{ 'rotate-180': openFaq === index }"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                                <Transition
                                    enter-active-class="transition-all duration-200 ease-out"
                                    enter-from-class="max-h-0 opacity-0"
                                    enter-to-class="max-h-96 opacity-100"
                                    leave-active-class="transition-all duration-200 ease-in"
                                    leave-from-class="max-h-96 opacity-100"
                                    leave-to-class="max-h-0 opacity-0"
                                >
                                    <div v-show="openFaq === index" class="overflow-hidden">
                                        <p class="px-4 pb-3 text-xs text-slate-500">{{ faq.a }}</p>
                                    </div>
                                </Transition>
                            </div>
                        </div>
                    </Card>
                </div>
            </div>
        </div>
    </FrontLayout>
</template>
