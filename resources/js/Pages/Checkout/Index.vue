<script setup>
import FrontLayout from '@/Layouts/FrontLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import Card from '@/Components/Card.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import { useHelpers } from '@/Composables/useHelpers';
import { ref } from 'vue';

const props = defineProps({
    cart: Object,
    customer: Object,
    addresses: Array,
    settings: Object,
});

const { formatPrice } = useHelpers();

const step = ref(1); // 1: Livraison, 2: Paiement

const form = useForm({
    // Contact
    email: props.customer?.email || '',
    phone: props.customer?.phone || '',

    // Shipping
    shipping_first_name: props.customer?.first_name || '',
    shipping_last_name: props.customer?.last_name || '',
    shipping_address: '',
    shipping_city: '',
    shipping_postal_code: '',
    shipping_country: 'CI',

    // Payment
    payment_method: props.settings.payment_cod_enabled === '1' ? 'cod' : 'moneyfusion',

    // Notes
    notes: '',
});

const submit = () => {
    form.post('/commander/traiter', {
        onSuccess: () => {
            // Redirect handled by controller
        },
    });
};
</script>

<template>
    <FrontLayout title="Commander">
        <Head title="Commander" />

        <div class="bg-slate-50 min-h-screen py-8">
            <div class="container mx-auto px-4">
                <div class="max-w-5xl mx-auto">
                    <h1 class="text-2xl font-bold text-slate-900 mb-6">Finaliser ma commande</h1>

                    <div class="grid lg:grid-cols-3 gap-6">
                        <!-- Form -->
                        <div class="lg:col-span-2 space-y-6">
                            <!-- Step 1: Livraison -->
                            <Card padding="lg">
                                <div class="flex items-center gap-3 mb-6">
                                    <div class="w-8 h-8 bg-primary-600 text-white rounded-full flex items-center justify-center font-bold">
                                        1
                                    </div>
                                    <h2 class="text-lg font-bold text-slate-900">Adresse de livraison</h2>
                                </div>

                                <div class="grid md:grid-cols-2 gap-4">
                                    <Input
                                        v-model="form.shipping_first_name"
                                        label="Prénom"
                                        required
                                        :error="form.errors.shipping_first_name"
                                    />
                                    <Input
                                        v-model="form.shipping_last_name"
                                        label="Nom"
                                        required
                                        :error="form.errors.shipping_last_name"
                                    />
                                    <Input
                                        v-model="form.phone"
                                        label="Téléphone"
                                        type="tel"
                                        required
                                        :error="form.errors.phone"
                                    />
                                    <Input
                                        v-model="form.email"
                                        label="Email"
                                        type="email"
                                        :error="form.errors.email"
                                    />
                                    <div class="md:col-span-2">
                                        <Input
                                            v-model="form.shipping_address"
                                            label="Adresse complète"
                                            required
                                            :error="form.errors.shipping_address"
                                        />
                                    </div>
                                    <Input
                                        v-model="form.shipping_city"
                                        label="Ville"
                                        required
                                        :error="form.errors.shipping_city"
                                    />
                                    <Input
                                        v-model="form.shipping_postal_code"
                                        label="Code postal"
                                        :error="form.errors.shipping_postal_code"
                                    />
                                </div>
                            </Card>

                            <!-- Step 2: Paiement -->
                            <Card padding="lg">
                                <div class="flex items-center gap-3 mb-6">
                                    <div class="w-8 h-8 bg-primary-600 text-white rounded-full flex items-center justify-center font-bold">
                                        2
                                    </div>
                                    <h2 class="text-lg font-bold text-slate-900">Mode de paiement</h2>
                                </div>

                                <div class="space-y-3">
                                    <label
                                        v-if="settings.payment_moneyfusion_enabled === '1'"
                                        class="flex items-center gap-3 p-4 border-2 rounded-lg cursor-pointer transition"
                                        :class="form.payment_method === 'moneyfusion' ? 'border-primary-600 bg-primary-50' : 'border-slate-200 hover:border-slate-300'"
                                    >
                                        <input
                                            v-model="form.payment_method"
                                            type="radio"
                                            value="moneyfusion"
                                            class="text-primary-600 focus:ring-primary-500"
                                        />
                                        <div>
                                            <p class="font-semibold text-slate-900">Paiement en ligne</p>
                                            <p class="text-sm text-slate-500">Carte bancaire, Mobile Money (MoneyFusion)</p>
                                        </div>
                                    </label>

                                    <label
                                        v-if="settings.payment_cod_enabled === '1'"
                                        class="flex items-center gap-3 p-4 border-2 rounded-lg cursor-pointer transition"
                                        :class="form.payment_method === 'cod' ? 'border-primary-600 bg-primary-50' : 'border-slate-200 hover:border-slate-300'"
                                    >
                                        <input
                                            v-model="form.payment_method"
                                            type="radio"
                                            value="cod"
                                            class="text-primary-600 focus:ring-primary-500"
                                        />
                                        <div>
                                            <p class="font-semibold text-slate-900">Paiement à la livraison</p>
                                            <p class="text-sm text-slate-500">Payez en espèces lors de la réception</p>
                                        </div>
                                    </label>
                                </div>
                            </Card>

                            <!-- Notes -->
                            <Card padding="lg">
                                <label class="block text-sm font-medium text-slate-700 mb-2">
                                    Notes de commande (optionnel)
                                </label>
                                <textarea
                                    v-model="form.notes"
                                    rows="3"
                                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                    placeholder="Instructions de livraison, point de repère..."
                                ></textarea>
                            </Card>
                        </div>

                        <!-- Summary -->
                        <div class="lg:col-span-1">
                            <Card padding="lg" shadow="lg" class="sticky top-4">
                                <h2 class="text-lg font-bold text-slate-900 mb-4">Récapitulatif</h2>

                                <!-- Items -->
                                <div class="space-y-3 mb-4 pb-4 border-b border-slate-200">
                                    <div
                                        v-for="item in cart.items"
                                        :key="item.id"
                                        class="flex gap-3"
                                    >
                                        <div class="w-16 h-16 bg-slate-100 rounded-lg overflow-hidden shrink-0">
                                            <img
                                                v-if="item.product.primary_image"
                                                :src="`/storage/${item.product.primary_image}`"
                                                :alt="item.product.name"
                                                class="w-full h-full object-cover"
                                            />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-slate-900 truncate">
                                                {{ item.product.name }}
                                            </p>
                                            <p class="text-xs text-slate-500">
                                                Qté: {{ item.quantity }} × {{ formatPrice(item.unit_price) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Totals -->
                                <div class="space-y-2 mb-4 pb-4 border-b border-slate-200">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-slate-600">Sous-total</span>
                                        <span class="font-medium text-slate-900">{{ formatPrice(cart.subtotal) }}</span>
                                    </div>
                                    <div v-if="cart.discount > 0" class="flex justify-between text-sm">
                                        <span class="text-slate-600">Réduction</span>
                                        <span class="font-medium text-success-600">-{{ formatPrice(cart.discount) }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-slate-600">Livraison</span>
                                        <span class="font-medium text-slate-900">Calculée après validation</span>
                                    </div>
                                </div>

                                <div class="flex justify-between mb-6">
                                    <span class="text-lg font-bold text-slate-900">Total</span>
                                    <span class="text-2xl font-bold text-primary-600">{{ formatPrice(cart.total) }}</span>
                                </div>

                                <Button
                                    @click="submit"
                                    variant="primary"
                                    size="lg"
                                    :loading="form.processing"
                                    :disabled="form.processing"
                                    class="w-full"
                                >
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Valider ma commande
                                </Button>
                            </Card>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </FrontLayout>
</template>
