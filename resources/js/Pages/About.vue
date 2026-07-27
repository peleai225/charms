<script setup>
import FrontLayout from '@/Layouts/FrontLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import Card from '@/Components/Card.vue';
import Button from '@/Components/Button.vue';

defineProps({
    about_text: String,
    stats: Object,
    whatsapp_number: String,
});

const values = [
    {
        icon: 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z',
        title: 'Qualité',
        description: 'Chaque produit est sélectionné avec soin pour garantir votre satisfaction.',
        color: 'blue',
    },
    {
        icon: 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z',
        title: 'Service',
        description: 'Une équipe dédiée disponible pour vous accompagner à chaque étape.',
        color: 'emerald',
    },
    {
        icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
        title: 'Confiance',
        description: 'Paiements sécurisés et données protégées : votre sécurité prime.',
        color: 'amber',
    },
    {
        icon: 'M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0',
        title: 'Livraison',
        description: 'Livraison rapide partout en Côte d\'Ivoire, suivie en temps réel.',
        color: 'slate',
    },
];
</script>

<template>
    <FrontLayout>
        <Head title="À propos de nous">
            <meta name="description" content="Découvrez notre histoire, nos valeurs et notre engagement envers la qualité. Une boutique de confiance au service de nos clients en Côte d'Ivoire." />
        </Head>

        <!-- Hero -->
        <div class="bg-slate-900 text-white py-16">
            <div class="container mx-auto px-4">
                <nav class="flex items-center gap-2 text-sm text-slate-400 mb-5">
                    <Link href="/" class="hover:text-white transition-colors">Accueil</Link>
                    <svg class="w-3 h-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                    <span class="text-slate-300">À propos</span>
                </nav>
                <h1 class="text-3xl md:text-4xl font-bold mb-3">À propos de nous</h1>
                <p class="text-slate-300 max-w-xl text-base">
                    Chamse est né d'une passion : offrir des produits de qualité accessibles à tous, avec une expérience d'achat simple et agréable en Côte d'Ivoire.
                </p>
            </div>
        </div>

        <div class="container mx-auto px-4 py-12 max-w-5xl">
            <!-- Notre histoire -->
            <div class="mb-12">
                <span class="text-xs font-semibold text-primary-600 uppercase tracking-widest">Notre histoire</span>
                <h2 class="text-2xl font-bold text-slate-900 mt-1 mb-4">Qui sommes-nous ?</h2>
                <div class="text-slate-600 leading-relaxed text-sm max-w-2xl">
                    <p v-if="about_text" class="whitespace-pre-line">{{ about_text }}</p>
                    <template v-else>
                        <p class="mb-3">
                            Chamse est une boutique en ligne ivoirienne dédiée à vous offrir les meilleurs produits au meilleur prix.
                            Notre mission est de simplifier vos achats quotidiens grâce à une plateforme fiable, des produits soigneusement
                            sélectionnés et un service client réactif.
                        </p>
                        <p>
                            Basés à Abidjan, nous livrons dans toute la Côte d'Ivoire et nous nous engageons à vous apporter satisfaction à chaque commande.
                        </p>
                    </template>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-12">
                <Card v-for="(stat, key) in stats" :key="key" padding="default" shadow="sm">
                    <p class="text-2xl font-bold text-slate-900">{{ stat.value }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">{{ stat.label }}</p>
                </Card>
            </div>

            <!-- Nos valeurs -->
            <div class="mb-12">
                <span class="text-xs font-semibold text-primary-600 uppercase tracking-widest">Nos valeurs</span>
                <h2 class="text-2xl font-bold text-slate-900 mt-1 mb-6">Ce qui nous guide</h2>
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div
                        v-for="value in values"
                        :key="value.title"
                        class="bg-white border border-slate-200 rounded-2xl p-5 text-center shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all"
                    >
                        <div
                            class="w-11 h-11 rounded-xl flex items-center justify-center mx-auto mb-3"
                            :class="`bg-${value.color}-50`"
                        >
                            <svg
                                class="w-5 h-5"
                                :class="`text-${value.color}-600`"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="value.icon" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-slate-900 text-sm mb-1">{{ value.title }}</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">{{ value.description }}</p>
                    </div>
                </div>
            </div>

            <!-- CTA -->
            <Card padding="lg" shadow="none" class="bg-slate-50 text-center">
                <h2 class="text-xl font-bold text-slate-900 mb-2">Prêt à découvrir nos produits ?</h2>
                <p class="text-sm text-slate-500 mb-6">Explorez notre catalogue et trouvez ce qu'il vous faut.</p>
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <Link href="/boutique">
                        <Button variant="primary" size="lg" class="gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                            Voir la boutique
                        </Button>
                    </Link>
                    <a
                        v-if="whatsapp_number"
                        :href="`https://wa.me/${whatsapp_number.replace(/[^0-9]/g, '')}?text=${encodeURIComponent('Bonjour, j\'aimerais en savoir plus sur Chamse.')}`"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-[#25D366] hover:opacity-90 text-white font-semibold text-sm rounded-lg transition-opacity"
                    >
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        Nous contacter sur WhatsApp
                    </a>
                </div>
            </Card>
        </div>
    </FrontLayout>
</template>
