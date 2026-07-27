<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
    user: Object,
})

// ── Formulaire profil ─────────────────────────────────────────────────────────
const avatarPreview = ref(null)

const profileForm = useForm({
    name:   props.user.name,
    email:  props.user.email,
    phone:  props.user.phone ?? '',
    avatar: null,
})

function onAvatarChange(e) {
    const file = e.target.files[0]
    if (file) {
        avatarPreview.value        = URL.createObjectURL(file)
        profileForm.avatar         = file
    }
}

function submitProfile() {
    profileForm.post(route('admin.profile.update'), {
        forceFormData: true,
        preserveScroll: true,
    })
}

// ── Formulaire mot de passe ───────────────────────────────────────────────────
const passwordForm = useForm({
    current_password:      '',
    password:              '',
    password_confirmation: '',
})

function submitPassword() {
    passwordForm.post(route('admin.profile.password'), {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
    })
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function initials(name) {
    return (name ?? '?').slice(0, 2).toUpperCase()
}
</script>

<template>
<div class="p-6 space-y-5">

    <!-- Header -->
    <div>
        <h1 class="text-xl font-bold text-gray-900">Mon profil</h1>
        <p class="text-[13px] text-gray-500 mt-0.5">Gérez vos informations personnelles et votre sécurité</p>
    </div>

    <!-- Flash success -->
    <div
        v-if="$page.props.flash?.success"
        class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-[13px] flex items-center gap-2"
    >
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ $page.props.flash.success }}
    </div>

    <!-- Avatar + infos -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4">
            <div class="flex-shrink-0">
                <img
                    v-if="avatarPreview || user.avatar_url"
                    :src="avatarPreview ?? user.avatar_url"
                    alt="Avatar"
                    class="w-20 h-20 rounded-xl object-cover ring-2 ring-gray-100"
                >
                <div
                    v-else
                    class="w-20 h-20 rounded-xl bg-blue-100 flex items-center justify-center text-blue-700 text-2xl font-bold"
                >
                    {{ initials(user.name) }}
                </div>
            </div>
            <div class="flex-1 text-center sm:text-left">
                <h2 class="text-[16px] font-bold text-gray-900">{{ user.name }}</h2>
                <p class="text-[13px] text-gray-500 mt-0.5">{{ user.email }}</p>
                <p v-if="user.phone" class="text-[12px] text-gray-400 mt-0.5">{{ user.phone }}</p>
                <div class="flex flex-wrap gap-2 mt-3 justify-center sm:justify-start">
                    <span class="inline-flex items-center px-2.5 py-1 bg-blue-50 text-blue-700 text-[11px] font-semibold rounded-lg">
                        {{ user.role_label ?? user.role ?? 'Admin' }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-1 bg-gray-100 text-gray-600 text-[11px] font-medium rounded-lg">
                        Membre depuis {{ user.created_at_formatted }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-5">
        <!-- Colonne principale -->
        <div class="lg:col-span-2 space-y-5">

            <!-- Informations personnelles -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-[14px] font-semibold text-gray-900">Informations personnelles</h3>
                </div>

                <form @submit.prevent="submitProfile">
                    <div class="p-5 space-y-4">

                        <!-- Avatar upload -->
                        <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl">
                            <div class="relative w-16 h-16 flex-shrink-0">
                                <img
                                    v-if="avatarPreview || user.avatar_url"
                                    :src="avatarPreview ?? user.avatar_url"
                                    alt="Aperçu"
                                    class="w-16 h-16 rounded-xl object-cover ring-2 ring-white shadow"
                                >
                                <div
                                    v-else
                                    class="w-16 h-16 rounded-xl bg-blue-100 flex items-center justify-center text-blue-700 text-xl font-bold shadow"
                                >
                                    {{ initials(user.name) }}
                                </div>
                            </div>
                            <div>
                                <label class="inline-flex items-center gap-2 h-9 px-4 bg-white border border-gray-200 rounded-lg text-[13px] font-medium text-gray-700 hover:bg-gray-50 cursor-pointer transition">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    Choisir une image
                                    <input type="file" accept="image/*" class="hidden" @change="onAvatarChange">
                                </label>
                                <p class="text-[11px] text-gray-400 mt-1.5">PNG, JPG ou WEBP. Max 2 Mo.</p>
                            </div>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">Nom complet *</label>
                                <input
                                    v-model="profileForm.name"
                                    type="text"
                                    required
                                    class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    :class="{ 'border-red-400': profileForm.errors.name }"
                                >
                                <p v-if="profileForm.errors.name" class="text-red-500 text-[12px] mt-1">{{ profileForm.errors.name }}</p>
                            </div>
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">Email *</label>
                                <input
                                    v-model="profileForm.email"
                                    type="email"
                                    required
                                    class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    :class="{ 'border-red-400': profileForm.errors.email }"
                                >
                                <p v-if="profileForm.errors.email" class="text-red-500 text-[12px] mt-1">{{ profileForm.errors.email }}</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[13px] font-medium text-gray-700 mb-1">Téléphone</label>
                            <input
                                v-model="profileForm.phone"
                                type="text"
                                placeholder="+212 6 XX XX XX XX"
                                class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                        </div>
                    </div>

                    <div class="px-5 py-4 bg-gray-50 border-t border-gray-100 flex items-center gap-3">
                        <button
                            type="submit"
                            :disabled="profileForm.processing"
                            class="h-9 px-5 bg-blue-600 text-white font-medium text-[13px] rounded-lg hover:bg-blue-700 transition disabled:opacity-60"
                        >
                            <span v-if="profileForm.processing">Enregistrement…</span>
                            <span v-else>Mettre à jour le profil</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Changer le mot de passe -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h3 class="text-[14px] font-semibold text-gray-900">Changer le mot de passe</h3>
                </div>

                <form @submit.prevent="submitPassword">
                    <div class="p-5 space-y-4">
                        <div>
                            <label class="block text-[13px] font-medium text-gray-700 mb-1">Mot de passe actuel *</label>
                            <input
                                v-model="passwordForm.current_password"
                                type="password"
                                required
                                placeholder="••••••••"
                                class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="{ 'border-red-400': passwordForm.errors.current_password }"
                            >
                            <p v-if="passwordForm.errors.current_password" class="text-red-500 text-[12px] mt-1">{{ passwordForm.errors.current_password }}</p>
                        </div>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">Nouveau mot de passe *</label>
                                <input
                                    v-model="passwordForm.password"
                                    type="password"
                                    required
                                    placeholder="••••••••"
                                    class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    :class="{ 'border-red-400': passwordForm.errors.password }"
                                >
                                <p v-if="passwordForm.errors.password" class="text-red-500 text-[12px] mt-1">{{ passwordForm.errors.password }}</p>
                            </div>
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">Confirmer *</label>
                                <input
                                    v-model="passwordForm.password_confirmation"
                                    type="password"
                                    required
                                    placeholder="••••••••"
                                    class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                            </div>
                        </div>
                    </div>

                    <div class="px-5 py-4 bg-gray-50 border-t border-gray-100">
                        <button
                            type="submit"
                            :disabled="passwordForm.processing"
                            class="h-9 px-5 bg-amber-600 text-white font-medium text-[13px] rounded-lg hover:bg-amber-700 transition disabled:opacity-60"
                        >
                            <span v-if="passwordForm.processing">Changement…</span>
                            <span v-else>Changer le mot de passe</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-4">
            <!-- Compte -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="px-5 py-3 border-b border-gray-100 flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="text-[13px] font-semibold text-gray-900">Compte</h3>
                </div>
                <div class="p-5 space-y-2 text-[13px]">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-gray-500">Rôle</span>
                        <span class="font-medium text-gray-900">{{ user.role_label ?? user.role ?? 'Admin' }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-gray-500">Créé le</span>
                        <span class="font-medium text-gray-900">{{ user.created_at_formatted }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-gray-500">Dernière connexion</span>
                        <span class="font-medium text-gray-900">{{ user.last_login_at_human ?? '—' }}</span>
                    </div>
                </div>
            </div>

            <!-- Sécurité -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="px-5 py-3 border-b border-gray-100 flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <h3 class="text-[13px] font-semibold text-gray-900">Sécurité</h3>
                </div>
                <ul class="p-5 space-y-2.5 text-[13px] text-gray-600">
                    <li
                        v-for="tip in ['Mot de passe fort (8+ caractères)', 'Ne partagez jamais vos identifiants', 'Déconnexion sur appareils partagés']"
                        :key="tip"
                        class="flex items-start gap-2"
                    >
                        <svg class="w-4 h-4 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>{{ tip }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

</div>
</template>
