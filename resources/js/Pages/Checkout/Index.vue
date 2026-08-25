<script setup>
import FrontLayout from '@/Layouts/FrontLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useHelpers } from '@/Composables/useHelpers';
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
    cart:      Object,
    customer:  Object,
    addresses: Array,
    settings:  Object,
});

const { formatPrice } = useHelpers();

// ─── Adresse sauvegardée ou saisie manuelle ───────────────────────────────────
const selectedAddressId = ref(props.addresses?.[0]?.id ?? null);

const fillFromAddress = (addr) => {
    form.shipping_first_name  = addr.first_name  || props.customer?.first_name || '';
    form.shipping_last_name   = addr.last_name   || props.customer?.last_name  || '';
    form.shipping_address     = addr.address     || '';
    form.shipping_city        = addr.city        || '';
    form.shipping_postal_code = addr.postal_code || '';
};

const selectAddress = (addr) => {
    selectedAddressId.value = addr.id;
    fillFromAddress(addr);
};

// ─── Formulaire ───────────────────────────────────────────────────────────────
const form = useForm({
    email:                props.customer?.email      || '',
    phone:                props.customer?.phone      || '',
    shipping_first_name:  props.customer?.first_name || '',
    shipping_last_name:   props.customer?.last_name  || '',
    shipping_address:     '',
    shipping_city:        '',
    shipping_postal_code: '',
    shipping_country:     'CI',
    payment_method:       props.settings?.payment_cod_enabled === '1' ? 'cod'
                        : props.settings?.payment_jeko_enabled === '1' ? 'jeko'
                        : 'moneyfusion',
    notes:                '',
});

// Pré-remplir depuis la première adresse
if (props.addresses?.length) {
    fillFromAddress(props.addresses[0]);
}

const submit = () => {
    form.post('/commander');
};

onMounted(() => {
    window.trackPixel?.initiateCheckout(
        props.cart?.total ?? 0,
        props.cart?.items?.length ?? 0
    );
});

// ─── Méthodes paiement disponibles ───────────────────────────────────────────
const paymentMethods = computed(() => {
    const methods = [];
    if (props.settings?.payment_jeko_enabled === '1') {
        methods.push({ value: 'jeko', label: 'Paiement en ligne', desc: 'Wave, Orange Money, MTN, Moov, Djamo (Jeko Africa)', icon: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z' });
    }
    if (props.settings?.payment_moneyfusion_enabled === '1') {
        methods.push({ value: 'moneyfusion', label: 'Paiement en ligne', desc: 'Mobile Money, Carte bancaire (MoneyFusion)', icon: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z' });
    }
    if (props.settings?.payment_cod_enabled === '1') {
        methods.push({ value: 'cod', label: 'Paiement à la livraison', desc: 'Payez en espèces à la réception', icon: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z' });
    }
    if (props.settings?.social_whatsapp && props.settings?.whatsapp_order_enabled !== '0') {
        methods.push({ value: 'whatsapp', label: 'Commander sur WhatsApp', desc: 'Notre équipe vous contacte pour finaliser', icon: null, isWA: true });
    }
    return methods;
});
</script>

<template>
    <FrontLayout title="Commander">
        <Head title="Commander" />

        <div class="bg-slate-50 min-h-screen py-8">
            <div class="container mx-auto px-4 max-w-5xl">

                <!-- Stepper -->
                <div class="flex items-center justify-center gap-0 mb-8">
                    <div v-for="(s, i) in [{n:1, label:'Panier'}, {n:2, label:'Livraison'}, {n:3, label:'Confirmation'}]" :key="s.n" class="flex items-center">
                        <div class="flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold"
                                :class="s.n <= 2 ? 'bg-slate-900 text-white' : 'bg-slate-200 text-slate-500'">
                                {{ s.n }}
                            </div>
                            <span class="text-xs mt-1 font-medium"
                                :class="s.n <= 2 ? 'text-slate-900' : 'text-slate-400'">{{ s.label }}</span>
                        </div>
                        <div v-if="i < 2" class="w-16 h-0.5 mx-1 mb-5"
                            :class="s.n < 2 ? 'bg-slate-900' : 'bg-slate-200'"></div>
                    </div>
                </div>

                <div class="grid lg:grid-cols-3 gap-6">

                    <!-- ─── Formulaire ─────────────────────────────── -->
                    <div class="lg:col-span-2 space-y-5">

                        <!-- Adresses sauvegardées -->
                        <div v-if="addresses?.length" class="bg-white rounded-xl border border-slate-200 p-5">
                            <h2 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                Mes adresses enregistrées
                            </h2>
                            <div class="grid sm:grid-cols-2 gap-3">
                                <label
                                    v-for="addr in addresses"
                                    :key="addr.id"
                                    class="flex items-start gap-3 p-3 rounded-xl border-2 cursor-pointer transition"
                                    :class="selectedAddressId === addr.id ? 'border-slate-900 bg-slate-50' : 'border-slate-200 hover:border-slate-300'"
                                    @click="selectAddress(addr)"
                                >
                                    <input type="radio" name="address" :value="addr.id" v-model="selectedAddressId" class="mt-0.5 shrink-0" />
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-slate-900 truncate">{{ addr.first_name }} {{ addr.last_name }}</p>
                                        <p class="text-xs text-slate-500 leading-relaxed">{{ addr.address }}, {{ addr.city }}</p>
                                        <span v-if="addr.is_default" class="inline-block mt-1 text-xs text-primary-600 font-medium">Par défaut</span>
                                    </div>
                                </label>
                            </div>
                            <button class="text-xs text-slate-500 hover:text-slate-700 mt-3 underline underline-offset-2" @click="selectedAddressId = null; form.shipping_address = ''">
                                + Saisir une nouvelle adresse
                            </button>
                        </div>

                        <!-- Bloc livraison -->
                        <div class="bg-white rounded-xl border border-slate-200 p-5">
                            <h2 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                                <span class="w-6 h-6 bg-slate-900 text-white rounded-full flex items-center justify-center text-xs font-bold">1</span>
                                Adresse de livraison
                            </h2>

                            <div class="grid sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 mb-1">Prénom *</label>
                                    <input v-model="form.shipping_first_name" type="text" required
                                        class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-slate-400"
                                        :class="form.errors.shipping_first_name ? 'border-red-400' : 'border-slate-200'" />
                                    <p v-if="form.errors.shipping_first_name" class="text-xs text-red-600 mt-1">{{ form.errors.shipping_first_name }}</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 mb-1">Nom *</label>
                                    <input v-model="form.shipping_last_name" type="text" required
                                        class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-slate-400"
                                        :class="form.errors.shipping_last_name ? 'border-red-400' : 'border-slate-200'" />
                                    <p v-if="form.errors.shipping_last_name" class="text-xs text-red-600 mt-1">{{ form.errors.shipping_last_name }}</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 mb-1">Téléphone *</label>
                                    <input v-model="form.phone" type="tel" required
                                        class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-slate-400"
                                        :class="form.errors.phone ? 'border-red-400' : 'border-slate-200'" />
                                    <p v-if="form.errors.phone" class="text-xs text-red-600 mt-1">{{ form.errors.phone }}</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 mb-1">Email</label>
                                    <input v-model="form.email" type="email"
                                        class="w-full px-3 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-slate-400" />
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-medium text-slate-700 mb-1">Adresse complète *</label>
                                    <input v-model="form.shipping_address" type="text" required
                                        placeholder="Rue, quartier, point de repère..."
                                        class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-slate-400"
                                        :class="form.errors.shipping_address ? 'border-red-400' : 'border-slate-200'" />
                                    <p v-if="form.errors.shipping_address" class="text-xs text-red-600 mt-1">{{ form.errors.shipping_address }}</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 mb-1">Ville *</label>
                                    <input v-model="form.shipping_city" type="text" required
                                        class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-slate-400"
                                        :class="form.errors.shipping_city ? 'border-red-400' : 'border-slate-200'" />
                                    <p v-if="form.errors.shipping_city" class="text-xs text-red-600 mt-1">{{ form.errors.shipping_city }}</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 mb-1">Code postal</label>
                                    <input v-model="form.shipping_postal_code" type="text"
                                        class="w-full px-3 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-slate-400" />
                                </div>
                            </div>
                        </div>

                        <!-- Paiement -->
                        <div class="bg-white rounded-xl border border-slate-200 p-5">
                            <h2 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                                <span class="w-6 h-6 bg-slate-900 text-white rounded-full flex items-center justify-center text-xs font-bold">2</span>
                                Mode de paiement
                            </h2>

                            <div class="space-y-3">
                                <label
                                    v-for="method in paymentMethods"
                                    :key="method.value"
                                    class="flex items-center gap-4 p-4 border-2 rounded-xl cursor-pointer transition"
                                    :class="form.payment_method === method.value
                                        ? 'border-slate-900 bg-slate-50'
                                        : 'border-slate-200 hover:border-slate-300'"
                                >
                                    <input v-model="form.payment_method" type="radio" :value="method.value" class="shrink-0" />
                                    <div v-if="method.isWA" class="w-9 h-9 bg-green-600 rounded-xl flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                    </div>
                                    <div v-else class="w-9 h-9 bg-slate-100 rounded-xl flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" :d="method.icon"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">{{ method.label }}</p>
                                        <p class="text-xs text-slate-500">{{ method.desc }}</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="bg-white rounded-xl border border-slate-200 p-5">
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-2">Notes / Instructions de livraison</label>
                            <textarea
                                v-model="form.notes"
                                rows="3"
                                placeholder="Point de repère, disponibilités horaires..."
                                class="w-full px-3 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-slate-400 resize-none"
                            ></textarea>
                        </div>
                    </div>

                    <!-- ─── Récapitulatif commande ──────────────────── -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-xl border border-slate-200 p-5 sticky top-20">
                            <h2 class="text-sm font-bold text-slate-900 mb-4">Votre commande</h2>

                            <!-- Articles -->
                            <div class="space-y-3 pb-4 border-b border-slate-100">
                                <div v-for="item in cart?.items" :key="item.id" class="flex gap-3">
                                    <div class="w-14 h-14 bg-slate-100 rounded-lg overflow-hidden shrink-0 relative">
                                        <img v-if="item.product.primary_image" :src="`/storage/${item.product.primary_image}`" :alt="item.product.name" class="w-full h-full object-cover" />
                                        <span class="absolute -top-1 -right-1 w-5 h-5 bg-slate-700 text-white text-xs rounded-full flex items-center justify-center font-bold">{{ item.quantity }}</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-medium text-slate-900 line-clamp-2 leading-tight">{{ item.product.name }}</p>
                                        <div v-if="item.variant?.attributes?.length" class="text-xs text-slate-400 mt-0.5">
                                            {{ item.variant.attributes.map(a => a.value).join(' · ') }}
                                        </div>
                                    </div>
                                    <p class="text-xs font-bold text-slate-900 shrink-0">{{ formatPrice(item.total) }}</p>
                                </div>
                            </div>

                            <!-- Totaux -->
                            <div class="space-y-2 py-4 border-b border-slate-100 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Sous-total</span>
                                    <span class="font-medium">{{ formatPrice(cart.subtotal) }}</span>
                                </div>
                                <div v-if="cart.discount > 0" class="flex justify-between">
                                    <span class="text-green-700">Réduction</span>
                                    <span class="font-semibold text-green-700">−{{ formatPrice(cart.discount) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Livraison</span>
                                    <span class="text-slate-400 italic text-xs">À calculer</span>
                                </div>
                            </div>

                            <div class="flex justify-between py-4">
                                <span class="font-bold text-slate-900">Total</span>
                                <span class="text-xl font-bold text-slate-900">{{ formatPrice(cart.total) }}</span>
                            </div>

                            <button
                                @click="submit"
                                :disabled="form.processing"
                                class="w-full py-3.5 bg-slate-900 text-white text-sm font-bold rounded-xl hover:bg-slate-800 disabled:opacity-60 transition flex items-center justify-center gap-2"
                            >
                                <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ form.processing ? 'Traitement...' : 'Valider ma commande' }}
                            </button>

                            <p class="text-xs text-center text-slate-400 mt-3 flex items-center justify-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                Vos données sont protégées
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </FrontLayout>
</template>
